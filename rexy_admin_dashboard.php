<?php
session_start();
require_once 'db.php';

// Guard clause: Siguraduhing naka-login at Admin ang user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Store Manager';

// --- DASHBOARD METRICS QUERIES ---
// Total Revenue
$rev_query = $conn->query("SELECT SUM(total_price) AS revenue FROM orders WHERE status = 'completed'");
$total_revenue = $rev_query ? ($rev_query->fetch_assoc()['revenue'] ?? 0) : 0;

// Pending Orders
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


// Fetch all historical orders chronologically (newest first)
$history_query = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
$all_orders = [];
if ($history_query) {
    while ($row = $history_query->fetch_assoc()) {
        $all_orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugar Baby Shop | Milk Tea & Coffee Admin</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">

    <style>
      :root {
        --bg-main: #fcfbfa;            
        --bg-sidebar: #cbebff;         
        --bg-card: #ffffff;            
        --text-main: #2d3748;          
        --text-muted: #62728d;        
        --pastel-yellow: #fff2a8;     
        --pastel-yellow-dark: #f0db6e;
        --pastel-blue: #cbebff;       
        --pastel-pink: #ffd6e7;       
        --pastel-pink-dark: #fca1c9;  
        --border: #f0e6db;             
        --sidebar-text: #2c3e50;
        --sidebar-active-bg: #fff2a8;
        --sidebar-active-text: #2c3e50;
        --card-shadow: 0 4px 15px rgba(203, 235, 255, 0.4);
      }
      body.dark-mode {
        --bg-main: #1a1e24;
        --bg-sidebar: #13171c;
        --bg-card: #22272e;
        --text-main: #f0f4f8;
        --text-muted: #9fb3c8;
        --border: #2d3748;
        --sidebar-text: #e2e8f0;
        --sidebar-active-bg: #3b4a6b;
        --sidebar-active-text: #fff2a8;
        --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      }
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Inter', sans-serif;
        transition: background-color 0.2s, color 0.2s, border-color 0.2s;
      }
      body {
        background-color: var(--bg-main);
        color: var(--text-main);
        display: flex;
        min-height: 100vh;
        overflow-x: hidden;
      }
      aside {
        width: 270px;
        background-color: var(--bg-sidebar);
        padding: 1.75rem 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-shrink: 0;
        border-right: 2px solid var(--border);
      }
      .brand-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 2rem;
      }
      .logo-holder {
        width: 95px;
        height: 95px;
        background-color: #ffffff;
        border: 3px solid var(--pastel-pink-dark);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.85rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        overflow: hidden;
      }
      .logo-holder img {
        width: 80%;
        height: 80%;
        object-fit: contain;
      }
      .brand-title-red {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 2.2rem;
        font-weight: 800;
        color: #ff0015;
        text-transform: uppercase;
        line-height: 0.9;
        text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000;
      }
      .brand-title-yellow {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 2.2rem;
        font-weight: 800;
        color: #ffe600;
        text-transform: uppercase;
        line-height: 0.95;
        margin-bottom: 0.35rem;
        text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000;
      }
      .brand-subtitle-white {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        text-shadow: -1.5px -1.5px 0 #000, 1.5px -1.5px 0 #000, -1.5px 1.5px 0 #000, 1.5px 1.5px 0 #000;
      }
      .nav-section { margin-bottom: 1.5rem; }
      .nav-section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
        font-weight: 700;
        padding-left: 0.5rem;
      }
      .nav-links { list-style: none; }
      .nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: var(--sidebar-text);
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        margin-bottom: 0.35rem;
        cursor: pointer;
      }
      .nav-item:hover, .nav-item.active {
        background-color: var(--sidebar-active-bg);
        color: var(--sidebar-active-text);
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      }
      .logout-btn {
        background-color: var(--pastel-pink);
        color: #2c3e50;
        border: none;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        text-decoration: none;
        margin-top: auto;
      }
      .logout-btn:hover { background-color: var(--pastel-pink-dark); color: #ffffff; }
      
      .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
      header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 2rem;
        background-color: var(--bg-card);
        border-bottom: 2px solid var(--border);
      }
      .search-bar {
        display: flex;
        align-items: center;
        background: var(--bg-main);
        padding: 0.6rem 1.2rem;
        border-radius: 20px;
        width: 320px;
        border: 2px solid var(--pastel-yellow-dark);
      }
      .search-bar input {
        border: none;
        background: transparent;
        outline: none;
        margin-left: 0.5rem;
        color: var(--text-main);
        width: 100%;
      }
      .user-profile { display: flex; align-items: center; gap: 1rem; position: relative; }
      .notification-btn {
        background: var(--pastel-pink);
        border: none;
        color: var(--text-main);
        font-size: 1rem;
        cursor: pointer;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .notification-dropdown {
        position: absolute;
        right: 0;
        top: 2.75rem;
        width: 250px;
        background: var(--bg-card);
        border: 2px solid var(--pastel-pink);
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 1rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        text-align: center;
        z-index: 100;
      }
      .user-trigger {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 0.4rem 0.6rem;
        border-radius: 10px;
      }
      .user-trigger:hover { background-color: var(--bg-main); }
      .user-info { text-align: right; }
      .user-name { font-weight: 700; font-size: 0.875rem; color: var(--text-main); }
      .user-role { font-size: 0.75rem; color: var(--text-muted); }
      .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--pastel-yellow);
        color: #2c3e50;
        border: 2px solid var(--pastel-yellow-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
      }
      .user-popup-box {
        position: absolute;
        top: 3.5rem;
        right: 0;
        width: 260px;
        background: var(--bg-card);
        border: 2px solid var(--pastel-yellow-dark);
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        padding: 1.25rem;
        z-index: 200;
      }
      .popup-user-header {
        border-bottom: 2px solid var(--bg-main);
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
      }
      .popup-menu { list-style: none; }
      .popup-menu-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.6rem 0;
        color: var(--text-main);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
      }
      .content-container { padding: 2rem; }
      .tab-content { display: none; }
      .tab-content.active { display: block; }
      
      .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
      }
      .stat-card {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: 16px;
        border: 2px solid var(--border);
        box-shadow: var(--card-shadow);
      }
      .stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
      .stat-icon { font-size: 1.25rem; color: #2c3e50; background-color: var(--pastel-yellow); padding: 0.6rem; border-radius: 12px; }
      .stat-card .value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); }
      
      .inventory-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
      }
      .action-controls { display: flex; align-items: center; gap: 1rem; }
      .sort-select {
        background-color: var(--bg-card);
        color: var(--text-main);
        border: 2px solid var(--pastel-yellow-dark);
        padding: 0.6rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 10px;
      }
      .btn-toggle {
        background-color: var(--pastel-pink);
        color: #2c3e50;
        border: none;
        padding: 0.6rem 1.2rem;
        font-size: 0.875rem;
        font-weight: 700;
        border-radius: 10px;
        cursor: pointer;
      }
      .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.5rem;
      }
      .settings-card {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: 16px;
        border: 2px solid var(--border);
        max-width: 500px;
      }
      .setting-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; }
      .switch { position: relative; display: inline-block; width: 46px; height: 24px; }
      .switch input { opacity: 0; width: 0; height: 0; }
      .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0; transition: .4s; border-radius: 24px;
      }
      .slider:before {
        position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
        background-color: white; transition: .4s; border-radius: 50%;
      }
      input:checked + .slider { background-color: var(--pastel-pink-dark); }
      input:checked + .slider:before { transform: translateX(22px); }
      .hidden { display: none !important; }
    
    /* --- ORDERS MODULE --- */
      .nav-item { position: relative; }
      .order-notif-badge {
        position: absolute;
        top: 6px;
        right: 8px;
        background: #ef4444;
        color: white;
        font-size: 11px;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .stats-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
        align-items: stretch;
      }
      .stat-card {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px 16px;
      }
      .stat-num {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 4px;
        text-align: center;
      }
      .stat-card h4 {
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        margin: 0;
      }
      .order-tabs { 
        display: flex; 
        gap: 12px; 
        margin-bottom: 24px;      
        flex-wrap: wrap; 
      }
      .order-tabs button { 
        padding: 10px 19px;  
        border-radius: 10px; 
        border: 2px solid var(--border); 
        background: var(--bg-card); 
        color: var(--text-main); 
        font-weight: 600; 
        cursor: pointer; 
      }
      .order-tabs button.active-tab { 
        background: var(--pastel-yellow); 
        border-color: var(--pastel-yellow-dark); 
        font-weight: 700; 
      }
      .table-container { 
        background: var(--bg-card); 
        border-radius: 16px; 
        border: 2px solid var(--border); 
        overflow-x: visible; 
        width: 100%;
      }
      table { width: 100%; 
      border-collapse: collapse; 
      font-size: 14px;
      table-layout: auto;
      }
      th, td { 
        padding: 12px 10px;     
        text-align: left; 
        border-bottom: 1px solid var(--border); 
        white-space: nowrap; 
        width: 1%; 
      }
      th { 
        background: var(--pastel-blue); 
        color: var(--sidebar-text); 
        font-weight: 700; 
      }
      .incoming { 
        background: #fff3e0; 
        color: #e65100; 
        padding: 5px 10px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 12px;
       }
      .queue { 
        background: #e3f2fd; 
        color: #1565c0; 
        padding: 5px 10px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 12px;
        }
      .completed { 
        background: #e8f5e9; 
        color: #2e7d32; 
        padding: 5px 10px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 12px; 
      }
      .refund-pending { 
        background: #fff9c4; 
        color: #f57f17; 
        padding: 5px 10px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 12px; 
      }
      
      button.view-receipt-btn, 
      button.view-order, 
      button.accept-order, 
      button.decline-order, 
      button.complete-order, 
      button.upload-refund { 
        padding: 6px 9px; 
        border-radius: 8px; 
        border: none; 
        font-weight: 600; 
        font-size: 12px;
        cursor: pointer; 
        margin: 0 2px; 
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap; 
        min-width: auto;
      }
      button.view-receipt-btn:hover, 
      button.view-order:hover, 
      button.accept-order:hover, 
      button.decline-order:hover, 
      button.complete-order:hover, 
      button.upload-refund:hover { 
        opacity: 0.85;
      }

      .view-receipt-btn { 
        background: var(--pastel-blue); 
        color: #1565c0; 
      }
      .view-order { 
        background: var(--pastel-yellow); 
        color: #856404; 
      }
      .accept-order { 
        background: #c8e6c9; 
        color: #2e7d32; 
      }
      .decline-order { 
        background: #ffcdd2; 
        color: #c62828; 
      }
      .complete-order { 
        background: #c8e6c9; 
        color: #2e7d32; 
      }
      .upload-refund { 
        background: #fff9c4; 
        color: #f57f17; 
      }
      .modal { 
        display: none; 
        position: fixed; 
        inset: 0; 
        background: rgba(0,0,0,0.4); 
        align-items: center; 
        justify-content: center; 
        z-index: 999; 
        padding: 16px;
      }
      .modal.show { 
        display: flex; 
      }
      .modal-content { 
        background: var(--bg-card); 
        padding: 24px; 
        border-radius: 16px; 
        border: 2px solid var(--border); 
        width: 100%; 
        max-width: 580px; 
        max-height: 85vh;
        overflow-y: auto;
        box-sizing: border-box;
      }
      .modal-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 16px; 
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
      }
      .modal-content p,
      .modal-content div {
        max-width: 100%;
        word-wrap: break-word; 
        overflow-wrap: break-word;
      }
      .modal-content div[style*="background:#fffdf5"] {
        max-width: 100%;
        box-sizing: border-box;
      }
      .close-modal { 
        background: none; 
        border: none; 
        font-size: 24px; 
        cursor: pointer; 
        color: var(--text-muted); 
      }
      .refund-confirm-btn { 
        background: var(--pastel-pink-dark); 
        color: white; 
        border: none; 
        padding: 10px 19px; 
        border-radius: 10px; 
        font-weight: 700; 
        cursor: pointer; 
        margin-top: 16px; 
      }
  
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside>
      <div>
        <div class="brand-container">
          <div class="logo-holder">
            <img src="images/SUGAR BABY 2.png" alt="Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <i class="fa-solid fa-mug-hot logo-placeholder-icon" style="display:none; font-size:2.2rem; color:#ff007f;"></i>
          </div>
          <div class="brand-title-red">SUGAR</div>
          <div class="brand-title-yellow">BABY</div>
          <div class="brand-subtitle-white">MILK TEA & COFFEE</div>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Main Menu</div>
          <ul class="nav-links">
            <li class="nav-item active" data-tab="dashboard">
              <i class="fa-solid fa-chart-pie"></i> Dashboard
            </li>
            <li class="nav-item" data-tab="menu">
              <i class="fa-solid fa-mug-hot"></i> Menu
            </li>
            <li class="nav-item" data-tab="orders">
              <i class="fa-solid fa-receipt"></i> Orders
              <?php if ($count_incoming > 0): ?>
                <span class="order-notif-badge"><?= $count_incoming ?></span>
              <?php endif; ?>
            </li>
            <li class="nav-item" data-tab="history">
              <i class="fa-solid fa-clock-rotate-left"></i> History
            </li>
          </ul>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">System</div>
          <ul class="nav-links">
            <li class="nav-item" data-tab="settings">
              <i class="fa-solid fa-gear"></i> Settings
            </li>
            <li class="nav-item" data-tab="about">
              <i class="fa-solid fa-circle-info"></i> About Us
            </li>
          </ul>
        </div>
      </div>

      <!-- Log out Button mapped to logout.php -->
      <a href="logout.php" class="logout-btn">
        <i class="fa-solid fa-right-from-bracket"></i> Log Out
      </a>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-wrapper">
      <header>
        <div class="search-bar">
          <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted);"></i>
          <input type="text" id="searchInput" placeholder="Search menu or orders...">
        </div>

        <div class="user-profile">
          <div class="notification-wrapper">
            <button class="notification-btn" id="notifBtn">
              <i class="fa-solid fa-bell"></i>
            </button>
            <div class="notification-dropdown hidden" id="notifDropdown">
              <p>No notifications to show</p>
            </div>
          </div>

          <!-- Dynamic Display of Admin Name -->
          <div class="user-trigger" id="userTrigger">
            <div class="user-info">
              <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
              <div class="user-role">Administrator</div>
            </div>
            <div class="avatar">
              <?php echo strtoupper(substr($admin_name, 0, 2)); ?>
            </div>
          </div>

          <!-- Profile Popup Box -->
          <div class="user-popup-box hidden" id="userPopup">
            <div class="popup-user-header">
              <h4><?php echo htmlspecialchars($admin_name); ?></h4>
              <p><?php echo $_SESSION['user_email'] ?? 'admin@sugarbaby.clsu.edu.ph'; ?></p>
            </div>
            <ul class="popup-menu">
              <li>
                <a href="#" class="popup-menu-item" onclick="switchTab('settings')">
                  <span><i class="fa-solid fa-gear"></i> Settings</span>
                </a>
              </li>
              <li class="popup-menu-item">
                <span><i class="fa-solid fa-moon"></i> Dark Mode</span>
                <label class="switch">
                  <input type="checkbox" class="darkModeToggle">
                  <span class="slider"></span>
                </label>
              </li>
              <li>
                <a href="logout.php" class="popup-menu-item" style="color: #e53e3e;">
                  <span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </header>

      <!-- CONTENT CONTAINER -->
      <div class="content-container">
        
        <!-- 1. DASHBOARD VIEW -->
        <section id="dashboard" class="tab-content active">
          <h2 style="margin-bottom: 1.5rem; color: var(--text-main);">Sugar Baby Dashboard</h2>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-header">
                <h3>Daily Sales</h3>
                <i class="fa-solid fa-peso-sign stat-icon"></i>
              </div>
              <div class="value">₱<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-header">
                <h3>Cups Sold</h3>
                <i class="fa-solid fa-mug-saucer stat-icon"></i>
              </div>
              <div class="value">342</div>
            </div>
            <div class="stat-card">
              <div class="stat-header">
                <h3>Pending Orders</h3>
                <i class="fa-solid fa-clock stat-icon"></i>
              </div>
              <div class="value"><?php echo $pending_orders; ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-header">
                <h3>Active Flavors</h3>
                <i class="fa-solid fa-wine-glass-side stat-icon"></i>
              </div>
              <div class="value">24</div>
            </div>
          </div>
        </section>

        <!-- 2. MENU VIEW -->
        <section id="menu" class="tab-content">
          <div class="inventory-header">
            <div>
              <h2 style="color: var(--text-main);">Sugar Baby Menu</h2>
              <p style="color: var(--text-muted);">Explore our Milk Tea & Coffee offerings</p>
            </div>
            <div class="action-controls">
              <select id="sortSelect" class="sort-select">
                <option value="" disabled selected>Sort By</option>
                <option value="price-low">▼ Price Low to High</option>
                <option value="price-high">▼ Price High to Low</option>
              </select>
              <button id="toggleBtn" class="btn-toggle">
                <i class="fa-solid fa-eye"></i> <span>Show Products</span>
              </button>
            </div>
          </div>
          <div id="statusMsg" style="text-align:center; color: var(--text-muted); margin-top:3rem;">
            Click "Show Products" to view our menu.
          </div>
          <div id="productGrid" class="products-grid hidden"></div>
        </section>

        <!-- ==============================================
        ONLY UPDATED: ORDERS VIEW SECTION
        ============================================== -->
        <section id="orders" class="tab-content">
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
                    <td>#<?= $o['id'] ?></td>
                    <td><?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= $o['item_count'] ?? '—' ?></td>
                    <td><?= htmlspecialchars($o['payment_method'] ?? '—') ?></td>
                    <td>₱<?= number_format($o['total_price'],2) ?></td>
                    <td>
                      <?php if (!empty($o['gcash_receipt'])): ?>
                      <button class="btn view-receipt" data-img="../receipts/<?= $o['gcash_receipt'] ?>">
                        <i class="fa-solid fa-file-image"></i> View
                      </button>
                      <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><span class="badge incoming">Incoming</span></td>
                    <td style="text-align:center;">
                      <button class="btn view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                      <button class="btn accept-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-check"></i> Accept</button>
                      <button class="btn decline-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-xmark"></i> Decline</button>
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
                    <th>Order #</th><th>Accepted Time</th>
                    <th>Customer</th><th>Items</th>
                    <th>Payment</th><th>Total</th>
                    <th>Status</th>
                    <th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($queue_orders && $queue_orders->num_rows): while ($o = $queue_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td>#<?= $o['id'] ?></td>
                    <td><?= !empty($o['accepted_at']) ? date('M d, Y h:i A', strtotime($o['accepted_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= $o['item_count'] ?? '—' ?></td>
                    <td><?= htmlspecialchars($o['payment_method'] ?? '—') ?></td>
                    <td>₱<?= number_format($o['total_price'],2) ?></td>
                    <td><span class="badge queue">Preparing</span></td>
                    <td style="text-align:center;">
                      <button class="view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                      <button class="complete-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-check-double"></i> Complete</button>
                    </td>
                  </tr>
                  <?php endwhile; else: ?>
                  <tr>
                    <td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted)">No orders being prepared.</td>
                  </tr>
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
                    <th>Order #</th>
                    <th>Completed Time</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($completed_orders && $completed_orders->num_rows): while ($o = $completed_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td>#<?= $o['id'] ?></td>
                    <td><?= !empty($o['completed_at']) ? date('M d, Y h:i A', strtotime($o['completed_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= $o['item_count'] ?? '—' ?></td>
                    <td><?= htmlspecialchars($o['payment_method'] ?? '—') ?></td>
                    <td>₱<?= number_format($o['total_price'],2) ?></td>
                    <td><span class="badge completed">Completed</span></td>
                    <td style="text-align:center;">
                      <button class="btn view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                    </td>
                  </tr>
                  <?php endwhile; else: ?>
                  <tr>
                    <td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted)">No completed orders.</td>
                  </tr>
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
                    <th>Order #</th>
                    <th>Cancelled Time</th>
                    <th>Cancelled By</th>
                    <th>Reason</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($cancelled_orders && $cancelled_orders->num_rows): while ($o = $cancelled_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td>#<?= $o['id'] ?></td>
                    <td><?= !empty($o['cancelled_at']) ? date('M d, Y h:i A', strtotime($o['cancelled_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['cancelled_by'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($o['cancel_reason'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td>₱<?= number_format($o['total_price'],2) ?></td>
                    <td style="text-align:center;">
                      <button class="view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
                      <button class="upload-refund" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-upload"></i> Upload Refund</button>
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
                    <th>Order #</th>
                    <th>Refunded Time</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Refund Proof</th>
                    <th style="text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($refunded_orders && $refunded_orders->num_rows): while ($o = $refunded_orders->fetch_assoc()): ?>
                  <tr data-order-id="<?= $o['id'] ?>">
                    <td>#<?= $o['id'] ?></td>
                    <td><?= !empty($o['refunded_at']) ? date('M d, Y h:i A', strtotime($o['refunded_at'])) : '—' ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td>₱<?= number_format($o['total_price'],2) ?></td>
                    <td>
                      <?php if (!empty($o['refund_receipt'])): ?>
                      <button class="btn view-receipt" data-img="../receipts/<?= $o['refund_receipt'] ?>">
                        <i class="fa-solid fa-file-image"></i> View
                      </button>
                      <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                      <button class="btn view-order" data-id="<?= $o['id'] ?>"><i class="fa-solid fa-eye"></i> View</button>
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

        <!-- ORDER DETAIL MODAL -->
        <div id="orderModal" class="modal">
          <div class="modal-content">
            <div class="modal-header">
              <h3>Order Details</h3>
              <button class="close-modal">&times;</button>
            </div>
            <div id="modalBody"></div>
          </div>
        </div>

        <!-- VIEW RECEIPT MODAL -->
        <div id="receiptImageModal" class="modal">
          <div class="modal-content" style="max-width:450px;">
            <div class="modal-header">
              <h3>Receipt Preview</h3>
              <button class="close-modal">&times;</button>
            </div>
            <img id="receiptFullImg" style="width:100%; border-radius:8px;">
          </div>
        </div>

        <!-- DECLINE REASON MODAL -->
        <div id="declineModal" class="modal">
          <div class="modal-content" style="max-width:420px;">
            <div class="modal-header">
              <h3>Decline Order</h3>
              <button class="close-modal">&times;</button>
            </div>
            <p style="margin-bottom:12px;">Select reason for declining:</p>

            <!-- DROPDOWN WITH YOUR REASONS -->
            <select id="declineReason" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); font-size:14px; font-weight:500;">
              <option value="">-- Choose a reason --</option>
              <option value="Outside working hours">Outside working hours</option>
              <option value="Product unavailable">Product unavailable</option>
              <option value="Cannot fulfill order">Cannot fulfill order</option>
              <option value="Other">Other (please specify)</option>
            </select>

            <!-- CUSTOM INPUT: ONLY SHOWS WHEN "OTHER" IS SELECTED -->
            <input type="text" id="customReason" placeholder="Type your reason here..." style="display:none; width:100%; padding:10px; margin-top:10px; border-radius:8px; border:1px solid var(--border); font-size:14px;">

            <button id="confirmDecline" class="refund-confirm-btn" style="width:100%; margin-top:16px;">Confirm Decline</button>
          </div>
        </div>

        <!-- UPLOAD REFUND MODAL -->
        <div id="receiptUploadModal" class="modal">
          <div class="modal-content" style="max-width:420px;">
            <div class="modal-header">
              <h3>Upload Refund Receipt</h3>
              <button class="close-modal">&times;</button>
            </div>
            <p style="margin-bottom:8px;">Order: <strong id="refundOrderId"></strong></p>
            <input type="file" id="receiptUpload" accept="image/*" style="margin:12px 0;">
            <img id="previewImg" style="width:100%; max-height:200px; object-fit:contain; border-radius:8px; display:none; margin-bottom:12px;">
            <button id="confirmRefund" class="refund-confirm-btn" style="width:100%;">Save Refund</button>
          </div>
        </div>

        <!-- 4. HISTORY VIEW (Fronda Module) -->
        <section id="history" class="tab-content">
          <div style="margin-bottom: 1.5rem;">
            <h2 style="color: var(--text-main);">Transaction History</h2>
            <p style="color: var(--text-muted);">Chronological record of all past orders and transactions.</p>
          </div>

          <!-- SUMMARY CARDS FOR COMPLETED, CANCELLED, REFUNDED -->
          <div class="stats-grid" style="margin-bottom: 1.5rem;">
            <div class="stat-card">
              <div class="stat-header">
                <h3>Completed Orders</h3>
                <i class="fa-solid fa-circle-check stat-icon" style="color: #2e7d32; background: #e8f5e9;"></i>
              </div>
              <div class="value"><?php echo $summary['total_completed']; ?></div>
              <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                Earned: ₱<?php echo number_format($summary['completed_revenue'], 2); ?>
              </p>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <h3>Cancelled Orders</h3>
                <i class="fa-solid fa-circle-xmark stat-icon" style="color: #c62828; background: #ffebee;"></i>
              </div>
              <div class="value"><?php echo $summary['total_cancelled']; ?></div>
            </div>

            <div class="stat-card">
              <div class="stat-header">
                <h3>Refunded Orders</h3>
                <i class="fa-solid fa-rotate-left stat-icon" style="color: #ef6c00; background: #fff3e0;"></i>
              </div>
              <div class="value"><?php echo $summary['total_refunded']; ?></div>
            </div>
          </div>

          <!-- SEARCH & FILTER CONTROLS -->
          <div class="inventory-header" style="background: var(--bg-card); padding: 1rem; border-radius: 12px; border: 2px solid var(--border); margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; width: 100%;">
              <!-- Status Filter Dropdown -->
              <select id="historyStatusFilter" class="sort-select">
                <option value="all">All Statuses</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="refunded">Refunded</option>
                <option value="pending">Pending</option>
              </select>

              <!-- Date Range Filters -->
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">From:</label>
                <input type="date" id="historyStartDate" class="sort-select">
              </div>
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">To:</label>
                <input type="date" id="historyEndDate" class="sort-select">
              </div>

              <!-- Search Input -->
              <input type="text" id="historySearchInput" placeholder="Search Order ID or Customer..." class="sort-select" style="flex: 1; min-width: 200px;">

              <button id="resetHistoryFilter" class="btn-toggle">
                <i class="fa-solid fa-arrows-rotate"></i> Reset Filters
              </button>
            </div>
          </div>

          <!-- CHRONOLOGICAL HISTORY TABLE -->
          <div style="background: var(--bg-card); border-radius: 16px; border: 2px solid var(--border); overflow-x: auto; box-shadow: var(--card-shadow);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
              <thead>
                <tr style="background-color: var(--pastel-blue); color: var(--sidebar-text); border-bottom: 2px solid var(--border);">
                  <th style="padding: 1rem;">Order ID</th>
                  <th style="padding: 1rem;">Customer Name</th>
                  <th style="padding: 1rem;">Time Ordered</th>
                  <th style="padding: 1rem;">Pickup Timestamp</th>
                  <th style="padding: 1rem;">Total Cost</th>
                  <th style="padding: 1rem;">Status</th>
                </tr>
              </thead>
              <tbody id="historyTableBody">
                <?php if (!empty($all_orders)): ?>
                  <?php foreach ($all_orders as $order): ?>
                    <?php 
                      $order_date = date('Y-m-d', strtotime($order['created_at']));
                      $badge_style = match($order['status']) {
                        'completed' => 'background-color: #d4edda; color: #155724;',
                        'cancelled' => 'background-color: #f8d7da; color: #721c24;',
                        'refunded'  => 'background-color: #fff3cd; color: #856404;',
                        default     => 'background-color: #e2e3e5; color: #383d41;'
                      };
                    ?>
                    <tr class="history-row" 
                        data-status="<?php echo htmlspecialchars($order['status']); ?>" 
                        data-date="<?php echo $order_date; ?>" 
                        data-search="<?php echo strtolower($order['id'] . ' ' . $order['customer_name']); ?>"
                        style="border-bottom: 1px solid var(--border);">
                      <td style="padding: 1rem; font-weight: 700;">#ORD-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                      <td style="padding: 1rem;"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                      <td style="padding: 1rem;"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                      <td style="padding: 1rem;">
                        <?php 
                          echo !empty($order['pickup_timestamp']) 
                            ? date('M d, Y h:i A', strtotime($order['pickup_timestamp'])) 
                            : '<span style="color: var(--text-muted); font-style: italic;">N/A</span>'; 
                        ?>
                      </td>
                      <td style="padding: 1rem; font-weight: 700;">₱<?php echo number_format($order['total_price'], 2); ?></td>
                      <td style="padding: 1rem;">
                        <span style="padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; <?php echo $badge_style; ?>">
                          <?php echo $order['status']; ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">No recorded transactions found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- 5. SETTINGS VIEW -->
        <section id="settings" class="tab-content">
          <h2 style="margin-bottom: 1.5rem; color: var(--text-main);">Settings</h2>
          <div class="settings-card">
            <div class="setting-item">
              <div>
                <strong>Dark Mode</strong>
                <p style="font-size: 0.8rem; color: var(--text-muted);">Switch between light and dark themes</p>
              </div>
              <label class="switch">
                <input type="checkbox" class="darkModeToggle">
                <span class="slider"></span>
              </label>
            </div>
          </div>
        </section>

        <!-- 6. ABOUT US VIEW -->
        <section id="about" class="tab-content">
          <h2 style="margin-bottom: 1rem; color: var(--text-main);">About Sugar Baby Shop</h2>
          <p style="line-height: 1.6; color: var(--text-muted); max-width: 600px;">
            Welcome to <strong>Sugar Baby Milk Tea & Coffee</strong>! We serve affordable, delicious, and high-quality drinks with various sizes.
          </p>
        </section>

      </div>
    </div>
          
    <!-- JAVASCRIPT INTEGRATION -->
    <script>
      // Tab Switching
      const navItems = document.querySelectorAll('.nav-item');
      const tabContents = document.querySelectorAll('.tab-content');

      function switchTab(targetTab) {
        navItems.forEach(nav => {
          nav.classList.toggle('active', nav.getAttribute('data-tab') === targetTab);
        });
        tabContents.forEach(tab => {
          tab.classList.toggle('active', tab.id === targetTab);
        });
        document.getElementById('userPopup').classList.add('hidden');
      }

      navItems.forEach(item => {
        item.addEventListener('click', () => switchTab(item.getAttribute('data-tab')));
      });

      // Dark Mode Toggle Sync
      const darkModeToggles = document.querySelectorAll('.darkModeToggle');
      darkModeToggles.forEach(toggle => {
        toggle.addEventListener('change', (e) => {
          const isChecked = e.target.checked;
          document.body.classList.toggle('dark-mode', isChecked);
          darkModeToggles.forEach(t => t.checked = isChecked);
        });
      });

      // Notification Dropdown Toggle
      const notifBtn = document.getElementById('notifBtn');
      const notifDropdown = document.getElementById('notifDropdown');
      notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.classList.toggle('hidden');
        document.getElementById('userPopup').classList.add('hidden');
      });

      // User Profile Popup Box Toggle
      const userTrigger = document.getElementById('userTrigger');
      const userPopup = document.getElementById('userPopup');
      userTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        userPopup.classList.toggle('hidden');
        notifDropdown.classList.add('hidden');
      });

      // Close Dropdowns on Outside Click
      document.addEventListener('click', (e) => {
        if (!notifDropdown.classList.contains('hidden')) {
          notifDropdown.classList.add('hidden');
        }
        if (!userPopup.contains(e.target) && !userTrigger.contains(e.target)) {
          userPopup.classList.add('hidden');
        }
      });

      // HISTORY SEARCH & FILTERING LOGIC
      const statusFilter = document.getElementById('historyStatusFilter');
      const startDateInput = document.getElementById('historyStartDate');
      const endDateInput = document.getElementById('historyEndDate');
      const searchInputHistory = document.getElementById('historySearchInput');
      const resetBtn = document.getElementById('resetHistoryFilter');
      const historyRows = document.querySelectorAll('.history-row');

      function filterHistoryTable() {
        const selectedStatus = statusFilter.value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const searchTerm = searchInputHistory.value.toLowerCase().trim();

        historyRows.forEach(row => {
          const rowStatus = row.getAttribute('data-status');
          const rowDate = row.getAttribute('data-date');
          const rowSearchText = row.getAttribute('data-search');

          const statusMatch = (selectedStatus === 'all') || (rowStatus === selectedStatus);

          let dateMatch = true;
          if (startDate && rowDate < startDate) dateMatch = false;
          if (endDate && rowDate > endDate) dateMatch = false;

          const searchMatch = !searchTerm || rowSearchText.includes(searchTerm);

          if (statusMatch && dateMatch && searchMatch) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      }

      if (statusFilter) {
        statusFilter.addEventListener('change', filterHistoryTable);
        startDateInput.addEventListener('change', filterHistoryTable);
        endDateInput.addEventListener('change', filterHistoryTable);
        searchInputHistory.addEventListener('input', filterHistoryTable);

        resetBtn.addEventListener('click', () => {
          statusFilter.value = 'all';
          startDateInput.value = '';
          endDateInput.value = '';
          searchInputHistory.value = '';
          filterHistoryTable();
        });
      }
      
      //Load  orders
      function loadOrders() {
        fetch(location.href)
          .then(r => r.text())
          .then(html => {
            const newDoc = new DOMParser().parseFromString(html, 'text/html');

            document.querySelectorAll('.orders-section').forEach((sec, i) => {
              sec.innerHTML = newDoc.querySelectorAll('.orders-section')[i].innerHTML;
            });

            document.querySelectorAll('.order-tabs button').forEach(tab => {
              let tabKey = tab.getAttribute('data-tab');
              if (tabKey === 'queue') tabKey = 'preparing';

              let freshTab = newDoc.querySelector(`.order-tabs button[data-tab="${tabKey}"]`);
              if (!freshTab) freshTab = newDoc.querySelector(`.order-tabs button[data-tab="${tab.getAttribute('data-tab')}"]`);

              if (freshTab) tab.innerHTML = freshTab.innerHTML;
            });

            document.querySelectorAll('.stats-row .stat-card').forEach((card, i) => {
              const freshCard = newDoc.querySelectorAll('.stats-row .stat-card')[i];
              if (freshCard) card.innerHTML = freshCard.innerHTML;
            });
          });
          // Refresh notification badge
          fetch(location.href)
            .then(r => r.text())
            .then(html => {
              const newDoc = new DOMParser().parseFromString(html, 'text/html');
              const newBadge = newDoc.querySelector('.order-notif-badge');
              const oldBadge = document.querySelector('.order-notif-badge');
              if (newBadge) {
                oldBadge ? oldBadge.replaceWith(newBadge) : document.querySelector('[data-tab="orders"]').appendChild(newBadge);
              } else {
                oldBadge?.remove();
              }
            });
      }

      // ONLY UPDATED: ORDERS MODULE LOGIc
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

        // ==============================================
        // FIXED: TAB SWITCHING — REMOVED DUPLICATE LISTENER
        // ==============================================
        orderTabs.forEach(tab => {
          tab.addEventListener("click", () => {
            orderTabs.forEach(b => b.classList.remove("active-tab"));
            tab.classList.add("active-tab");
            const targetTab = tab.getAttribute("data-tab");
            orderSections.forEach(sec => sec.classList.add("hidden"));
            document.getElementById(targetTab)?.classList.remove("hidden");
            localStorage.setItem("lastOrderTab", targetTab);
          });
        });

        // Load saved tab — MOVED OUTSIDE DUPLICATE BLOCK
        const savedTab = localStorage.getItem("lastOrderTab") || "incoming";
        orderTabs.forEach(b => b.classList.remove("active-tab"));
        document.querySelector(`.order-tabs button[data-tab="${savedTab}"]`)?.classList.add("active-tab");
        orderSections.forEach(s => s.classList.add("hidden"));
        document.getElementById(savedTab)?.classList.remove("hidden");

        // --- VIEW ORDER DETAILS (unchanged) ---
        document.addEventListener("click", e => {
          if (e.target.closest(".view-order")) {
            activeOrderId = e.target.closest(".view-order").dataset.id;
            modalBody.innerHTML = `<p style="padding:40px;text-align:center;">Loading...</p>`;
            orderModal.classList.add("show");
            fetch(`orderBackend/get_order_details.php?id=${activeOrderId}`)
              .then(r => r.text()).then(h => modalBody.innerHTML = h);
          }
        });

        // ==============================================
        //  ACCEPT ORDER — NO FULL RELOAD
        // ==============================================
        document.addEventListener("click", e => {
          if (e.target.closest(".accept-order")) {
            activeOrderId = e.target.closest(".accept-order").dataset.id;
            if(!confirm("Accept this order?")) return;
            fetch("orderBackend/update_order_status.php", {
              method: "POST",
              headers: {"Content-Type":"application/x-www-form-urlencoded"},
              body: `order_id=${activeOrderId}&new_status=preparing`
            }).then(r => r.json()).then(d => {
              if(d.ok) {
                alert("Order accepted!");
                orderModal.classList.remove("show");
                // Auto-switch tab
                orderTabs.forEach(b => b.classList.remove("active-tab"));
                document.querySelector('[data-tab="queue"]').classList.add("active-tab");
                orderSections.forEach(s => s.classList.add("hidden"));
                document.getElementById("queue").classList.remove("hidden");
                localStorage.setItem("lastOrderTab", "queue");
                loadOrders(); 
              } else alert("Error: " + (d.msg || "Update failed"));
            });
          }
        });

        // --- DECLINE ORDER MODAL (unchanged logic) ---
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
          const sel = document.getElementById("declineReason").value;
          document.getElementById("customReason").style.display = sel === "Other" ? "block" : "none";
        });

        // ==============================================
        // CONFIRM DECLINE — NO FULL RELOAD
        // ==============================================
        confirmDecline.onclick = async () => {
          const selectReason = declineReason.value.trim();
          const customReason = document.getElementById("customReason").value.trim();
          const finalReason = selectReason === "Other" ? customReason : selectReason;

          if (!selectReason) return alert("Please select a reason first");
          if (selectReason === "Other" && !customReason) return alert("Please type your reason");

          try {
            const res = await fetch("orderBackend/update_order_status.php", {
              method: "POST",
              headers: {"Content-Type":"application/x-www-form-urlencoded"},
              body: `order_id=${encodeURIComponent(activeOrderId)}&new_status=cancelled&reason=${encodeURIComponent(finalReason)}`
            });
            const result = await res.json();
            if(result.ok) {
              alert("Order declined successfully!");
              declineModal.classList.remove("show");
              // Auto-switch tab
              orderTabs.forEach(b => b.classList.remove("active-tab"));
              document.querySelector('[data-tab="cancelled"]').classList.add("active-tab");
              orderSections.forEach(s => s.classList.add("hidden"));
              document.getElementById("cancelled").classList.remove("hidden");
              localStorage.setItem("lastOrderTab", "cancelled");
              loadOrders(); // 
            } else alert("Error: " + result.msg);
          } catch (err) {
            alert("FAILED:\n" + err.message);
          }
        };

        // ==============================================
        // COMPLETE ORDER — NO FULL RELOAD
        // ==============================================
        document.addEventListener("click", e => {
          if (e.target.closest(".complete-order")) {
            activeOrderId = e.target.closest(".complete-order").dataset.id;
            if(!confirm("Mark this order as completed?")) return;
            fetch("orderBackend/update_order_status.php", {
              method: "POST",
              headers: {"Content-Type":"application/x-www-form-urlencoded"},
              body: `order_id=${activeOrderId}&new_status=completed`
            }).then(r => r.json()).then(d => {
              if(d.ok) {
                alert("Order completed!");
                orderModal.classList.remove("show");
                // Auto-switch tab
                orderTabs.forEach(b => b.classList.remove("active-tab"));
                document.querySelector('[data-tab="completed"]').classList.add("active-tab");
                orderSections.forEach(s => s.classList.add("hidden"));
                document.getElementById("completed").classList.remove("hidden");
                localStorage.setItem("lastOrderTab", "completed");
                loadOrders(); 
              } else alert("Error: " + (d.msg || "Update failed"));
            });
          }
        });

        // --- VIEW RECEIPT (unchanged) ---
        document.addEventListener("click", e => {
          if (e.target.closest(".view-receipt")) {
            receiptFullImg.src = e.target.closest(".view-receipt").dataset.img;
            receiptModal.classList.add("show");
          }
        });

        // --- UPLOAD REFUND ( ALSO FIXED: NO FULL RELOAD) ---
        document.addEventListener("click", e => {
          if (e.target.closest(".upload-refund")) {
            activeOrderId = e.target.closest(".upload-refund").dataset.id;
            refundOrderId.textContent = `Order #${activeOrderId}`;
            receiptUpload.value = "";
            previewImg.style.display = "none";
            refundModal.classList.add("show");
          }
        });

        receiptUpload.addEventListener("change", e => {
          const f = e.target.files[0];
          if(!f) return;
          const r = new FileReader();
          r.onload = ev => { previewImg.src = ev.target.result; previewImg.style.display = "block"; uploadedProof = f; };
          r.readAsDataURL(f);
        });

        confirmRefund.addEventListener("click", () => {
          if(!uploadedProof) return alert("Select receipt first");
          if(!confirm("Save refund receipt?")) return;
          const fd = new FormData();
          fd.append("id", activeOrderId);
          fd.append("receipt", uploadedProof);
          fetch("orderBackend/upload_refund_receipt.php", {method:"POST", body:fd})
            .then(r => r.json()).then(d => {
              alert(d.ok ? "Refund saved!" : "Error");
              if(d.ok) {
                refundModal.classList.remove("show");
                loadOrders(); 
              }
            });
        });

        // --- CLOSE MODALS (unchanged) ---
        closeModals.forEach(b => b.addEventListener("click", () => {
          [orderModal,receiptModal,declineModal,refundModal].forEach(m => m.classList.remove("show"));
        }));
        window.addEventListener("click", e => {
          if([orderModal,receiptModal,declineModal,refundModal].includes(e.target))
            e.target.classList.remove("show");
        });
      });
    </script>
</body>
</html>