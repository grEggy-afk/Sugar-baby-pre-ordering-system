<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../db.php';

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$new_status = trim($_POST['new_status'] ?? '');
$reason = trim($_POST['reason'] ?? '');

if (!$order_id || !$new_status) {
    echo json_encode(['ok' => false, 'msg' => 'Missing order ID or status']);
    exit;
}

// EXACTLY YOUR COLUMNS — NO TYPO, NO EXTRA FIELDS
if ($new_status === 'cancelled') {
    $stmt = $conn->prepare("UPDATE orders SET status=?, cancel_reason=?, cancelled_by='Admin', cancelled_at=NOW() WHERE id=?");
    $stmt->bind_param("ssi", $new_status, $reason, $order_id);
} elseif ($new_status === 'preparing') {
    $stmt = $conn->prepare("UPDATE orders SET status=?, accepted_at=NOW() WHERE id=?");
    $stmt->bind_param("si", $new_status, $order_id);
} elseif ($new_status === 'completed') {
    $stmt = $conn->prepare("UPDATE orders SET status=?, completed_at=NOW() WHERE id=?");
    $stmt->bind_param("si", $new_status, $order_id);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Invalid status']);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'DB Error: '.$conn->error]);
}
exit;
?>