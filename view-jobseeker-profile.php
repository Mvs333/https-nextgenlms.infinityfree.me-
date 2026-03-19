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

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../login.php");
    exit;
}

include("../config.php");

$jobseeker_id = isset($_GET['jobseeker_id']) ? intval($_GET['jobseeker_id']) : 0;

if ($jobseeker_id <= 0) {
    header("Location: view-applicants.php");
    exit;
}

// Fetch user and advanced profile details
$sql = "SELECT u.username, u.email, u.resume, p.work_experience, p.education, p.skills
        FROM users u
        LEFT JOIN jobseeker_profiles p ON u.id = p.user_id
        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobseeker_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

if (!$profile) {
    header("Location: view-applicants.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jobseeker Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .container-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 800px;
            margin: 60px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .profile-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .profile-section h4 {
            color: #003366;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container container-box">
    <h2 class="text-center mb-4 text-dark">Profile for <?= htmlspecialchars($profile['username']) ?></h2>

    <div class="profile-section">
        <h4>Contact Information</h4>
        <p><strong>Name:</strong> <?= htmlspecialchars($profile['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
        <?php if (!empty($profile['resume'])): ?>
            <p><strong>Resume:</strong> <a href="../uploads/<?= htmlspecialchars($profile['resume']) ?>" class="btn btn-sm btn-primary" target="_blank">View Resume</a></p>
        <?php else: ?>
            <p><strong>Resume:</strong> No resume uploaded</p>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h4>Skills</h4>
        <p><?= htmlspecialchars($profile['skills'] ?? 'Not provided') ?></p>
    </div>

    <div class="profile-section">
        <h4>Work Experience</h4>
        <p><?= nl2br(htmlspecialchars($profile['work_experience'] ?? 'Not provided')) ?></p>
    </div>

    <div class="profile-section">
        <h4>Education</h4>
        <p><?= nl2br(htmlspecialchars($profile['education'] ?? 'Not provided')) ?></p>
    </div>

    <div class="text-center mt-4">
        <a href="view-applicants.php" class="btn btn-secondary">← Back to Applicants</a>
    </div>
</div>

</body>
</html>