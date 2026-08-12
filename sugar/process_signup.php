<?php
require_once 'db.php';

/** @var mysqli $conn */ // Sinisiguradong kilala ng VS Code ang $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Securely hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check kung umiiral na ang email sa database
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('Email is already registered!'); window.location.href='signup.php';</script>";
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();

    // Ipasok ang bagong user sa users table
    $stmt = $conn->prepare("INSERT INTO users (full_name, phone_number, email, password, role) VALUES (?, ?, ?, ?, 'customer')");
    $stmt->bind_param("ssss", $fullname, $phone, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Account created successfully! Please log in.'); window.location.href='login.php';</script>";
        $stmt->close();
        $conn->close();
        exit();
    } else {
        echo "<script>alert('Registration failed. Please try again.'); window.location.href='signup.php';</script>";
        $stmt->close();
        $conn->close();
        exit();
    }
}
?>