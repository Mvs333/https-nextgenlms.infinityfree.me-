<?php
$servername = "sql105.infinityfree.com";
$username = "if0_39564077";
$password = "m1a2n3a4v5";
$dbname = "if0_39564077_jobportal";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
