<?php
session_start();
require_once '../db.php'; // ✅ Correct path

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
if (!$order_id || !isset($_FILES['refund_file'])) {
    echo json_encode(['ok' => false, 'msg' => 'Missing data']);
    exit;
}

$allowed = ['jpg','jpeg','png','webp'];
$ext = strtolower(pathinfo($_FILES['refund_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    echo json_encode(['ok' => false, 'msg' => 'Only JPG/PNG/WEBP allowed']);
    exit;
}

$filename = "refund_ord{$order_id}_" . time() . "." . $ext;
$target = "../receipts/" . $filename;

if (move_uploaded_file($_FILES['refund_file']['tmp_name'], $target)) {
    $stmt = $conn->prepare("UPDATE orders SET refund_receipt = ?, status = 'refunded', refunded_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $filename, $order_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Failed to save image']);
}