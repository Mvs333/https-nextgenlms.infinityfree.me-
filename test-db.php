<?php
$host = "sql105.infinityfree.com";  // Replace if it's different in your panel
$user = "if0_39564077";             // Your InfinityFree MySQL username
$pass = "m1a2n3a4v5";         // Replace with your correct DB password
$db   = "if0_39564077_jobportal";   // Your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Connected to database successfully!";
}
?>
