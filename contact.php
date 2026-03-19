<?php
session_start();
include("config.php");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));
    $logo = "";

    // Handle Logo Upload
    if (!empty($_FILES['logo']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $logo_name = time() . "_" . basename($_FILES['logo']['name']);
            $target = "uploads/" . $logo_name;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                $logo = $logo_name;
            } else {
                $error = "Failed to upload logo.";
            }
        } else {
            $error = "Only JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (empty($error) && !empty($name) && !empty($email) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, logo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $message, $logo);
        if ($stmt->execute()) {
            $success = "Your message has been sent successfully!";
        } else {
            $error = "Something went wrong. Try again.";
        }
    } elseif (empty($error)) {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .contact-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            margin: 80px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        h2 {
            color: #333;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-preview {
            max-width: 150px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container contact-container">
    <h2>Contact Us</h2>

    <?php if ($success): ?>
        <div class="alert alert-success text-center"><?= $success ?></div>
        <?php if (!empty($logo)): ?>
            <div class="text-center">
                <p>Uploaded Logo:</p>
                <img src="uploads/<?= htmlspecialchars($logo) ?>" alt="Logo" class="logo-preview">
            </div>
        <?php endif; ?>
    <?php elseif ($error): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Message:</label>
            <textarea name="message" rows="5" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload Company Logo (JPG/PNG):</label>
            <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png">
        </div>

        <button type="submit" class="btn btn-primary w-100">Send Message</button>
    </form>

    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-secondary">← Back to Home</a>
    </div>
</div>

</body>
</html>
<?php
session_start();
include("config.php");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));
    $logo = "";

    // Handle Logo Upload
    if (!empty($_FILES['logo']['name'])) {
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $logo_name = time() . "_" . basename($_FILES['logo']['name']);
            $target = "uploads/" . $logo_name;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                $logo = $logo_name;
            } else {
                $error = "Failed to upload logo.";
            }
        } else {
            $error = "Only JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (empty($error) && !empty($name) && !empty($email) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, logo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $message, $logo);
        if ($stmt->execute()) {
            $success = "Your message has been sent successfully!";
        } else {
            $error = "Something went wrong. Try again.";
        }
    } elseif (empty($error)) {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .contact-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            margin: 80px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        h2 {
            color: #333;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-preview {
            max-width: 150px;
            margin-top: 10px;
        }
    </style>
</head>
<body>


</html>
