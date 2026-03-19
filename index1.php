<!DOCTYPE html>
<html>
<head>
    <title>Job-Portal</title>
    <script type="text/javascript">
        window.addEventListener('pageshow', function (event) {
            // Check if the page was loaded from the back/forward cache
            if (event.persisted) {
                // Redirect to the login page
                window.location.href = "login.php"; 
            }
        });
    </script>
    </head>
<body>
    </body>
</html>