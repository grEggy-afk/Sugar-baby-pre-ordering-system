<?php
session_start();
require_once 'db.php';

// Check muna gamit ang isset() para siguradong hindi mag-warning kung walang REQUEST_METHOD
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Kunin ang user profile at role mula sa database
    $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Isave ang session ng user
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            // Role-Based Redirection
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'employee':
                    header("Location: employee_dashboard.php");
                    break;
                case 'customer':
                default:
                    header("Location: user_dashboard.php");
                    break;
            }
            exit();

        } else {
            echo "<script>alert('Maling password!'); window.location.href='login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Walang account na nakalista sa email na ito!'); window.location.href='login.php';</script>";
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // Kapag binuksan nang direkta sa URL, ibalik agad sa login.php
    header("Location: login.php");
    exit();
}
?>