<!-- customer_page/history.php -->
<h2 style="color: var(--text-main); margin-bottom: 0.5rem; font-size: 26px;">History</h2>
<p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 14px;">Your completed and past orders</p>

<?php
// FETCH ONLY COMPLETED, CANCELLED, OR REFUNDED ORDERS FOR THIS USER
$user_id = $_SESSION['user_id'];
$history_orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id AND status IN ('completed', 'cancelled', 'refunded') ORDER BY created_at DESC");
?>

<?php if ($history_orders && $history_orders->num_rows > 0): ?>
    <?php while ($order = $history_orders->fetch_assoc()): 
        // Determine status class and text
        $status_class = '';
        $status_text = ucfirst($order['status']);
        
        if ($order['status'] === 'completed') {
            $status_class = 'status-completed';   // Green
        } elseif ($order['status'] === 'cancelled') {
            $status_class = 'status-cancelled';   // Red
        } elseif ($order['status'] === 'refunded') {
            $status_class = 'status-refunded';    // Yellow
        } else {
            $status_class = 'status-completed';
        }
    ?>
    
    <!-- HISTORY CARD (Exact same design as Orders page) -->
    <div class="order-card" data-order-id="<?php echo $order['id']; ?>" style="background: var(--bg-card); border-radius: 20px; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border: 2px solid #f0e6db;">
        
        <!-- LEFT SIDE: Order Details -->
        <div>
            <h4 style="color: var(--text-main); font-size: 1.1rem; font-weight: 700; margin: 0 0 0.25rem 0;">Order #SB-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></h4>
            
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.25rem 0;">
                <?php echo htmlspecialchars($order['item_count'] ?? '1'); ?>x item(s) • 
                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
            </p>
            
            <p style="font-weight: 700; color: #2c3e50; margin: 0; font-size: 0.95rem;">
                Total: ₱<?php echo number_format($order['total_price'], 2); ?>
            </p>
        </div>
        
        <!-- RIGHT SIDE: Status and View Button -->
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <span class="order-status <?php echo $status_class; ?>" style="padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase;"><?php echo $status_text; ?></span>
            
            <!-- View Order Button (Reuses the same modal function from Orders) -->
            <button class="btn-view-order" onclick="openOrderModal(<?php echo $order['id']; ?>)" style="background: var(--pastel-blue); color: #2c3e50; border: none; padding: 0.35rem 0.75rem; font-weight: 700; border-radius: 10px; cursor: pointer; font-size: 0.75rem; transition: 0.2s;">
                View Order
            </button>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="color: var(--text-muted); text-align: center; padding: 3rem;">You have no past orders yet.</p>
<?php endif; ?>