<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
$timeout_duration = 900;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?session_expired=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['userid']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../login.php");
    exit();
}

include("../config.php");

$userid = $_SESSION['userid'];
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch saved jobs for the current user
$sql = "SELECT j.id, j.title, j.company_name, j.location, j.tags, s.saved_on
        FROM saved_jobs s
        JOIN jobs j ON s.job_id = j.id
        WHERE s.user_id = ?
        ORDER BY s.saved_on DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

$saved_jobs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $saved_jobs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Saved Jobs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: url("https://images.unsplash.com/photo-1504384308090-c894fdcc538d") no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .dashboard-wrapper {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            padding: 50px 30px;
            border-radius: 25px;
            max-width: 1200px;
            margin: 60px auto;
            box-shadow: 0 10px 60px rgba(0, 0, 0, 0.35);
        }
        .nav-link {
            font-weight: 500;
            font-size: 16px;
            border-radius: 10px !important;
            padding: 10px 18px;
            transition: all 0.3s ease-in-out;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #000 !important;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff !important;
            color: white !important;
        }
        .tab-content {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            padding: 20px;
            border-radius: 15px;
        }
        .job-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: none;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            transition: all 0.3s ease-in-out;
            color: #000;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .job-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        .job-title {
            font-size: 20px;
            font-weight: 600;
            color: #003366;
        }
        .job-info {
            font-size: 15px;
            color: #333;
        }
        .btn-custom {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>

<div class="container dashboard-wrapper">
    <h2 class="text-center mb-4 text-white">Saved Jobs</h2>

    <ul class="nav nav-pills justify-content-center mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'jobseeker-dashboard.php' ? 'active' : 'bg-primary text-white'); ?>" href="jobseeker-dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'view-jobs.php' ? 'active' : 'bg-primary text-white'); ?>" href="view-jobs.php">View Jobs</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'your-applications.php' ? 'active' : 'bg-primary text-white'); ?>" href="your-applications.php">Your Applications</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'saved-jobs.php' ? 'active' : 'bg-primary text-white'); ?>" href="saved-jobs.php">Saved Jobs</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'profile.php' ? 'active' : 'bg-secondary text-white'); ?>" href="profile.php">My Profile</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'change-password.php' ? 'active' : 'bg-warning text-white'); ?>" href="../change-password.php">Change Password</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'contact.php' ? 'active' : 'bg-success text-white'); ?>" href="../contact.php">Contact Us</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link text-white bg-danger" href="../logout.php">Logout</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active">
            <div class="row">
                <?php if (!empty($saved_jobs)): ?>
                    <?php foreach ($saved_jobs as $job): ?>
                        <div class="col-md-6 mb-4">
                            <div class="job-card">
                                <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                                <div class="job-info">
                                    <strong>Company:</strong> <?php echo htmlspecialchars($job['company_name'] ?? 'N/A'); ?><br>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?><br>
                                    <strong>Saved On:</strong> <?php echo htmlspecialchars($job['saved_on']); ?>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="view-job.php?id=<?php echo $job['id']; ?>" class="btn btn-custom">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">You have not saved any jobs yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>