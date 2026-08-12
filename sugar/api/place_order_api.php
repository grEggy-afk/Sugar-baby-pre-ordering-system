<?php
// I-off ang error display para hindi mag-output ng HTML
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../db.php';

header('Content-Type: application/json');

$response = ['ok' => false, 'message' => 'Unknown error'];

try {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
        throw new Exception('Unauthorized');
    }

    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'Customer';
    $total_price = floatval($_POST['price'] ?? 0);
    $qty = intval($_POST['quantity'] ?? 1);
    $payment = $_POST['payment_method'] ?? 'GCash';

    if ($total_price <= 0) {
        throw new Exception('Invalid price');
    }

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, total_price, item_count, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("isdss", $user_id, $user_name, $total_price, $qty, $payment);

    if ($stmt->execute()) {
        $response = ['ok' => true, 'order_id' => $conn->insert_id];
    } else {
        throw new Exception('Database error: ' . $conn->error);
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response = ['ok' => false, 'message' => $e->getMessage()];
}

// Laging mag-output ng JSON
echo json_encode($response);
exit;
?>