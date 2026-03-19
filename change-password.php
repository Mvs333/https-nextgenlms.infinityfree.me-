<?php
session_start();
$timeout_duration = 900; // 15 minutes = 900 seconds

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?session_expired=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

include("config.php");

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $msg = "<div class='alert alert-danger'>New passwords do not match.</div>";
    } else {
        $userId = $_SESSION['userid'];
        $sql = "SELECT password FROM users WHERE id = $userId";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if ($row && password_verify($current, $row['password'])) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            if ($conn->query("UPDATE users SET password = '$hashed' WHERE id = $userId")) {
                $msg = "<div class='alert alert-success'>Password updated successfully.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Error updating password. Please try again.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Current password is incorrect.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .change-password-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            margin: 70px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .form-control {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="change-password-container">
        <h3 class="text-center mb-4 text-dark">Change Password</h3>

        <?php if ($msg) echo $msg; ?>

        <form method="POST">
            <input type="password" name="current_password" class="form-control" placeholder="Current Password" required>
            <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required>
            
            <button type="submit" class="btn btn-primary w-100">Update Password</button>
        </form>
    </div>
</div>

</body>
</html>
