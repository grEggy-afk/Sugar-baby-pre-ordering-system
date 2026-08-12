<?php
session_start();

// Kung naka-login na ang user, i-redirect agad batay sa role
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: user_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugar Baby</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="splash-page">

    <div class="splash">
        <img src="images/SUGAR BABY 2.png" id="cup" alt="Sugar Baby Cup">
        <img src="images/Group 1.png" id="text" alt="Sugar Baby Text">
    </div>

    <!-- Script para sa Automatic Redirect pagkatapos ng Splash Screen -->
    <script>
        // Pagkalipas ng 2.5 seconds (2500ms), lilipat sa login.php
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2500);
    </script>
    
    <script src="js/splash.js"></script>

</body>
</html>
