<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Store Manager';

$in_iframe = isset($_GET['iframe']) && $_GET['iframe'] === '1';

// --- DASHBOARD METRICS QUERIES ---
$rev_query = $conn->query("SELECT SUM(total_price) AS revenue FROM orders WHERE status = 'completed'");
$total_revenue = $rev_query ? ($rev_query->fetch_assoc()['revenue'] ?? 0) : 0;

$pending_query = $conn->query("SELECT COUNT(*) AS pending FROM orders WHERE status = 'pending'");
$pending_orders = $pending_query ? ($pending_query->fetch_assoc()['pending'] ?? 0) : 0;

// --- HISTORY METRICS & QUERIES ---
$summary_query = $conn->query("
    SELECT 
        COUNT(CASE WHEN status = 'completed' THEN 1 END) AS total_completed,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS total_cancelled,
        COUNT(CASE WHEN status = 'refunded' THEN 1 END) AS total_refunded,
        SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) AS completed_revenue
    FROM orders
");
$summary = $summary_query ? $summary_query->fetch_assoc() : [
    'total_completed' => 0, 'total_cancelled' => 0, 'total_refunded' => 0, 'completed_revenue' => 0
];

// --- LIVE ORDERS FOR ORDERS TAB ---
$count_incoming = $count_queue = $count_completed = $count_cancelled = $count_refunded = 0;
$count_query = $conn->query("
    SELECT 
        SUM(status = 'incoming') AS incoming,
        SUM(status = 'preparing') AS queue,
        SUM(status = 'completed') AS completed,
        SUM(status = 'cancelled') AS cancelled,
        SUM(status = 'refunded') AS refunded
    FROM orders
");
if ($count_query) {
    $c = $count_query->fetch_assoc();
    $count_incoming = $c['incoming'] ?? 0;
    $count_queue = $c['queue'] ?? 0;
    $count_completed = $c['completed'] ?? 0;
    $count_cancelled = $c['cancelled'] ?? 0;
    $count_refunded = $c['refunded'] ?? 0;
}

$incoming_orders = $conn->query("SELECT * FROM orders WHERE status = 'incoming' ORDER BY created_at DESC");
$queue_orders    = $conn->query("SELECT * FROM orders WHERE status = 'preparing' ORDER BY created_at DESC");
$completed_orders= $conn->query("SELECT * FROM orders WHERE status = 'completed' ORDER BY created_at DESC LIMIT 10");
$cancelled_orders= $conn->query("SELECT * FROM orders WHERE status = 'cancelled' ORDER BY created_at DESC");
$refunded_orders = $conn->query("SELECT * FROM orders WHERE status = 'refunded' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Sugar Baby Admin</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
      :root {
        --bg-main: #fcfbfa; --bg-sidebar: #cbebff; --bg-card: #ffffff;
        --text-main: #2d3748; --text-muted: #62728d;
        --pastel-yellow: #fff2a8; --pastel-yellow-dark: #f0db6e;
        --pastel-blue: #cbebff; --pastel-pink: #ffd6e7;
        --pastel-pink-dark: #fca1c9; --border: #f0e6db;
        --sidebar-text: #2c3e50; --sidebar-active-bg: #fff2a8;
        --sidebar-active-text: #2c3e50; --card-shadow: 0 4px 15px rgba(203, 235, 255, 0.4);
      }
      
      body.dark-mode {
        --bg-main: #1a1e24; --bg-sidebar: #13171c; --bg-card: #22272e;
        --text-main: #f0f4f8; --text-muted: #9fb3c8; --border: #2d3748;
        --sidebar-text: #e2e8f0; --sidebar-active-bg: #3b4a6b;
        --sidebar-active-text: #fff2a8; --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      }
      
      * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; transition: background-color 0.2s, color 0.2s, border-color 0.2s; }
      
      /* ===== FIX: REMOVE ALL MARGINS AND PADDINGS TO MAKE IT FULL WIDTH ===== */
      body { background-color: var(--bg-main); color: var(--text-main); display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; }
      
      .main-wrapper { width: 100%; display: flex; flex-direction: column; }
      
      /* ONLY SHOW SIDEBAR & HEADER IF NOT IN IFRAME */
      <?php if (!$in_iframe): ?>
      aside {
        width: 270px; background-color: var(--bg-sidebar); padding: 1.75rem 1.25rem; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; border-right: 2px solid var(--border); height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; overflow-y: auto;
      }
      .brand-container { text-align: center; margin-bottom: 2rem; }
      .logo-holder { width: 95px; height: 95px; background: #fff; border: 3px solid var(--pastel-pink-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.85rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
      .brand-title-red { font-family: 'Fredoka', cursive; font-size: 2.2rem; font-weight: 800; color: #ff0015; text-transform: uppercase; text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000; }
      .brand-title-yellow { font-family: 'Fredoka', cursive; font-size: 2.2rem; font-weight: 800; color: #ffe600; text-transform: uppercase; text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000; }
      .brand-subtitle-white { font-family: 'Fredoka', cursive; font-size: 0.85rem; font-weight: 700; color: #ffffff; text-transform: uppercase; text-shadow: -1.5px -1.5px 0 #000, 1.5px -1.5px 0 #000, -1.5px 1.5px 0 #000, 1.5px 1.5px 0 #000; }
      .nav-links { list-style: none; }
      .nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--sidebar-text); text-decoration: none; border-radius: 12px; font-weight: 600; margin-bottom: 0.35rem; cursor: pointer; position: relative; }
      .nav-item:hover, .nav-item.active { background-color: var(--sidebar-active-bg); color: var(--sidebar-active-text); font-weight: 700; }
      .order-notif-badge { position: absolute; top: 6px; right: 8px; background: #ef4444; color: white; font-size: 11px; font-weight: 700; min-width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
      .logout-btn { background-color: var(--pastel-pink); color: #2c3e50; border: none; padding: 0.75rem 1rem; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s ease; margin-top: auto; text-decoration: none; }
      .logout-btn:hover { background-color: var(--pastel-pink-dark); color: #ffffff; }
      
      header {
        display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 2rem; background-color: var(--bg-card); border-bottom: 2px solid var(--border); height: 70px; width: 100%; margin-left: 270px; box-sizing: border-box;
      }
      .search-bar { display: flex; align-items: center; background: var(--bg-main); padding: 0.5rem 1.2rem; border-radius: 20px; width: 320px; border: 2px solid var(--pastel-yellow-dark); }
      .search-bar input { border: none; background: transparent; outline: none; margin-left: 0.5rem; color: var(--text-main); width: 100%; }
      .user-profile { display: flex; align-items: center; gap: 1rem; position: relative; }
      .notification-btn { background: var(--pastel-pink); border: none; color: var(--text-main); font-size: 1rem; cursor: pointer; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
      .user-trigger { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.4rem 0.6rem; border-radius: 10px; }
      .user-info { text-align: right; }
      .user-name { font-weight: 700; font-size: 0.875rem; color: var(--text-main); }
      .user-role { font-size: 0.75rem; color: var(--text-muted); }
      .avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--pastel-yellow); color: #2c3e50; border: 2px solid var(--pastel-yellow-dark); display: flex; align-items: center; justify-content: center; font-weight: 800; }
      .user-popup-box { position: absolute; top: 3.5rem; right: 0; width: 260px; background: var(--bg-card); border: 2px solid var(--pastel-yellow-dark); border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); padding: 1.25rem; z-index: 200; }
      <?php else: ?>
      /* When inside iframe, we hide sidebar and header and remove margins */
      header, aside { display: none !important; }
      .main-wrapper { width: 100%; margin: 0 !important; padding: 0 !important; }
      body { padding: 0 !important; margin: 0 !important; }
      <?php endif; ?>

      /* ===== CONTENT CONTAINER - 100% WIDE ===== */
      .content-container {
        width: 100%;
        padding: 1.5rem 2rem;
        box-sizing: border-box;
        margin-left: <?php echo $in_iframe ? '0' : '270px'; ?>;
        max-width: 100% !important;
      }

      .tab-content.active { display: block; width: 100%; }
      .page-title { margin-top: 0; font-size: 1.5rem; color: var(--text-main); margin-bottom: 0.25rem; }
      .page-sub { color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem; }

      /* ===== STATS ROW - FULL STRETCH ===== */
      .stats-row { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
        gap: 16px; 
        margin-bottom: 24px; 
        width: 100%;
      }
      .stat-card { background: var(--bg-card); padding: 20px 12px; border-radius: 16px; border: 2px solid var(--border); box-shadow: var(--card-shadow); display: flex; flex-direction: column; justify-content: center; align-items: center; transition: transform 0.2s; }
      .stat-card:hover { transform: translateY(-5px); border-color: var(--pastel-pink-dark); }
      .stat-num { font-size: 32px; font-weight: 800; margin-bottom: 4px; text-align: center; }
      .stat-card h4 { font-size: 14px; font-weight: 600; text-align: center; margin: 0; }
      
      /* ===== ORDER TABS ===== */
      .order-tabs { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
      .order-tabs button { padding: 10px 19px; border-radius: 20px; border: 2px solid var(--border); background: var(--bg-card); color: var(--text-main); font-weight: 600; cursor: pointer; transition: all 0.2s; }
      .order-tabs button:hover { background: var(--pastel-yellow); border-color: var(--pastel-yellow-dark); }
      .order-tabs button.active-tab { background: var(--pastel-yellow); border-color: var(--pastel-yellow-dark); font-weight: 700; }
      
      /* ===== TABLES - 100% WIDE ===== */
      .table-container, .table-wrap { width: 100%; border-radius: 16px; overflow: hidden; }
      table { width: 100%; border-collapse: collapse; font-size: 14px; table-layout: fixed; }
      th, td { padding: 14px 10px; text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      th { background: var(--pastel-blue); color: var(--sidebar-text); font-weight: 700; font-size: 14px; }
      tr:last-child td { border-bottom: none; }
      tr:hover { background-color: var(--bg-main); }
      
      /* ===== BADGES ===== */
      .badge { padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 12px; display: inline-block; }
      .incoming { background: #fff3e0; color: #e65100; }
      .queue { background: #e3f2fd; color: #1565c0; }
      .completed { background: #e8f5e9; color: #2e7d32; }
      .refund-pending { background: #fff9c4; color: #f57f17; }
      
      /* ===== ACTION BUTTONS ===== */
      .action-btn { padding: 5px 12px; border-radius: 20px; border: none; font-weight: 600; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.2s; margin: 0 2px; }
      .action-btn:hover { transform: scale(1.02); opacity: 0.9; }
      .btn-view { background: var(--pastel-yellow); color: #856404; }
      .btn-receipt { background: var(--pastel-blue); color: #1565c0; padding: 4px 10px; border-radius: 6px; border: 1px solid #1565c0; }
      .btn-receipt:hover { background: #1565c0; color: white; }
      .btn-accept { background: #c8e6c9; color: #2e7d32; }
      .btn-decline { background: #ffcdd2; color: #c62828; }
      .btn-complete { background: #c8e6c9; color: #2e7d32; }
      .btn-upload { background: #fff9c4; color: #f57f17; }
      .btn-upload:hover { background: #fbc02d; color: white; }
      
      /* ===== MODALS ===== */
      .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); align-items: center; justify-content: center; z-index: 999; padding: 16px; }
      .modal.show { display: flex; }
      .modal-content { background: var(--bg-card); padding: 24px; border-radius: 16px; border: 2px solid var(--border); width: 100%; max-width: 580px; max-height: 85vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
      .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
      .close-modal { background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted); }
      .refund-confirm-btn { background: var(--pastel-pink-dark); color: white; border: none; padding: 10px 19px; border-radius: 10px; font-weight: 700; cursor: pointer; margin-top: 16px; width: 100%; }
      
      .hidden { display: none !important; }
    </style>
</head>
<body>

<?php if (!$in_iframe): ?>
    <aside>
      <div>
        <div class="brand-container">
          <div class="logo-holder"><i class="fa-solid fa-mug-hot" style="font-size:2.2rem; color:#ff007f;"></i></div>
          <div class="brand-title-red">SUGAR</div>
          <div class="brand-title-yellow">BABY</div>
          <div class="brand-subtitle-white">MILK TEA & COFFEE</div>
        </div>
        <div class="nav-section">
          <div class="nav-section-title">Main Menu</div>
          <ul class="nav-links">
            <li class="nav-item active" data-tab="orders">
              <i class="fa-solid fa-receipt"></i> Orders
              <span class="order-notif-badge"><?= $count_incoming ?></span>
            </li>
          </ul>
        </div>
      </div>
      <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
    </aside>

    <header>
      <div class="search-bar"><i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted);"></i><input type="text" placeholder="Search..."></div>
      <div class="user-profile">
        <button class="notification-btn"><i class="fa-regular fa-bell"></i></button>
        <div class="user-trigger" id="userTrigger">
          <div class="user-info"><div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div><div class="user-role">Administrator</div></div>
          <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 2)); ?></div>
        </div>
        <div class="user-popup-box hidden" id="userPopup"><a href="../logout.php" style="display:block;color:#e53e3e;text-decoration:none;font-weight:700;padding:0.5rem 0;">Log Out</a></div>
      </div>
    </header>
<?php endif; ?>

    <div class="main-wrapper">
      <div class="content-container">
        <section id="orders" class="tab-content active">
          <h2 class="page-title">Orders</h2>
          <p class="page-sub">Manage all customer orders.</p>

          <div class="stats-row">
            <div class="stat-card"><div class="stat-num"><?= $count_incoming ?></div><h4>Incoming</h4></div>
            <div class="stat-card"><div class="stat-num"><?= $count_queue ?></div><h4>On Queue</h4></div>
            <div class="stat-card"><div class="stat-num"><?= $count_completed ?></div><h4>Completed</h4></div>
            <div class="stat-card"><div class="stat-num"><?= $count_cancelled ?></div><h4>Cancelled</h4></div>
            <div class="stat-card"><div class="stat-num"><?= $count_refunded ?></div><h4>Refunded</h4></div>
          </div>

          <div class="order-tabs">
            <button class="active-tab" data-tab="incoming">Incoming (<?= $count_incoming ?>)</button>
            <button data-tab="queue">On Queue (<?= $count_queue ?>)</button>
            <button data-tab="completed">Completed (<?= $count_completed ?>)</button>
            <button data-tab="cancelled">Cancelled (<?= $count_cancelled ?>)</button>
            <button data-tab="refunded">Refunded (<?= $count_refunded ?>)</button>
          </div>

          <!-- INCOMING ORDERS -->
          <div class="orders-section active" id="incoming">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Order #</th><th>Time Ordered</th><th>Customer</th><th>Items</th><th>Payment</th><th>Total</th><th>GCash Receipt</th><th>Status</th><th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($incoming_orders && $incoming_orders->num_rows): while ($o = $incoming_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= $o['item_count'] ?? '—' ?></td>
                    <td><?= htmlspecialchars($o['payment_method'] ?? '—') ?></td>
                    <td><strong>₱<?= number_format($o['total_price'],2) ?></strong></td>
                    <td>
                      <?php if (!empty($o['gcash_receipt'])): ?>
                      <button class="action-btn btn-receipt" data-img="../receipts/<?= $o['gcash_receipt'] ?>">
                        <i class="fa-solid fa-file-image"></i> View
                      </button>
                      <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><span class="badge incoming">Incoming</span></td>
                    <td style="text-align:center;">
                      <button class="action-btn btn-view view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                      <button class="action-btn btn-accept accept-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-check"></i> Accept</button>
                      <button class="action-btn btn-decline decline-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-xmark"></i> Decline</button>
                    </td>
                  </tr>
                  <?php endwhile; else: ?>
                  <tr><td colspan="9" style="text-align:center; padding:32px; color:var(--text-muted)">No incoming orders.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ON QUEUE / PREPARING -->
          <div class="orders-section hidden" id="queue">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Order #</th><th>Accepted Time</th><th>Customer</th><th>Items</th><th>Payment</th><th>Total</th><th>Status</th><th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($queue_orders && $queue_orders->num_rows): while ($o = $queue_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= !empty($o['accepted_at']) ? date('M d, Y h:i A', strtotime($o['accepted_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= $o['item_count'] ?? '—' ?></td>
                    <td><?= htmlspecialchars($o['payment_method'] ?? '—') ?></td>
                    <td><strong>₱<?= number_format($o['total_price'],2) ?></strong></td>
                    <td><span class="badge queue">Preparing</span></td>
                    <td style="text-align:center;">
                      <button class="action-btn btn-view view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                      <button class="action-btn btn-complete complete-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-check-double"></i> Complete</button>
                    </td>
                  </tr>
                  <?php endwhile; else: ?>
                  <tr><td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted)">No orders being prepared.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- COMPLETED -->
          <div class="orders-section hidden" id="completed">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Order #</th><th>Completed Time</th><th>Customer</th><th>Items</th><th>Payment</th><th>Total</th><th>Status</th><th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($completed_orders && $completed_orders->num_rows): while ($o = $completed_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= !empty($o['completed_at']) ? date('M d, Y h:i A', strtotime($o['completed_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= $o['item_count'] ?? '—' ?></td>
                    <td><?= htmlspecialchars($o['payment_method'] ?? '—') ?></td>
                    <td><strong>₱<?= number_format($o['total_price'],2) ?></strong></td>
                    <td><span class="badge completed">Completed</span></td>
                    <td style="text-align:center;">
                      <button class="action-btn btn-view view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                    </td>
                  </tr>
                  <?php endwhile; else: ?>
                  <tr><td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted)">No completed orders.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- CANCELLED -->
          <div class="orders-section hidden" id="cancelled">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Order #</th><th>Cancelled Time</th><th>Cancelled By</th><th>Reason</th><th>Customer</th><th>Total</th><th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($cancelled_orders && $cancelled_orders->num_rows): while ($o = $cancelled_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= !empty($o['cancelled_at']) ? date('M d, Y h:i A', strtotime($o['cancelled_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['cancelled_by'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($o['cancel_reason'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><strong>₱<?= number_format($o['total_price'],2) ?></strong></td>
                    <td style="text-align:center;">
                      <button class="action-btn btn-view view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                      <button class="action-btn btn-upload upload-refund" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-upload"></i> Upload Refund</button>
                    </td>
                  </tr>
                  <?php endwhile; else: ?>
                  <tr><td colspan="7" style="text-align:center; padding:32px; color:var(--text-muted)">No cancelled orders.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- REFUNDED -->
          <div class="orders-section hidden" id="refunded">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Order #</th><th>Refunded Time</th><th>Customer</th><th>Total</th><th>Refund Proof</th><th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($refunded_orders && $refunded_orders->num_rows): while ($o = $refunded_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= !empty($o['refunded_at']) ? date('M d, Y h:i A', strtotime($o['refunded_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><strong>₱<?= number_format($o['total_price'],2) ?></strong></td>
                    <td>
                      <?php if (!empty($o['refund_receipt'])): ?>
                      <button class="action-btn btn-receipt" data-img="../receipts/<?= $o['refund_receipt'] ?>">
                        <i class="fa-solid fa-file-image"></i> View
                      </button>
                      <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                      <button class="action-btn btn-view view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                    </td>
                  </tr>
                  <?php endwhile; else: ?>
                  <tr><td colspan="6" style="text-align:center; padding:32px; color:var(--text-muted)">No refunded orders.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- ORDER DETAIL MODAL -->
    <div id="orderModal" class="modal">
      <div class="modal-content">
        <div class="modal-header"><h3>Order Details</h3><button class="close-modal">&times;</button></div>
        <div id="modalBody"></div>
      </div>
    </div>

    <!-- VIEW RECEIPT MODAL -->
    <div id="receiptImageModal" class="modal">
      <div class="modal-content" style="max-width:450px;">
        <div class="modal-header"><h3>Receipt Preview</h3><button class="close-modal">&times;</button></div>
        <img id="receiptFullImg" style="width:100%; border-radius:8px;">
      </div>
    </div>

    <!-- DECLINE REASON MODAL -->
    <div id="declineModal" class="modal">
      <div class="modal-content" style="max-width:420px;">
        <div class="modal-header"><h3>Decline Order</h3><button class="close-modal">&times;</button></div>
        <p style="margin-bottom:12px;">Select reason for declining:</p>
        <select id="declineReason" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); font-size:14px; font-weight:500;">
          <option value="">-- Choose a reason --</option>
          <option value="Outside working hours">Outside working hours</option>
          <option value="Product unavailable">Product unavailable</option>
          <option value="Cannot fulfill order">Cannot fulfill order</option>
          <option value="Other">Other (please specify)</option>
        </select>
        <input type="text" id="customReason" placeholder="Type your reason here..." style="display:none; width:100%; padding:10px; margin-top:10px; border-radius:8px; border:1px solid var(--border); font-size:14px;">
        <button id="confirmDecline" class="refund-confirm-btn" style="width:100%; margin-top:16px;">Confirm Decline</button>
      </div>
    </div>

    <!-- UPLOAD REFUND MODAL -->
    <div id="receiptUploadModal" class="modal">
      <div class="modal-content" style="max-width:420px;">
        <div class="modal-header"><h3>Upload Refund Receipt</h3><button class="close-modal">&times;</button></div>
        <p style="margin-bottom:8px;">Order: <strong id="refundOrderId"></strong></p>
        <input type="file" id="receiptUpload" accept="image/*" style="margin:12px 0;">
        <img id="previewImg" style="width:100%; max-height:200px; object-fit:contain; border-radius:8px; display:none; margin-bottom:12px;">
        <button id="confirmRefund" class="refund-confirm-btn" style="width:100%;">Save Refund</button>
      </div>
    </div>

    <!-- JAVASCRIPT INTEGRATION -->
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        const orderTabs = document.querySelectorAll(".order-tabs button");
        const orderSections = document.querySelectorAll(".orders-section");
        const orderModal = document.getElementById("orderModal");
        const modalBody = document.getElementById("modalBody");
        const receiptModal = document.getElementById("receiptImageModal");
        const receiptFullImg = document.getElementById("receiptFullImg");
        const declineModal = document.getElementById("declineModal");
        const declineReason = document.getElementById("declineReason");
        const confirmDecline = document.getElementById("confirmDecline");
        const refundModal = document.getElementById("receiptUploadModal");
        const refundOrderId = document.getElementById("refundOrderId");
        const receiptUpload = document.getElementById("receiptUpload");
        const previewImg = document.getElementById("previewImg");
        const confirmRefund = document.getElementById("confirmRefund");
        const closeModals = document.querySelectorAll(".close-modal");

        let activeOrderId = null;
        let uploadedProof = null;

        orderTabs.forEach(tab => {
          tab.addEventListener("click", () => {
            orderTabs.forEach(b => b.classList.remove("active-tab"));
            tab.classList.add("active-tab");
            const targetTab = tab.getAttribute("data-tab");
            orderSections.forEach(sec => sec.classList.add("hidden"));
            document.getElementById(targetTab)?.classList.remove("hidden");
          });
        });

        document.addEventListener("click", e => {
          if (e.target.closest(".view-order")) {
            activeOrderId = e.target.closest(".view-order").dataset.id;
            modalBody.innerHTML = `<p style="padding:40px;text-align:center;">Loading...</p>`;
            orderModal.classList.add("show");
            fetch(`../api/get_order_details.php?id=${activeOrderId}`).then(r => r.text()).then(h => modalBody.innerHTML = h);
          }
        });

        document.addEventListener("click", e => {
          if (e.target.closest(".accept-order")) {
            activeOrderId = e.target.closest(".accept-order").dataset.id;
            if(!confirm("Accept this order?")) return;
            fetch("../api/update_order_status.php", {
              method: "POST",
              headers: {"Content-Type":"application/x-www-form-urlencoded"},
              body: `order_id=${activeOrderId}&new_status=preparing`
            }).then(r => r.json()).then(d => {
              if(d.ok) { alert("Order accepted!"); location.reload(); } else alert("Error: " + (d.msg || "Update failed"));
            });
          }
        });

        document.addEventListener("click", e => {
          if (e.target.closest(".decline-order")) {
            activeOrderId = e.target.closest(".decline-order").dataset.id;
            document.getElementById("declineReason").value = "";
            document.getElementById("customReason").value = "";
            document.getElementById("customReason").style.display = "none";
            declineModal.classList.add("show");
          }
        });

        document.getElementById("declineReason").addEventListener("change", () => {
          document.getElementById("customReason").style.display = document.getElementById("declineReason").value === "Other" ? "block" : "none";
        });

        confirmDecline.onclick = async () => {
          const selectReason = declineReason.value.trim();
          const customReason = document.getElementById("customReason").value.trim();
          const finalReason = selectReason === "Other" ? customReason : selectReason;
          if (!selectReason) return alert("Please select a reason first");
          if (selectReason === "Other" && !customReason) return alert("Please type your reason");

          fetch("../api/update_order_status.php", {
            method: "POST",
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body: `order_id=${activeOrderId}&new_status=cancelled&reason=${encodeURIComponent(finalReason)}`
          }).then(r => r.json()).then(result => {
            if(result.ok) { alert("Order declined!"); location.reload(); } else alert("Error: " + result.msg);
          });
        };

        document.addEventListener("click", e => {
          if (e.target.closest(".complete-order")) {
            activeOrderId = e.target.closest(".complete-order").dataset.id;
            if(!confirm("Mark order as completed?")) return;
            fetch("../api/update_order_status.php", {
              method: "POST",
              headers: {"Content-Type":"application/x-www-form-urlencoded"},
              body: `order_id=${activeOrderId}&new_status=completed`
            }).then(r => r.json()).then(d => {
              if(d.ok) { alert("Order completed!"); location.reload(); } else alert("Error: " + (d.msg || "Update failed"));
            });
          }
        });

        document.addEventListener("click", e => {
          if (e.target.closest(".view-receipt")) {
            receiptFullImg.src = e.target.closest(".view-receipt").dataset.img;
            receiptModal.classList.add("show");
          }
        });

        document.addEventListener("click", e => {
          if (e.target.closest(".upload-refund")) {
            activeOrderId = e.target.closest(".upload-refund").dataset.id;
            refundOrderId.textContent = `Order #${activeOrderId}`;
            receiptUpload.value = ""; previewImg.style.display = "none";
            refundModal.classList.add("show");
          }
        });

        receiptUpload.addEventListener("change", e => {
          const f = e.target.files[0]; if(!f) return;
          const r = new FileReader();
          r.onload = ev => { previewImg.src = ev.target.result; previewImg.style.display = "block"; uploadedProof = f; };
          r.readAsDataURL(f);
        });

        confirmRefund.addEventListener("click", () => {
          if(!uploadedProof) return alert("Select receipt first");
          if(!confirm("Save refund receipt?")) return;
          const fd = new FormData();
          fd.append("order_id", activeOrderId); fd.append("refund_file", uploadedProof);
          fetch("../api/upload_refund_receipt.php", {method:"POST", body:fd})
            .then(r => r.json()).then(d => { alert(d.ok ? "Refund saved!" : "Error"); if(d.ok) location.reload(); });
        });

        closeModals.forEach(b => b.addEventListener("click", () => {
          [orderModal,receiptModal,declineModal,refundModal].forEach(m => m.classList.remove("show"));
        }));
        window.addEventListener("click", e => {
          if([orderModal,receiptModal,declineModal,refundModal].includes(e.target)) e.target.classList.remove("show");
        });
      });
    </script>
</body>
</html>