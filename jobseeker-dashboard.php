<?php
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

// Set the current page to highlight the active tab in the navigation
$current_page = basename($_SERVER['PHP_SELF']);
$userid = $_SESSION['userid'];

// --- NEW CODE BLOCK: Fetch Jobseeker's Applied Jobs ---
$applied_jobs = [];
$applied_jobs_sql = "SELECT j.title, j.company_name, j.location, a.applied_on 
                     FROM applications a
                     JOIN jobs j ON a.job_id = j.id
                     WHERE a.user_id = ?
                     ORDER BY a.applied_on DESC";
$stmt = $conn->prepare($applied_jobs_sql);
if ($stmt) {
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result_applied = $stmt->get_result();
    while ($row = $result_applied->fetch_assoc()) {
        $applied_jobs[] = $row;
    }
    $stmt->close();
}

// Fetch jobseeker's skills
$jobseeker_profile_sql = "SELECT skills FROM jobseeker_profiles WHERE user_id = ?";
$stmt = $conn->prepare($jobseeker_profile_sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$jobseeker_profile_result = $stmt->get_result();
$skills_string = "";
if ($jobseeker_profile_result && $jobseeker_profile_result->num_rows > 0) {
    $profile_data = $jobseeker_profile_result->fetch_assoc();
    $skills_string = $profile_data['skills'];
}
$stmt->close();

// Prepare the recommended jobs query
$recommended_jobs = [];
if (!empty($skills_string)) {
    $skills_array = explode(',', $skills_string);
    $search_queries = [];
    foreach ($skills_array as $skill) {
        $skill = trim($skill);
        if (!empty($skill)) {
            $search_queries[] = "tags LIKE '%" . $conn->real_escape_string($skill) . "%'";
        }
    }

    if (!empty($search_queries)) {
        $search_query = implode(' OR ', $search_queries);
        $recommended_jobs_sql = "SELECT * FROM jobs WHERE " . $search_query . " LIMIT 5";
        $recommended_jobs_result = $conn->query($recommended_jobs_sql);

        if ($recommended_jobs_result && $recommended_jobs_result->num_rows > 0) {
            while ($row = $recommended_jobs_result->fetch_assoc()) {
                $recommended_jobs[] = $row;
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jobseeker Dashboard</title>
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

        h2 span {
            color: #007bff;
            font-weight: 600;
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

        /* Active tab styling */
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
        
        .tab-pane h4 {
            color: #003366;
        }
        
        .job-card, .applied-job-item {
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

        .job-card:hover, .applied-job-item:hover {
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

        @media (max-width: 768px) {
            .dashboard-wrapper {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container dashboard-wrapper">
    <h2 class="text-center mb-4 text-white">Welcome, <span class="text-primary"><?php echo htmlspecialchars($_SESSION['username']); ?></span> (Jobseeker)</h2>

    <ul class="nav nav-pills justify-content-center mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'view-jobs.php' ? 'active' : 'bg-primary text-white'); ?>" href="view-jobs.php">View Jobs</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 active" href="jobseeker-dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'your-applications.php' ? 'active' : 'bg-primary text-white'); ?>" href="your-applications.php">Your Applications</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'saved_jobs.php' ? 'active' : 'bg-primary text-white'); ?>" href="saved_jobs.php">Saved Jobs</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'profile.php' ? 'active' : 'bg-secondary text-white'); ?>" href="profile.php">My Profile</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link me-2 <?php echo ($current_page == 'change-password.php' ? 'active' : 'bg-warning text-white'); ?>" href="../change-password.php">Change Password</a>
        </li>
       
        <li class="nav-item" role="presentation">
            <a class="nav-link text-white bg-danger" href="../logout.php">Logout</a>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active">

            <h4 class="text-center">Your Applications</h4>
            <p class="text-center text-dark">A list of jobs you have applied for.</p>
            <div class="row mt-4">
                <?php if (!empty($applied_jobs)): ?>
                    <?php foreach ($applied_jobs as $job): ?>
                        <div class="col-md-6 mb-4">
                            <div class="applied-job-item">
                                <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                                <div class="job-info">
                                    <strong>Company:</strong> <?php echo htmlspecialchars($job['company_name']); ?><br>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?><br>
                                    <strong>Applied On:</strong> <?php echo date('M d, Y', strtotime($job['applied_on'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">You have not applied for any jobs yet.</p>
                <?php endif; ?>
            </div>

            <hr>

            <h4 class="text-center">Recommended Jobs for you</h4>
            <p class="text-center text-dark">Based on your profile skills.</p>
            <div class="row mt-4">
                <?php if (!empty($recommended_jobs)): ?>
                    <?php foreach ($recommended_jobs as $job): ?>
                        <div class="col-md-6 mb-4">
                            <div class="job-card">
                                <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                                <div class="job-info">
                                    <strong>Company:</strong> <?php echo htmlspecialchars($job['company_name']); ?><br>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?><br>
                                    <strong>Tags:</strong> <?php echo htmlspecialchars($job['tags']); ?>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="view-job.php?id=<?php echo $job['id']; ?>" class="btn btn-custom">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No recommended jobs found. Please update your skills in your profile to get better recommendations.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include('../footer.php'); ?>
</body>
</html>