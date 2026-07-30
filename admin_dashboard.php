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

        <!-- 3. ORDERS VIEW -->
        <section id="orders" class="tab-content">
          <h2 style="color: var(--text-main);">Active Orders</h2>
          <p style="margin-top: 1rem; color: var(--text-muted);">No current pending orders right now.</p>
        </section>

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
    </script>
</body>
</html>