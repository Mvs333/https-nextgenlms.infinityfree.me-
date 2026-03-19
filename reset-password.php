<?php
include("config.php");
session_start();

$message = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $check = $conn->query("SELECT * FROM users WHERE reset_token='$token' AND reset_expiry > NOW()");

    if ($check->num_rows > 0) {
        if (isset($_POST['update'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$password', reset_token=NULL, reset_expiry=NULL WHERE reset_token='$token'");
            $message = "<div class='alert alert-success text-center'>
                            ✅ Password updated successfully! <br>
                            <a href='login.php' class='btn btn-primary mt-2'>Login Now</a>
                        </div>";
        }
    } else {
        $message = "<div class='alert alert-danger text-center'>Invalid or expired token!</div>";
    }
} else {
    $message = "<div class='alert alert-danger text-center'>Invalid request!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
        }
        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 420px;
            margin: 100px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        h3 {
            color: #333;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <h3 class="text-center">Reset Password</h3>
    <p class="text-center text-muted">Enter your new password below</p>

    <?php echo $message; ?>

    <?php if (isset($_GET['token']) && $check->num_rows > 0): ?>
        <form method="post">
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="New Password" required>
            </div>
            <button type="submit" name="update" class="btn btn-success w-100">Update Password</button>
        </form>
    <?php endif; ?>

    <p class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">Back to Login</a>
    </p>
</div>

</body>
</html>
