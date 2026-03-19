<?php
include("config.php");
session_start();

$message = "";

if (isset($_POST['reset'])) {
    $email = trim($_POST['email']);
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($check->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $conn->query("UPDATE users SET reset_token='$token', reset_expiry=DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE email='$email'");

    $reset_link = "https://job-portal3.free.nf/reset-password.php?token=$token";


        $message = "<div class='alert alert-success text-center'>
                        Password reset link generated! <br>
                        <a href='$reset_link' class='btn btn-success mt-2'>Reset Password</a>
                    </div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>Email not found!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
        }
        .forgot-container {
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

<div class="forgot-container">
    <h3 class="text-center">Forgot Password?</h3>
    <p class="text-center text-muted">Enter your registered email to reset your password</p>

    <?php echo $message; ?>

    <form method="post">
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <button type="submit" name="reset" class="btn btn-primary w-100">Send Reset Link</button>
        <p class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">Back to Login</a>
        </p>
    </form>
</div>

</body>
</html>
