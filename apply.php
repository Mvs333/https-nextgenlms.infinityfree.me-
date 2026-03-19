<?php 
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1. 
header("Pragma: no-cache"); // HTTP 1.0. 
header("Expires: 0"); // Proxies. 
session_start(); // Start session once at the very top

// Check for duplicate session_start() (removed the second one from your original block)

// Check if user is logged in and is a jobseeker
if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'jobseeker') { 
    header("Location: ../login.php"); 
    exit; 
} 

include("../config.php"); 

$message = ''; 
$job_id = null;
$user_id = $_SESSION['userid'];

if (isset($_GET['job_id'])) { 
    $job_id = intval($_GET['job_id']); 

    if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
        // --- 1. Check if resume file was provided ---
        if (empty($_FILES['resume']['name']) || $_FILES['resume']['error'] == UPLOAD_ERR_NO_FILE) {
            header("Location: your-applications.php?status=no_file");
            exit;
        }

        // Handle resume upload 
        $resume = null; 
        $targetDir = "../uploads/"; 
        
        // Ensure upload directory exists
        if (!is_dir($targetDir)) { 
            // Setting permissions 0777 might be too permissive; 0755 is often safer, but 0777 may be needed on some hosts.
            if (!mkdir($targetDir, 0777, true)) {
                // If directory creation fails, treat it as an upload error
                header("Location: your-applications.php?status=upload_failed");
                exit;
            }
        } 
        
        $fileName = time() . "_" . basename($_FILES['resume']['name']); 
        $targetFile = $targetDir . $fileName; 

        // Validate file type (PDF only) 
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION)); 
        if ($fileType !== 'pdf') { 
            header("Location: your-applications.php?status=invalid_file");
            exit;
        }
        
        // 2. Attempt to upload file
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetFile)) { 
            $resume = $fileName; 
        } else { 
            // File move failed (e.g., permissions issue)
            header("Location: your-applications.php?status=upload_failed");
            exit;
        } 

        // 3. Insert Application into Database
        if ($resume) { 
            $sql = "INSERT INTO applications (user_id, job_id, applied_on, resume) VALUES (?, ?, NOW(), ?)"; 
            
            // Check if connection is valid before preparing
            if ($conn) {
                $stmt = $conn->prepare($sql); 
                $stmt->bind_param("iis", $user_id, $job_id, $resume); 

                if ($stmt->execute()) { 
                    // SUCCESS: Redirect using URL status flag
                    header("Location: your-applications.php?status=success"); 
                    exit;
                } else { 
                    // DB Error: Optionally delete uploaded file and redirect with error flag
                    if (file_exists($targetFile)) {
                        unlink($targetFile);
                    }
                    header("Location: your-applications.php?status=error"); 
                    exit;
                } 
            } else {
                // Database connection error (should be handled in config.php, but this is a fail-safe)
                header("Location: your-applications.php?status=error");
                exit;
            }
        } 
    } 
} else { 
    // Invalid Job ID on initial GET request
    $message = "<div class='alert alert-danger'>Invalid Job ID.</div>"; 
} 
?> 

<!DOCTYPE html> 
<html> 
<head> 
    <title>Apply for Job</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <style> 
        body { 
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d') no-repeat center center fixed; 
            background-size: cover; 
            font-family: Arial, sans-serif; 
        } 
        .apply-container { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 30px; 
            border-radius: 12px; 
            max-width: 450px; 
            margin: 80px auto; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
        } 
    </style> 
</head> 
<body> 

<div class="container"> 
    <div class="apply-container"> 
        <h3 class="text-center mb-4 text-dark">Apply for Job</h3> 

        <?php 
        // Display any messages set by the PHP logic (e.g., Invalid Job ID)
        if ($message) echo $message; 
        
        // Show the form only if job_id is valid
        if ($job_id):
        ?> 

        <form method="POST" enctype="multipart/form-data"> 
            <div class="mb-3"> 
                <label class="form-label">Upload Resume (PDF only):</label> 
                <input type="file" name="resume" class="form-control" accept=".pdf" required> 
            </div> 
            <button type="submit" class="btn btn-primary w-100">Submit Application</button> 
        </form> 
        
        <?php endif; ?>

        <div class="text-center mt-3"> 
            <a href="view-jobs.php" class="text-decoration-none">← Back to Jobs</a> 
        </div> 
    </div> 
</div> 

</body> 
</html>