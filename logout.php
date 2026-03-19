<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logging Out...</title>
    <script>
        // Use a slight delay to ensure the session is fully destroyed on some servers
        setTimeout(function() {
            // Replaces the current page in the browser history with index.php
            window.location.replace("index.php");
        }, 100);
    </script>
</head>

</html>