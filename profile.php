<?php

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
session_start();
// ... rest of your existing code

session_start();
$timeout_duration = 900; // 15 minutes

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

$userid = $_SESSION['userid'];
$message = "";

// Fetch user and jobseeker profile details
$user = $conn->query("SELECT * FROM users WHERE id=$userid")->fetch_assoc();
$jobseeker_profile = $conn->query("SELECT * FROM jobseeker_profiles WHERE user_id=$userid")->fetch_assoc();

// If no profile exists, create a new empty one
if (!$jobseeker_profile) {
    $conn->query("INSERT INTO jobseeker_profiles (user_id) VALUES ($userid)");
    $jobseeker_profile = $conn->query("SELECT * FROM jobseeker_profiles WHERE user_id=$userid")->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Determine which form was submitted
    if (isset($_POST['form_type'])) {
        $form_type = $_POST['form_type'];

        if ($form_type === 'profile_summary') {
            $username = $conn->real_escape_string($_POST['username']);
            $email = $conn->real_escape_string($_POST['email']);
            $update_user_sql = "UPDATE users SET username='$username', email='$email' WHERE id=$userid";
            if ($conn->query($update_user_sql)) {
                $message = "<div class='alert alert-success'>Profile summary updated successfully!</div>";
                $_SESSION['username'] = $username;
            } else {
                $message = "<div class='alert alert-danger'>Error updating profile summary: " . $conn->error . "</div>";
            }
        } elseif ($form_type === 'work_experience') {
            $work_experience = $conn->real_escape_string($_POST['work_experience']);
            $update_profile_sql = "UPDATE jobseeker_profiles SET work_experience='$work_experience' WHERE user_id=$userid";
            if ($conn->query($update_profile_sql)) {
                $message = "<div class='alert alert-success'>Work experience updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating work experience: " . $conn->error . "</div>";
            }
        } elseif ($form_type === 'education') {
            $education = $conn->real_escape_string($_POST['education']);
            $update_profile_sql = "UPDATE jobseeker_profiles SET education='$education' WHERE user_id=$userid";
            if ($conn->query($update_profile_sql)) {
                $message = "<div class='alert alert-success'>Education updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating education: " . $conn->error . "</div>";
            }
        } elseif ($form_type === 'skills') {
            $skills = $conn->real_escape_string($_POST['skills']);
            $update_profile_sql = "UPDATE jobseeker_profiles SET skills='$skills' WHERE user_id=$userid";
            if ($conn->query($update_profile_sql)) {
                $message = "<div class='alert alert-success'>Skills updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating skills: " . $conn->error . "</div>";
            }
        } elseif ($form_type === 'resume_upload') {
            // Handle resume upload
            $resume = $user['resume'];
            if (!empty($_FILES['resume']['name'])) {
                $resume_name = time() . "_" . basename($_FILES['resume']['name']);
                $target = "../uploads/" . $resume_name;

                $fileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));
                if ($fileType === 'pdf') {
                    if (move_uploaded_file($_FILES['resume']['tmp_name'], $target)) {
                        $update_user_sql = "UPDATE users SET resume='$resume_name' WHERE id=$userid";
                        if ($conn->query($update_user_sql)) {
                            $message = "<div class='alert alert-success'>Resume uploaded successfully!</div>";
                        } else {
                            $message = "<div class='alert alert-danger'>Failed to update resume path in database.</div>";
                        }
                    } else {
                        $message = "<div class='alert alert-danger'>Failed to upload resume.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Only PDF files are allowed.</div>";
                }
            }
        } elseif ($form_type === 'profile_picture_upload') {
            // Handle profile picture upload
            $uploadDir = '../uploads/profile_pics/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (!empty($_FILES['profile_picture']['name'])) {
                $imageFileType = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageFileType, $allowedExtensions)) {
                    $fileName = time() . '_' . $userid . '.' . $imageFileType;
                    $targetFilePath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                        // Delete old profile picture if it exists
                        if (!empty($user['profile_picture']) && file_exists($uploadDir . $user['profile_picture'])) {
                            unlink($uploadDir . $user['profile_picture']);
                        }

                        $update_sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                        $stmt = $conn->prepare($update_sql);
                        $stmt->bind_param("si", $fileName, $userid);
                        if ($stmt->execute()) {
                            $message = "<div class='alert alert-success'>Profile picture uploaded successfully!</div>";
                        } else {
                            $message = "<div class='alert alert-danger'>Error updating database.</div>";
                        }
                        $stmt->close();
                    } else {
                        $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
                }
            }
        }
    }
    // Re-fetch data to show the latest changes
    $user = $conn->query("SELECT * FROM users WHERE id=$userid")->fetch_assoc();
    $jobseeker_profile = $conn->query("SELECT * FROM jobseeker_profiles WHERE user_id=$userid")->fetch_assoc();
}

