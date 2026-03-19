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

$user_id = $_SESSION['userid'];

// Fetch applied jobs with company name
$sql = "SELECT jobs.title, jobs.location, jobs.salary_range, jobs.company_name, applications.applied_on, applications.resume
        FROM applications
        INNER JOIN jobs ON applications.job_id = jobs.id
        WHERE applications.user_id = ?
        ORDER BY applications.applied_on DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .application-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 900px;
            margin: 50px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .job-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
        }
        .job-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container application-container">
    <h2 class="text-center mb-4 text-dark">Your Applications</h2>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="job-card">
                <div class="job-title"><?= htmlspecialchars($row['title']); ?></div>
                <p class="text-muted mb-1">Company: <?= htmlspecialchars($row['company_name']); ?></p>
                <p class="text-muted mb-1">Location: <?= htmlspecialchars($row['location']); ?></p>
                <p class="text-muted mb-1">Salary: <?= htmlspecialchars($row['salary_range']); ?></p>
                <p class="text-muted mb-1">Applied On: <?= htmlspecialchars($row['applied_on']); ?></p>
                <?php if (!empty($row['resume'])): ?>
                    <p><a href="../uploads/<?= htmlspecialchars($row['resume']); ?>" target="_blank" class="btn btn-sm btn-success">View Resume</a></p>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        echo "<div class='alert alert-warning text-center'>You have not applied for any jobs yet.</div>";
    }
    ?>
</div>


<script>
    setTimeout(function() {
        window.location.href = "view-jobs.php";
    }, 20000);
</script>

</body>
</html>