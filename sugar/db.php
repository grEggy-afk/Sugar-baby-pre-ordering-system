<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sugar_baby_db";
$port = 3307; // Gamitin ang 3307 kung ito ang port sa XAMPP Control Panel

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>


