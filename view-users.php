<?php
session_start();
$timeout_duration = 900; // 15 minutes = 900 seconds

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?session_expired=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();
include '../config.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$result = $conn->query("SELECT id, username, email, role FROM users");
?>

<h2>All Registered Users</h2>

<table border="1" cellpadding="10">
    <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>
    <?php while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['username']}</td>
                <td>{$row['email']}</td>
                <td>{$row['role']}</td>
              </tr>";
    } ?>
</table>
