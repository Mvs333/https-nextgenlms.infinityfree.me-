<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
session_start();
// ... rest of your existing code
session_start();
$timeout_duration = 900; // 15 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?session_expired=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../login.php");
    exit;
}

include("../config.php");

$jobseeker_id = $_SESSION['userid'];
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch jobs with the new 'company_name' field
$jobs_query = !empty($search)
    ? "SELECT * FROM jobs WHERE title LIKE '%$search%' OR location LIKE '%$search%'"
    : "SELECT * FROM jobs";
$jobs_result = $conn->query($jobs_query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Jobs & Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1581091870622-2c1f1a3fd3d5') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container-box {
            background: rgba(255, 255, 255, 0.97);
            padding: 40px;
            border-radius: 15px;
            max-width: 1100px;
            margin: 50px auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }
        .job-card {
            border-left: 5px solid #007bff;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
            border-radius: 10px;
            transition: 0.3s;
        }
        .job-card:hover {
            transform: scale(1.01);
            box-shadow: 0 6px 18px rgba(0, 123, 255, 0.2);
        }
        .job-title {
            font-size: 22px;
            font-weight: bold;
            color: #212529;
        }
        .search-box input {
            width: 350px;
            margin-right: 10px;
        }
        .badge {
            font-size: 14px;
        }
        .btn-sm {
            font-size: 14px;
            padding: 5px 10px;
        }
        .back-button-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container container-box">
    <div class="d-flex justify-content-end back-button-container">
        <a href="jobseeker-dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <h2 class="text-center text-dark mb-4">Find Your Next Opportunity</h2>

    <form method="GET" class="d-flex justify-content-center mb-4 search-box">
        <input type="text" name="search" class="form-control" placeholder="Search jobs by title or location" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if ($jobs_result && $jobs_result->num_rows > 0): ?>
        <div id="jobList">
        <?php while ($job = $jobs_result->fetch_assoc()): ?>
            <?php
            $job_id = $job['id'];
            
            // Check if already applied
            $applied_stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND user_id = ?");
            $applied_stmt->bind_param("ii", $job_id, $jobseeker_id);
            $applied_stmt->execute();
            $applied_result = $applied_stmt->get_result();
            $is_applied = $applied_result->num_rows > 0;
            $applied_stmt->close();
            ?>
            <div class="job-card">
                <div class="job-title"><?= htmlspecialchars($job['title']); ?></div>
                <p class="text-muted mb-1">Company: <?= htmlspecialchars($job['company_name'] ?? 'N/A'); ?></p>
                <p class="text-muted mb-1">Location: <?= htmlspecialchars($job['location']); ?></p>
                <p class="text-muted mb-2">Salary: <?= htmlspecialchars($job['salary_range']); ?></p>
                <p class="text-muted mb-3">Job type: <?= htmlspecialchars($job['job_type']); ?></p>
                <p class="text-muted mb-4">Experience required: <?= htmlspecialchars($job['experience_required']); ?></p>
                <?php
                // Check if already saved
                $saved_stmt = $conn->prepare("SELECT id FROM saved_jobs WHERE job_id = ? AND user_id = ?");
                $saved_stmt->bind_param("ii", $job_id, $jobseeker_id);
                $saved_stmt->execute();
                $saved_result = $saved_stmt->get_result();
                $is_saved = $saved_result->num_rows > 0;
                $saved_stmt->close();
                ?>
                <div class="d-flex align-items-center">
                    <?php if ($is_applied): ?>
                        <button class="btn btn-success btn-sm me-2" disabled>Applied</button>
                    <?php else: ?>
                        <a href='apply.php?job_id=<?= $job_id; ?>' class='btn btn-outline-primary btn-sm me-2'>Apply Now</a>
                    <?php endif; ?>
                    
                    <?php if ($is_saved): ?>
                        <a href='save_job.php?job_id=<?= $job_id; ?>&action=unsave' class='btn btn-warning btn-sm'>
                            <i class="fa fa-bookmark"></i> Unsave
                        </a>
                    <?php else: ?>
                        <a href='save_job.php?job_id=<?= $job_id; ?>&action=save' class='btn btn-outline-secondary btn-sm'>
                            <i class="fa fa-bookmark"></i> Save Job
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">No jobs found.</div>
    <?php endif; ?>
</div>

</body>
</html>