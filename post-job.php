<?php
session_start();
$timeout_duration = 900; // 15 minutes

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

$message = "";
if (isset($_POST['post_job'])) {
    $title = trim($_POST['title']);
    $company_name = trim($_POST['company_name']); // New field
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary_range']);
    $job_type = $_POST['job_type'];
    $experience = trim($_POST['experience_required']);
    $skills = trim($_POST['skills_required']);
    $employer_id = $_SESSION['userid'];
    $tags = $_SESSION['tags'];

    // Updated SQL query to include 'company_name'
    $sql = "INSERT INTO jobs (title, company_name, description, location, salary_range, job_type, experience_required, skills_required, employer_id,tags)
            VALUES ('$title', '$company_name', '$description', '$location', '$salary', '$job_type', '$experience', '$skills', '$employer_id','$tags')";

    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success text-center'>Job posted successfully! <a href='dashboard.php' class='alert-link'>Go to Dashboard</a></div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post a Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            max-width: 700px;
            margin: 50px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #000;
        }
    </style>
</head>
<body>

<div class="container form-container">
    <h2>Post a New Job</h2>
    <?php echo $message; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Job Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter job title" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control" placeholder="Enter company name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Job Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Enter job description" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" placeholder="Enter location">
        </div>

        <div class="mb-3">
            <label class="form-label">Salary Range</label>
            <input type="text" name="salary_range" class="form-control" placeholder="e.g. ₹30,000 - ₹50,000">
        </div>

        <div class="mb-3">
            <label class="form-label">Job Type</label>
            <select name="job_type" class="form-select">
                <option value="Full-Time">Full-Time</option>
                <option value="Part-Time">Part-Time</option>
                <option value="Internship">Internship</option>
                <option value="Remote">Remote</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Experience Required</label>
            <input type="text" name="experience_required" class="form-control" placeholder="e.g. 2 years">
        </div>

        <div class="mb-3">
            <label class="form-label">Required Skills</label>
            <input type="text" name="skills_required" class="form-control" placeholder="e.g. PHP, MySQL, HTML">
        </div>

        <button type="submit" name="post_job" class="btn btn-primary w-100">Post Job</button>
    </form>

    <div class="text-center mt-3">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

</body>
</html>