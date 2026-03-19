<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
session_start();
// ... rest of your existing code
include("config.php");
session_start();

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['userid'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'jobseeker') {
                header("Location: jobseeker/jobseeker-dashboard.php");
            } elseif ($user['role'] == 'employer') {
                header("Location: employer/dashboard.php");
            } elseif ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            }
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Email not registered.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body, html {
            height: 100%;
            margin: 0;
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0,0,0,0.45);
            z-index: 0;
        }
        .login-wrapper {
            position: relative;
            z-index: 1;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.35);
            max-width: 420px;
            width: 100%;
            padding: 35px 30px;
            transition: transform 0.3s ease;
        }
        .login-container:hover {
            transform: translateY(-5px);
        }
        h3 {
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            color: #333;
        }
        .form-floating > input:focus ~ label {
            color: #0d6efd;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #0b5ed7;
        }
        .alert-danger {
            font-weight: 600;
            font-size: 0.95rem;
        }
        /* Modal tweaks */
        .modal-content {
            border-radius: 12px;
        }
        .modal-header {
            border-bottom: none;
        }
        .btn-close {
            background: transparent;
            border: none;
            font-size: 1.4rem;
        }
        a.forgot-link {
            font-weight: 500;
            font-size: 0.9rem;
            color: #0d6efd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a.forgot-link:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="overlay"></div>

<div class="login-wrapper">
    <div class="login-container shadow-sm">
        <h3>Login</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="form-floating mb-4">
                <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required autofocus>
                <label for="email">Email address</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 py-2">Login</button>
            <p class="text-center mt-3">
                <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
            </p>
        </form>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="forgotPasswordLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="forgotPasswordForm" method="post" action="forgot-password.php" novalidate>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" name="reset" class="btn btn-success w-100 py-2">Send Reset Link</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
