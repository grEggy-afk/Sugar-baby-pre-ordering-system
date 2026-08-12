<!-- customer_page/orders.php -->
<h2 style="color: var(--text-main); margin-bottom: 0.5rem; font-size: 26px;">My Orders</h2>
<p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 14px;">Track real-time status of your active orders</p>

<?php
// FETCH ONLY ACTIVE ORDERS (Pending, Preparing, Ready to Pick Up)
$user_id = $_SESSION['user_id'];
$active_orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id AND status NOT IN ('completed', 'cancelled', 'refunded') ORDER BY created_at DESC");
?>

<?php if ($active_orders && $active_orders->num_rows > 0): ?>
    <?php while ($order = $active_orders->fetch_assoc()): 
        // Determine status class and text EXACTLY LIKE THE PICTURE
        $status_class = '';
        $status_text = ucfirst($order['status']);
        $cancel_hidden = ''; 
        $order_border = '';
        
        // PERFECT STATUS MAPPING FROM YOUR SCREENSHOT
        if ($order['status'] === 'incoming' || $order['status'] === 'pending') {
            $status_class = 'status-pending';     // Gray/Badge: PENDING
            $cancel_hidden = '';                  // Show Cancel Order
            $order_border = 'border: 2px solid #f0e6db;';
        } elseif ($order['status'] === 'preparing') {
            $status_class = 'status-preparing';   // Yellow/Badge: PREPARING
            $cancel_hidden = 'hidden';            // Hide Cancel Order
            $order_border = 'border: 2px solid #f4d85b;'; // Yellow border
        } elseif ($order['status'] === 'ready_pickup' || $order['status'] === 'ready') {
            $status_class = 'status-ready-pickup'; // Blue/Badge: READY TO PICK UP
            $cancel_hidden = 'hidden';
            $order_border = 'border: 2px solid #b0d8eb;'; // Light blue border
        } else {
            $status_class = 'status-pending';
            $order_border = 'border: 2px solid #f0e6db;';
        }
    ?>
    
    <!-- MAGANDA AT MALINIS NA ORDER CARD -->
    <div class="order-card" data-order-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['status']; ?>" style="background: var(--bg-card); border-radius: 20px; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; <?php echo $order_border; ?>">
        
        <!-- LEFT SIDE: Order Details -->
        <div>
            <h4 style="color: var(--text-main); font-size: 1.1rem; font-weight: 700; margin: 0 0 0.25rem 0;">Order #SB-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></h4>
            
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 0.25rem 0;">
                <?php echo htmlspecialchars($order['item_count'] ?? '1'); ?>x Product Name (Size, Sugar)
            </p>
            
            <p style="font-weight: 700; color: #2c3e50; margin: 0; font-size: 0.95rem;">
                Total: ₱<?php echo number_format($order['total_price'], 2); ?> • Just Placed
            </p>
        </div>
        
        <!-- RIGHT SIDE: Status and Buttons -->
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <span class="order-status <?php echo $status_class; ?>" style="padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase;"><?php echo $status_text; ?></span>
            
            <!-- View Order Button -->
            <button class="btn-view-order" onclick="openOrderModal(<?php echo $order['id']; ?>)" style="background: var(--pastel-blue); color: #2c3e50; border: none; padding: 0.35rem 0.75rem; font-weight: 700; border-radius: 10px; cursor: pointer; font-size: 0.75rem; transition: 0.2s;">
                View Order
            </button>

            <!-- Cancel Order Button -->
            <button class="btn-cancel-order cancel-btn <?php echo $cancel_hidden; ?>" data-id="<?php echo $order['id']; ?>" style="background: #f8d7da; color: #721c24; border: none; padding: 0.35rem 0.75rem; font-weight: 700; border-radius: 10px; cursor: pointer; font-size: 0.75rem; transition: 0.2s;">
                Cancel Order
            </button>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="color: var(--text-muted); text-align: center; padding: 3rem;">You have no active orders.</p>
<?php endif; ?>

<!-- ORDER DETAILS MODAL -->
<div class="order-modal-overlay hidden" id="orderModalOverlay">
    <div class="order-modal">
        <h3 id="modalOrderId">Order #SB-0000</h3>
        <p><strong>Items:</strong> <span id="modalItems">-</span></p>
        <p><strong>Total:</strong> <span id="modalTotal">₱0.00</span></p>
        <p><strong>Status:</strong> <span id="modalStatus">Pending</span></p>
        <p><strong>Notes:</strong> <span id="modalNotes">-</span></p>
        <button class="close-btn" onclick="closeOrderModal()">Close</button>
    </div>
</div>