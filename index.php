<?php
session_start();
include("config.php");

// CSRF Protection - ensure token exists before processing POST
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

if (isset($_POST['register'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = $first_name . ' ' . $last_name; // Combine for the 'username' column
    $email = trim($_POST['email']);
    $password_input = $_POST['password']; // Get the raw password input
    $role = $_POST['role'];
    $company_name = ($role === 'employer') ? trim($_POST['company_name']) : null;

    // --- START of password validation change ---
    if (empty($password_input)) {
        $message = "<div class='alert alert-danger text-center'>Password cannot be empty!</div>";
    } else {
        $password = password_hash($password_input, PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "<div class='alert alert-danger text-center'>Email already registered!</div>";
        } else {
            // Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                // Insert employer details if role is employer
                if ($role === 'employer' && !empty($company_name)) {
                    $emp_stmt = $conn->prepare("INSERT INTO employers (user_id, company_name) VALUES (?, ?)");
                    $emp_stmt->bind_param("is", $user_id, $company_name);
                    $emp_stmt->execute();
                }

                // Auto-login with consistent session keys
                $_SESSION['userid'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                // Redirect to respective dashboards
                if ($role === 'employer') {
                    header("Location: employer/dashboard.php");
                } else {
                    header("Location: jobseeker/jobseeker-dashboard.php");
                }
                exit;
            } else {
                $message = "<div class='alert alert-danger text-center'>Error: " . htmlspecialchars($conn->error) . "</div>";
            }
        }
    }
    // --- END of password validation change ---
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register - Job Portal</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --glass-bg: rgba(255,255,255,0.75);
            --accent: #147bff;
            --muted: rgba(0,0,0,0.55);
        }
        html,body{
            height:100%;
            margin:0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        /* keep the same background image (full cover) */
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            position: center;
            color: #222;
        }

        /* subtle dark overlay so white text reads well */
        .bg-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(90deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.3) 40%, rgba(0,0,0,0.12) 100%);
            pointer-events: none;
            z-index: 0;
        }

        /* left big heading */
        .hero-left {
            position: absolute;
            left: 18px;
            top: 1px;
            z-index: 1;
            color: white;
            font-weight: 700;
            font-size: clamp(28px, 4vw, 56px);
            letter-spacing: -1px;
            text-shadow: 0 6px 22px rgba(0,0,0,0.5);
            max-width: 42%;
        }
        .hero-quote {
            position: absolute;
            right: 48px;
            bottom: 48px;
            z-index: 1;
            color: rgba(255,255,255,0.95);
            font-style: italic;
            font-size: 1.1rem;
            text-align: right;
            text-shadow: 0 4px 18px rgba(0,0,0,0.45);
            max-width: 36%;
        }
        .hero-quote small { display:block; margin-top:10px; opacity:0.9; font-weight:600; }

        /* center glass card */
        .glass-card {
            position: relative;
            z-index: 2;
            backdrop-filter: blur(6px) saturate(120%);
            background: linear-gradient(180deg, rgba(255,255,255,0.88), rgba(250,250,250,0.80));
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.55);
            padding: 34px;
            max-width: 500px; /* Reduced max-width for a smaller box */
            margin: 12vh auto; /* Adjusted margin to move it down */
        }

        .glass-card h2 {
            color: var(--accent);
            font-weight:700;
            text-align:center;
            margin-bottom:24px;
        }

        .form-label {
            color: var(--muted);
            font-weight:600;
        }

        /* rounded select, inputs */
        .form-control, .form-select {
            border-radius:8px;
            padding:14px 12px;
            box-shadow:none;
            border:1px solid rgba(0,0,0,0.08);
            background: rgba(255,255,255,0.9);
        }

        .btn-primary {
            background: linear-gradient(90deg, #0d6efd 0%, #147bff 100%);
            border:none;
            border-radius:10px;
            padding:12px;
            font-weight:700;
            box-shadow: 0 8px 24px rgba(20,123,255,0.18);
        }

        .small-muted {
            color: rgba(0,0,0,0.55);
            text-align:center;
            margin-top:14px;
        }

        /* responsive adjustments */
        @media (max-width: 991px){
            .hero-left { display:none; }
            .hero-quote { display:none; }
            .glass-card { margin: 8vh 18px; padding:22px; }
        }

        @media (max-width: 420px){
            .glass-card { padding:18px; }
            .form-control, .form-select { padding:10px; }
        }
    </style>
</head>
<body>
    <div class="bg-overlay" aria-hidden="true"></div>

    <div class="hero-left">
        <div style="font-size: 1.6rem; opacity: 0.95; font-weight: 800;">
            <u><a href="https://job-portal3.free.nf/login.php" style="text-decoration: none; color: inherit;">
                Welcome to Job-Portal
            </u></a>
        </div>
    </div>

    <div class="container">
        <div class="glass-card">
            <?php if (!empty($message)) echo $message; ?>

            <h2>Create Your Account</h2>

            <form method="post" novalidate>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">First Name</label>
                        <input autocomplete="given-name" type="text" name="first_name" class="form-control" placeholder="Enter your first name" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Last Name</label>
                        <input autocomplete="family-name" type="text" name="last_name" class="form-control" placeholder="Enter your last name" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Email</label>
                        <input autocomplete="email" type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Password</label>
                        <input autocomplete="new-password" type="password" name="password" class="form-control" placeholder="Create a strong password" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Select Role</label>
                        <select name="role" id="roleSelect" class="form-select" required>
                            <option value="jobseeker">Job Seeker</option>
                            <option value="employer">Employer</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6" id="companyField" style="display:none;">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" placeholder="Your company name">
                    </div>

                    <div class="col-12">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                    </div>

                    <div class="col-12">
                        <p class="small-muted">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle company field when employer selected
        const roleSelect = document.getElementById('roleSelect');
        const companyField = document.getElementById('companyField');
        const companyInput = companyField.querySelector('input');

        function toggleCompanyField() {
            if (roleSelect.value === 'employer') {
                companyField.style.display = 'block';
                companyInput.setAttribute('required', 'required');
            } else {
                companyField.style.display = 'none';
                companyInput.removeAttribute('required');
                companyInput.value = '';
            }
        }

        roleSelect.addEventListener('change', toggleCompanyField);
        window.addEventListener('DOMContentLoaded', toggleCompanyField);
    </script>
</body>
</html>