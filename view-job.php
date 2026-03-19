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

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../login.php");
    exit;
}

include("../config.php");

$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($job_id <= 0) {
    header("Location: view-jobs.php");
    exit;
}

$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    header("Location: view-jobs.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($job['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .job-details-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 800px;
            margin: 50px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .job-title {
            font-size: 28px;
            font-weight: bold;
            color: #003366;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 20px;
            color: #555;
            margin-bottom: 20px;
        }
        .details-section {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .details-section h4 {
            color: #007bff;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .btn-apply {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            font-size: 18px;
            margin-top: 20px;
        }
        .btn-apply:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
    </style>
</head>
<body>

<div class="container job-details-container">
    <h2 class="job-title"><?= htmlspecialchars($job['title']); ?></h2>
    <p class="company-name"><?= htmlspecialchars($job['company_name'] ?? 'N/A'); ?></p>

    <div class="details-section">
        <h4>Job Description</h4>
        <p><?= nl2br(htmlspecialchars($job['description'])); ?></p>
    </div>

    <div class="details-section">
        <h4>Details</h4>
        <p><strong>Location:</strong> <?= htmlspecialchars($job['location']); ?></p>
        <p><strong>Salary Range:</strong> <?= htmlspecialchars($job['salary_range']); ?></p>
        <p><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']); ?></p>
        <p><strong>Experience Required:</strong> <?= htmlspecialchars($job['experience_required']); ?></p>
        <p><strong>Required Skills:</strong> <?= htmlspecialchars($job['skills_required']); ?></p>
    </div>
    
    <div class="text-center">
        <a href="apply.php?job_id=<?= $job['id']; ?>" class="btn btn-apply">Apply for this Job</a>
    </div>

    <div class="text-center mt-3">
        <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
    </div>
</div>

</body>
</html>