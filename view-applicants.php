<?php
session_start();
include("../config.php"); // Adjust path as necessary

// CRITICAL CHECK: Enforce Employer Login
if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'employer') { 
    header("Location: ../login.php"); 
    exit; 
} 

$employer_id = $_SESSION['userid'];
$message = '';
$result = null; 

// --- Fetch Applicants ---
// This query fetches the basic applicant details for all jobs posted by the current employer.
$applicants_sql = "SELECT 
                       a.id AS application_id, 
                       a.resume,
                       u.id AS user_id, 
                       u.username, 
                       u.email, 
                       j.title AS job_title
                   FROM applications a
                   JOIN users u ON a.user_id = u.id
                   JOIN jobs j ON a.job_id = j.id
                   WHERE j.employer_id = ?"; 
                   
$applicants_stmt = $conn->prepare($applicants_sql);

if ($applicants_stmt) {
    // Bind the employer_id and execute the query
    $applicants_stmt->bind_param("i", $employer_id);
    $applicants_stmt->execute();
    $result = $applicants_stmt->get_result(); // Assign the result set to $result
} else {
    // Error handling for failed statement preparation
    $message = "<div class='alert alert-danger'>Database Error: Could not prepare applicant query.</div>";
}
// --- End Fetch Applicants ---
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Applicants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body>

<div class="container mt-5">
    <h3 class="text-center mb-4">Applicants to Your Jobs</h3>
    <?php if ($message) echo $message; ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Applicant Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Check if $result is a valid object and has rows
            if ($result && $result->num_rows > 0): 
                while ($applicant = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?= htmlspecialchars($applicant['job_title']); ?></td>
                <td><?= htmlspecialchars($applicant['username']); ?></td>
                <td><?= htmlspecialchars($applicant['email']); ?></td>
                
                <td>
                    <?php if (!empty($applicant['resume'])): ?>
                        <a href="../uploads/<?= htmlspecialchars($applicant['resume']); ?>" target="_blank" class="btn btn-sm btn-info">View Resume</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; 
            else: ?>
            <tr>
                <td colspan="4" class="text-center">No applicants found for your jobs.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

</div>

<?php 
// Close the statement after use
if (isset($applicants_stmt)) $applicants_stmt->close(); 
?>
</body>
</html>