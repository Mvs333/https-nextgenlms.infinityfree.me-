<?php
session_start();
$timeout_duration = 900; // 15 minutes = 900 seconds

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?session_expired=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../login.php");
    exit;
}

include("../config.php");

$employer_id = $_SESSION['userid'];
$jobs = $conn->query("SELECT * FROM jobs WHERE employer_id = $employer_id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
        }
        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            max-width: 950px;
            margin: 60px auto;
        }
        h2, h3 {
            font-weight: bold;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-top: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .nav-link {
            color: #000 !important;
            font-weight: 600;
            transition: background 0.3s;
        }
        .nav-link:hover {
            background-color: rgba(0,0,0,0.05) !important;
            border-radius: 5px;
        }
        .btn-outline-primary {
            font-weight: 600;
            border-radius: 50px;
        }
        .text-center span {
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <h2 class="text-center mb-4">Welcome, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span> (Employer)</h2>

    <!-- Navigation Menu -->
    <nav class="nav nav-pills justify-content-center mb-4">
        <a class="nav-link me-2" href="post-job.php">Post New Job</a>
        <a class="nav-link me-2" href="view-applicants.php">View Applicants</a>
        <a class="nav-link me-2" href="../change-password.php">Change Password</a>
        
        <a class="nav-link" href="../logout.php">Logout</a>
    </nav>

    <h3 class="mb-3">Your Posted Jobs</h3>

    <?php if ($jobs && $jobs->num_rows > 0): ?>
        <?php while ($job = $jobs->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                    <p class="card-text">
                        <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?><br>
                        <strong>Posted on:</strong> <?php echo htmlspecialchars($job['created_at']); ?>
                    </p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center">You have not posted any jobs yet.</div>
    <?php endif; ?>
</div>
<?php include('../footer.php'); ?>
</body>
</html>
