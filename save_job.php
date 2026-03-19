<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
session_start();
// ... rest of your existing code
session_start();
if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'jobseeker') {
    header("Location: ../login.php");
    exit();
}

include("../config.php");

$userid = $_SESSION['userid'];

if (isset($_GET['job_id']) && isset($_GET['action'])) {
    $job_id = (int)$_GET['job_id'];
    $action = $_GET['action'];

    if ($action === 'save') {
        // Check if the job is already saved to prevent duplicates
        $check_stmt = $conn->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $check_stmt->bind_param("ii", $userid, $job_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $insert_stmt = $conn->prepare("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
            $insert_stmt->bind_param("ii", $userid, $job_id);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();

    } elseif ($action === 'unsave') {
        // Delete the saved job record
        $delete_stmt = $conn->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $delete_stmt->bind_param("ii", $userid, $job_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
}

// Redirect back to the view-jobs page
header("Location: view-jobs.php");
exit();
?>