// Fetch applied jobs for the current user
$applied_jobs = [];
$applied_jobs_sql = "SELECT j.title, j.company_name, j.location, a.applied_on 
                     FROM applications a 
                     JOIN jobs j ON a.job_id = j.id 
                     WHERE a.user_id = ? 
                     ORDER BY a.applied_on DESC";
$stmt_applied_jobs = $conn->prepare($applied_jobs_sql);
if ($stmt_applied_jobs) {
    $stmt_applied_jobs->bind_param("i", $userid);
    $stmt_applied_jobs->execute();
    $result_applied = $stmt_applied_jobs->get_result();
    while ($row = $result_applied->fetch_assoc()) {
        $applied_jobs[] = $row;
    }
    $stmt_applied_jobs->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .profile-container {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .profile-sidebar {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-main-content {
            margin-bottom: 20px;
        }
        .profile-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 20px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        .profile-pic-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            border: 2px solid #0d6efd;
            flex-shrink: 0;
        }
        .profile-pic-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-pic-placeholder {
            font-size: 3rem;
            color: #6c757d;
        }
        .profile-details h4 {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 5px;
        }
        .profile-details p {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .section-title h5 {
            font-weight: 600;
            color: #495057;
        }
        .btn-edit {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-edit:hover {
            color: #0a58ca;
        }
        .form-control {
            border-radius: 6px;
        }
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .applied-job-item {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .applied-job-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .applied-job-item h6 {
            color: #007bff;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .applied-job-item p {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 2px;
        }
        .list-group-item {
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .list-group-item:hover {
            background-color: #e9ecef;
            color: #0d6efd;
        }
        .list-group-item a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>

<div class="container profile-container">
    <?php if ($message) echo $message; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="profile-card profile-sidebar text-center">
                <div class="profile-pic-container mx-auto">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="../uploads/profile_pics/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user profile-pic-placeholder"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-details mt-3">
                    <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="text-muted">Jobseeker ID: <?php echo htmlspecialchars($userid); ?></p>
                </div>
            </div>

            <div class="profile-card profile-sidebar">
                <div class="section-title">
                    <h5>Profile Picture</h5>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="profile_picture_upload">
                    <div class="mb-3">
                        <label class="form-label">Upload a new picture (JPG, PNG, GIF)</label>
                        <input type="file" name="profile_picture" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload Picture</button>
                </form>
            </div>

            <div class="profile-card profile-sidebar">
                <h5 class="mb-3">Quick Links</h5>
                <div class="list-group">
                    <a href="#profile-summary" class="list-group-item">Profile Summary</a>
                    <a href="#work-experience" class="list-group-item">Work Experience</a>
                    <a href="#education" class="list-group-item">Education</a>
                    <a href="#skills" class="list-group-item">Skills</a>
                    <a href="#resume" class="list-group-item">Resume</a>
                    <a href="#applied-jobs" class="list-group-item">Applied Jobs</a>
                    <a href="jobseeker-dashboard.php" class="btn btn-secondary w-100 mt-3">Back to Dashboard</a>
                </div>
            </div>
            
            <div class="profile-card profile-sidebar" id="applied-jobs">
                <h5 class="mb-4">Applied Jobs</h5>
                <div class="applied-jobs-list">
                    <?php if (!empty($applied_jobs)): ?>
                        <?php foreach ($applied_jobs as $job): ?>
                            <div class="applied-job-item">
                                <h6><?php echo htmlspecialchars($job['title']); ?></h6>
                                <p><i class="fas fa-building me-2"></i><?php echo htmlspecialchars($job['company_name']); ?></p>
                                <p><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($job['location']); ?></p>
                                <p><i class="fas fa-calendar-check me-2"></i>Applied: <?php echo date('M d, Y', strtotime($job['applied_on'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">You have not applied for any jobs yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 profile-main-content">

            <div class="profile-card" id="profile-summary">
                <div class="section-title">
                    <h5>Profile Summary</h5>
                    <a href="#" class="btn-edit" onclick="toggleForm('profileSummaryForm')">Edit</a>
                </div>
                <div id="profileSummaryDisplay">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <form id="profileSummaryForm" method="post" style="display: none;">
                    <input type="hidden" name="form_type" value="profile_summary">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>

            <div class="profile-card" id="work-experience">
                <div class="section-title">
                    <h5>Work Experience</h5>
                    <a href="#" class="btn-edit" onclick="toggleForm('workExperienceForm')">Edit</a>
                </div>
                <div id="workExperienceDisplay">
                    <?php if (!empty($jobseeker_profile['work_experience'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($jobseeker_profile['work_experience'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No work experience added yet.</p>
                    <?php endif; ?>
                </div>
                <form id="workExperienceForm" method="post" style="display: none;">
                    <input type="hidden" name="form_type" value="work_experience">
                    <div class="mb-3">
                        <textarea name="work_experience" class="form-control" rows="5" placeholder="Add your work experience, including job title, company, and key responsibilities."><?php echo htmlspecialchars($jobseeker_profile['work_experience']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>

            <div class="profile-card" id="education">
                <div class="section-title">
                    <h5>Education</h5>
                    <a href="#" class="btn-edit" onclick="toggleForm('educationForm')">Edit</a>
                </div>
                <div id="educationDisplay">
                    <?php if (!empty($jobseeker_profile['education'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($jobseeker_profile['education'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No education added yet.</p>
                    <?php endif; ?>
                </div>
                <form id="educationForm" method="post" style="display: none;">
                    <input type="hidden" name="form_type" value="education">
                    <div class="mb-3">
                        <textarea name="education" class="form-control" rows="3" placeholder="Add your education, including degrees and institutions."><?php echo htmlspecialchars($jobseeker_profile['education']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>

            <div class="profile-card" id="skills">
                <div class="section-title">
                    <h5>Skills</h5>
                    <a href="#" class="btn-edit" onclick="toggleForm('skillsForm')">Edit</a>
                </div>
                <div id="skillsDisplay">
                    <?php if (!empty($jobseeker_profile['skills'])): ?>
                        <p><?php echo htmlspecialchars($jobseeker_profile['skills']); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No skills added yet.</p>
                    <?php endif; ?>
                </div>
                <form id="skillsForm" method="post" style="display: none;">
                    <input type="hidden" name="form_type" value="skills">
                    <div class="mb-3">
                        <input type="text" name="skills" class="form-control" placeholder="e.g., PHP, JavaScript, SQL" value="<?php echo htmlspecialchars($jobseeker_profile['skills']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>

            <div class="profile-card" id="resume">
                <div class="section-title">
                    <h5>Resume</h5>
                </div>
                <div>
                    <?php if (!empty($user['resume'])): ?>
                        <p>Current Resume: <a href="../uploads/<?php echo htmlspecialchars($user['resume']); ?>" target="_blank" class="btn btn-secondary btn-sm">View Resume</a></p>
                    <?php else: ?>
                        <p>No resume uploaded yet.</p>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="resume_upload">
                        <div class="mb-3">
                            <label class="form-label">Upload New Resume (PDF only):</label>
                            <input type="file" name="resume" class="form-control" accept=".pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleForm(formId) {
        var form = document.getElementById(formId);
        var displayDiv = document.getElementById(formId.replace('Form', 'Display'));
        
        // Toggle visibility
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            if (displayDiv) {
                displayDiv.style.display = 'none';
            }
        } else {
            form.style.display = 'none';
            if (displayDiv) {
                displayDiv.style.display = 'block';
            }
        }
    }
</script>
</body>
</html>