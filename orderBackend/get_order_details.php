<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo '<p style="color:red;">Unauthorized access</p>';
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) {
    echo '<p style="color:red;">Invalid Order ID</p>';
    exit;
}

// Get main order info
$o = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$o) {
    echo '<p style="color:red;">Order not found</p>';
    exit;
}

// Get ALL items for this order
$items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id ORDER BY id ASC");
?>
<div style="padding:24px; line-height:1.7;">
  <h3>Order #<?= $o['id'] ?> — <?= ucfirst($o['status']) ?></h3>
  <hr style="border:1px solid var(--border); margin:16px 0;">

  <p><strong>Customer:</strong> <?= htmlspecialchars($o['customer_name']) ?></p>
  <p><strong>Contact:</strong> <?= htmlspecialchars($o['contact'] ?? '—') ?></p>
  <p><strong>Payment Method:</strong> <?= htmlspecialchars($o['payment_method'] ?? '—') ?></p>
  <p><strong>Ordered:</strong> <?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></p>

  <h4 style="margin-top:20px; margin-bottom:8px; border-bottom:1px solid var(--border); padding-bottom:4px;">📦 Items Ordered</h4>
  <?php if ($items && $items->num_rows): ?>
  <table style="width:100%; border-collapse:collapse; font-size:14px;">
    <thead>
      <tr style="background:var(--pastel-blue);">
        <th style="padding:8px; text-align:left;">Product</th>
        <th style="padding:8px; text-align:left;">Flavor</th>
        <th style="padding:8px; text-align:left;">Size</th>
        <th style="padding:8px; text-align:left;">Sugar</th>
        <th style="padding:8px; text-align:left;">Add-ons</th>
        <th style="padding:8px; text-align:center;">Qty</th>
        <th style="padding:8px; text-align:right;">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($i = $items->fetch_assoc()): ?>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:8px;"><?= htmlspecialchars($i['product_name']) ?></td>
        <td style="padding:8px;"><?= htmlspecialchars($i['flavor'] ?? '—') ?></td>
        <td style="padding:8px;"><?= htmlspecialchars($i['size']) ?></td>
        <td style="padding:8px;"><?= htmlspecialchars($i['sugar_level']) ?></td>
        <td style="padding:8px;"><?= htmlspecialchars($i['add_ons'] ?? 'None') ?></td>
        <td style="padding:8px; text-align:center;"><?= $i['quantity'] ?></td>
        <td style="padding:8px; text-align:right;">₱<?= number_format($i['subtotal'],2) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
    <tfoot>
      <tr style="font-weight:bold;">
        <td colspan="6" style="padding:8px; text-align:right;">TOTAL:</td>
        <td style="padding:8px; text-align:right; color:#d32f2f;">₱<?= number_format($o['total_price'],2) ?></td>
      </tr>
    </tfoot>
  </table>
  <?php else: ?>
  <p style="color:var(--text-muted);">No item details found for this order.</p>
  <?php endif; ?>

  <?php if (!empty($o['gcash_receipt'])): ?>
  <p style="margin-top:16px;"><strong>GCash Receipt:</strong><br>
    <img src="../receipts/<?= $o['gcash_receipt'] ?>" style="max-width:300px; margin-top:8px; border-radius:8px;">
  </p>
  <?php endif; ?>
</div>