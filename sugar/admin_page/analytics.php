<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Store Manager';

// ✅ Iframe detection
$in_iframe = isset($_GET['iframe']) && $_GET['iframe'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics - Sugar Baby Admin</title>
  
  <!-- Google Fonts & Font Awesome Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
      /* Pastel UI Color Palette */
      --bg-main: #fcfbfa;            /* Warm Off-White */
      --bg-sidebar: #cbebff;         /* Pastel Light Blue */
      --bg-card: #ffffff;            /* Pure White */
      --text-main: #2d3748;          /* Deep Slate for high contrast */
      --text-muted: #62728d;        /* Muted Blue-Grey */
      --pastel-yellow: #fff2a8;     /* Soft Yellow */
      --pastel-yellow-dark: #f0db6e;/* Darker Yellow for borders/hover */
      --pastel-blue: #cbebff;       /* Soft Light Blue */
      --pastel-pink: #ffd6e7;       /* Soft Pink */
      --pastel-pink-dark: #fca1c9;  /* Pink Border Accent */
      --border: #f0e6db;             /* Light Neutral Border */
      --sidebar-text: #2c3e50;
      --sidebar-active-bg: #fff2a8;
      --sidebar-active-text: #2c3e50;
      --card-shadow: 0 4px 15px rgba(203, 235, 255, 0.4);
    }

    /* Dark Mode Variables */
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
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
    }
    
    <?php if (!$in_iframe): ?>
    /* Styles only if this page is loaded directly (not inside iframe) */
    aside {
      width: 270px;
      background-color: var(--bg-sidebar);
      padding: 1.75rem 1.25rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      flex-shrink: 0;
      border-right: 2px solid var(--border);
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 1000;
      overflow-y: auto;
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
      position: relative;
    }
    .logo-holder img { width: 80%; height: 80%; object-fit: contain; }
    .logo-placeholder-icon { font-size: 2.2rem; color: #ff007f; }
    .brand-title-red {
      font-family: 'Fredoka', cursive, sans-serif;
      font-size: 2.2rem; font-weight: 800; color: #ff0015; text-transform: uppercase;
      text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000;
    }
    .brand-title-yellow {
      font-family: 'Fredoka', cursive, sans-serif;
      font-size: 2.2rem; font-weight: 800; color: #ffe600; text-transform: uppercase;
      text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000;
    }
    .brand-subtitle-white {
      font-family: 'Fredoka', cursive, sans-serif;
      font-size: 0.85rem; font-weight: 700; color: #ffffff; text-transform: uppercase;
      text-shadow: -1.5px -1.5px 0 #000, 1.5px -1.5px 0 #000, -1.5px 1.5px 0 #000, 1.5px 1.5px 0 #000;
    }
    .nav-section { margin-bottom: 1.5rem; }
    .nav-section-title {
      font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);
      letter-spacing: 0.05em; margin-bottom: 0.75rem; font-weight: 700; padding-left: 0.5rem;
    }
    .nav-links { list-style: none; }
    .nav-item {
      display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;
      color: var(--sidebar-text); text-decoration: none; border-radius: 12px;
      font-weight: 600; margin-bottom: 0.35rem; cursor: pointer;
    }
    .nav-item:hover, .nav-item.active {
      background-color: var(--sidebar-active-bg); color: var(--sidebar-active-text); font-weight: 700;
    }
    .logout-btn {
      background-color: var(--pastel-pink); color: #2c3e50; border: none;
      padding: 0.75rem 1rem; border-radius: 12px; font-weight: 700; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 0.5rem;
      transition: all 0.2s ease; margin-top: auto; text-decoration: none;
    }
    .logout-btn:hover { background-color: var(--pastel-pink-dark); color: #ffffff; }
    
    .main-wrapper { margin-left: 270px; width: calc(100% - 270px); display: flex; flex-direction: column; }
    header {
      display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 2rem;
      background-color: var(--bg-card); border-bottom: 2px solid var(--border); height: 70px;
    }
    .search-bar {
      display: flex; align-items: center; background: var(--bg-main); padding: 0.6rem 1.2rem;
      border-radius: 20px; width: 320px; border: 2px solid var(--pastel-yellow-dark);
    }
    .search-bar input { border: none; background: transparent; outline: none; margin-left: 0.5rem; color: var(--text-main); width: 100%; }
    .user-profile { display: flex; align-items: center; gap: 1rem; position: relative; }
    .notification-wrapper { position: relative; }
    .notification-btn {
      background: var(--pastel-pink); border: none; color: var(--text-main); font-size: 1rem;
      cursor: pointer; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    }
    .notification-dropdown {
      position: absolute; right: 0; top: 2.75rem; width: 250px; background: var(--bg-card);
      border: 2px solid var(--pastel-pink); border-radius: 12px; box-shadow: var(--card-shadow);
      padding: 1rem; font-size: 0.875rem; color: var(--text-muted); text-align: center; z-index: 100;
    }
    .user-trigger {
      display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.4rem 0.6rem; border-radius: 10px; transition: background 0.2s;
    }
    .user-trigger:hover { background-color: var(--bg-main); }
    .user-info { text-align: right; }
    .user-name { font-weight: 700; font-size: 0.875rem; color: var(--text-main); }
    .user-role { font-size: 0.75rem; color: var(--text-muted); }
    .avatar {
      width: 40px; height: 40px; border-radius: 50%; background: var(--pastel-yellow);
      color: #2c3e50; border: 2px solid var(--pastel-yellow-dark); display: flex; align-items: center;
      justify-content: center; font-weight: 800;
    }
    .user-popup-box {
      position: absolute; top: 3.5rem; right: 0; width: 260px; background: var(--bg-card);
      border: 2px solid var(--pastel-yellow-dark); border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      padding: 1.25rem; z-index: 200;
    }
    .popup-user-header { border-bottom: 2px solid var(--bg-main); padding-bottom: 0.75rem; margin-bottom: 0.75rem; }
    .popup-user-header h4 { font-size: 1rem; color: var(--text-main); font-weight: 700; }
    .popup-user-header p { font-size: 0.75rem; color: var(--text-muted); }
    .popup-menu { list-style: none; }
    .popup-menu-item {
      display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0;
      color: var(--text-main); text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer;
    }
    .popup-menu-item:hover { color: var(--pastel-pink-dark); }
    .popup-logout {
      margin-top: 0.75rem; padding-top: 0.75rem; border-top: 2px solid var(--bg-main);
      color: #e53e3e; font-weight: 700;
    }
    .switch {
      position: relative; display: inline-block; width: 46px; height: 24px;
    }
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
    <?php else: ?>
    /* If inside iframe, hide sidebar and header */
    aside, header { display: none !important; }
    .main-wrapper { width: 100%; margin: 0; padding: 0; }
    <?php endif; ?>

    .content-container {
      padding: 2rem;
      width: 100%;
      max-width: 100%;
      margin: 0 auto;
      box-sizing: border-box;
    }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* --- ANALYTICS VIEW STYLES & HOVER EFFECTS --- */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: var(--bg-card);
      padding: 1.5rem;
      border-radius: 16px;
      border: 2px solid var(--border);
      box-shadow: var(--card-shadow);
      position: relative;
      overflow: hidden;
      transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
                  box-shadow 0.3s ease,
                  border-color 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-6px);
      border-color: var(--pastel-pink-dark);
      box-shadow: 0 12px 20px rgba(255, 214, 231, 0.4);
    }

    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.75rem;
    }

    .stat-card h3 {
      font-size: 0.875rem;
      color: var(--text-muted);
    }

    .stat-icon {
      font-size: 1.25rem;
      color: #2c3e50;
      background-color: var(--pastel-yellow);
      padding: 0.6rem;
      border-radius: 12px;
      transition: transform 0.4s ease, background-color 0.3s ease;
    }

    .stat-card:hover .stat-icon {
      transform: scale(1.15) rotate(5deg);
      background-color: var(--pastel-pink);
    }

    .stat-card .value {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--text-main);
    }

    /* --- ANALYTICS PERIOD BUTTONS --- */
    .period-btn {
      background: var(--bg-card);
      color: var(--text-main);
      border: 2px solid var(--border);
      padding: 0.65rem 1.5rem;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .period-btn:hover {
      border-color: var(--pastel-pink-dark);
      background: var(--pastel-pink);
    }

    .period-btn.active {
      background: var(--pastel-yellow);
      border-color: var(--pastel-yellow-dark);
      font-weight: 700;
      color: #2c3e50;
    }

    /* MAIN VIEW BUTTONS — MATCH DASHBOARD DESIGN */
    .view-btn {
      background: var(--bg-card);
      color: var(--text-main);
      border: 2px solid var(--border);
      border-radius: 12px;
      padding: 0.65rem 1.5rem;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: var(--card-shadow);
    }

    .view-btn:hover {
      border-color: var(--pastel-pink-dark);
      background: var(--pastel-pink);
      transform: translateY(-2px);
    }

    .view-btn.active {
      background: var(--pastel-yellow);
      border-color: var(--pastel-yellow-dark);
      font-weight: 700;
      color: #2c3e50;
      transform: translateY(0);
    }

    .hidden { display: none !important; }
  </style>
</head>
<body>

<?php if (!$in_iframe): ?>
  <!-- LEFT SIDEBAR (Only shows if not in iframe) -->
  <aside>
    <div>
      <div class="brand-container">
        <div class="logo-holder">
          <img src="../logo.png" alt="Sugar Baby Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
          <i class="fa-solid fa-mug-hot logo-placeholder-icon" style="display: none;"></i>
        </div>
        <div class="brand-title-red">SUGAR</div>
        <div class="brand-title-yellow">BABY</div>
        <div class="brand-subtitle-white">MILK TEA & COFFEE</div>
      </div>

      <div class="nav-section">
        <div class="nav-section-title">Main Menu</div>
        <ul class="nav-links">
          <li><a class="nav-item" href="../admin_dashboard.php"><i class="fa-solid fa-house"></i> Home</a></li>
          <li><a class="nav-item" href="menu.php?iframe=1"><i class="fa-solid fa-glass-water"></i> Menu</a></li>
          <li><a class="nav-item" href="orders.php?iframe=1"><i class="fa-solid fa-receipt"></i> Orders</a></li>
          <li><a class="nav-item" href="history.php?iframe=1"><i class="fa-solid fa-clock-rotate-left"></i> History</a></li>
        </ul>
      </div>

      <div class="nav-section">
        <div class="nav-section-title">System</div>
        <ul class="nav-links">
          <li><a class="nav-item active" href="analytics.php?iframe=1"><i class="fa-solid fa-chart-pie"></i> Analytics</a></li>
          <li><a class="nav-item" href="settings.php?iframe=1"><i class="fa-solid fa-gear"></i> Settings</a></li>
          <li><a class="nav-item" href="about.php?iframe=1"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
        </ul>
      </div>
    </div>

    <a href="../logout.php" class="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Log Out</span>
    </a>
  </aside>
<?php endif; ?>

  <!-- MAIN WRAPPER -->
  <div class="main-wrapper">
<?php if (!$in_iframe): ?>
    <header>
      <div class="search-bar">
        <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted)"></i>
        <input type="text" placeholder="Search menu or orders...">
      </div>

      <div class="user-profile">
        <div class="notification-wrapper">
          <button class="notification-btn" id="notifBtn">
            <i class="fa-regular fa-bell"></i>
          </button>
          <div class="notification-dropdown hidden" id="notifDropdown">
            No notifications to show
          </div>
        </div>

        <div class="user-trigger" id="userTrigger">
          <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
            <div class="user-role">Store Manager</div>
          </div>
          <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 2)); ?></div>
        </div>

        <div class="user-popup-box hidden" id="userPopup">
          <div class="popup-user-header">
            <h4><?php echo htmlspecialchars($admin_name); ?></h4>
            <p><?php echo $_SESSION['user_email'] ?? 'admin@sugarbaby.ph'; ?></p>
          </div>
          <ul class="popup-menu">
            <li class="popup-menu-item">
              <span><i class="fa-solid fa-moon"></i> Dark Mode</span>
              <label class="switch">
                <input type="checkbox" class="darkModeToggle">
                <span class="slider"></span>
              </label>
            </li>
            <li class="popup-menu-item popup-logout" onclick="window.location.href='../logout.php'">
              <span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span>
            </li>
          </ul>
        </div>
      </div>
    </header>
<?php endif; ?>

    <!-- CONTENT AREA -->
    <div class="content-container">

      <!-- 5. ANALYTICS VIEW -->
      <section id="analytics" class="tab-content active">
        <h2 style="margin-bottom: 1.5rem; color: var(--text-main);">Store Analytics</h2>

        <!-- MAIN VIEW SWITCH -->
        <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
          <button class="view-btn active" data-main="weekly">Weekly</button>
          <button class="view-btn" data-main="monthly">Monthly</button>
          <button class="view-btn" data-main="yearly">Yearly</button>
        </div>

        <!-- TOP STATS CARDS -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-header">
              <h3>Revenue</h3>
              <i class="fa-solid fa-peso-sign stat-icon"></i>
            </div>
            <div class="value" id="statRevenue">₱0</div>
          </div>
          <div class="stat-card">
            <div class="stat-header">
              <h3>Cups Sold</h3>
              <i class="fa-solid fa-glass-water stat-icon"></i>
            </div>
            <div class="value" id="statCups">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-header">
              <h3>Orders Completed</h3>
              <i class="fa-solid fa-bag-shopping stat-icon"></i>
            </div>
            <div class="value" id="statOrders">0</div>
          </div>
        </div>

        <!--DYNAMIC BUTTONS: DIRECTLY UNDER CARDS, ABOVE CHARTS -->
        <div id="periodButtons" style="display: flex; gap: 0.75rem; margin: 1.5rem 0 2rem; flex-wrap: wrap;"></div>

        <!-- OVERALL COMPARISON -->
        <div style="display: grid; gap: 1.5rem;">
          <div class="stat-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-main); font-size: 1.1rem;">Revenue (₱)</h3>
            <div style="width: 100%; height: 260px; position: relative;"><canvas id="overallRevChart"></canvas></div>
          <p style="color: var(--text-muted); font-size: 0.9rem; padding: 0 0.5rem;">
            <strong>Insight:</strong> <span id="overallRevInsight"></span>
          </p>
          </div>
          <div class="stat-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-main); font-size: 1.1rem;">Cups Sold</h3>
            <div style="width: 100%; height: 260px; position: relative;"><canvas id="overallCupChart"></canvas></div>
          <p style="color: var(--text-muted); font-size: 0.9rem; padding: 0 0.5rem;">
            <strong>Insight:</strong> <span id="overallCupInsight"></span>
          </p>
          </div>
          <div class="stat-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-main); font-size: 1.1rem;">Orders Completed</h3>
            <div style="width: 100%; height: 260px; position: relative;"><canvas id="overallOrdChart"></canvas></div>
          <p style="color: var(--text-muted); font-size: 0.9rem; padding: 0 0.5rem;">
            <strong>Insight:</strong> <span id="overallOrdInsight"></span>
          </p>
          </div>
        </div>

        <!-- TREND CHARTS -->
        <div style="display: grid; gap: 1.5rem; margin-top: 2rem;">
          <div class="stat-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-main); font-size: 1.1rem;">Revenue Trend</h3>
            <div style="width: 100%; height: 260px; position: relative;"><canvas id="revenueChart"></canvas></div>
          </div>
          <div class="stat-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-main); font-size: 1.1rem;">Cups Sold Trend</h3>
            <div style="width: 100%; height: 260px; position: relative;"><canvas id="cupsChart"></canvas></div>
          </div>
          <div class="stat-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-main); font-size: 1.1rem;">Orders Completed Trend</h3>
            <div style="width: 100%; height: 260px; position: relative;"><canvas id="ordersChart"></canvas></div>
          </div>
        </div>

        <!-- SUMMARY & CONCLUSION -->
        <div class="stat-card" style="padding: 1.75rem; margin-top: 2rem;">
          <h3 style="margin-bottom: 1rem; color: var(--text-main); font-size: 1.1rem;">Summary & Conclusion</h3>
          <ul style="list-style: none; color: var(--text-muted); line-height: 1.8;" id="summaryBox"></ul>
        </div>
      </section>

    </div>
  </div>

  <!-- CHART.JS & JAVASCRIPT LOGIC -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Dark Mode Toggle Sync
    const darkModeToggles = document.querySelectorAll('.darkModeToggle');
    darkModeToggles.forEach(toggle => {
      toggle.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        document.body.classList.toggle('dark-mode', isChecked);
        darkModeToggles.forEach(t => t.checked = isChecked);
      });
    });

    // Notification Dropdown
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    if(notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          notifDropdown.classList.toggle('hidden');
        });
    }

    // User Profile Clickable Popup Box Toggle
    const userTrigger = document.getElementById('userTrigger');
    const userPopup = document.getElementById('userPopup');
    if(userTrigger && userPopup) {
        userTrigger.addEventListener('click', (e) => {
          e.stopPropagation();
          userPopup.classList.toggle('hidden');
          if(notifDropdown) notifDropdown.classList.add('hidden');
        });
    }

    // Close Dropdowns on Click Outside
    document.addEventListener('click', (e) => {
      if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
        notifDropdown.classList.add('hidden');
      }
      if (userPopup && !userPopup.contains(e.target) && !userTrigger.contains(e.target)) {
        userPopup.classList.add('hidden');
      }
    });
  
    let revenueChart, cupsChart, ordersChart, overallRevChart, overallCupChart, overallOrdChart;
    let mainView = 'weekly';
    let selectedPeriod = 1;
    let showOverall = false;
    let isDrilldown = false;

    const analyticsData = {
      weekly: {
        labels: ['Week 1','Week 2','Week 3','Week 4','Week 5'],
        periods: {
          1: { revenue:18450, cups:342, orders:128, avgOrder:"₱144.14", peak:"Sat ₱3,200", change:"+5%", changeText:"higher than previous week", dailyLabels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], revData:[2200,2500,2750,2600,2900,3200,2300], cupData:[42,48,52,47,55,60,38], orderData:[16,18,20,18,21,24,11] },
          2: { revenue:21100, cups:395, orders:145, avgOrder:"₱145.52", peak:"Fri ₱3,600", change:"+14%", changeText:"higher than Week 1", dailyLabels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], revData:[2600,2900,3100,3300,3600,3000,2600], cupData:[50,55,60,65,70,58,37], orderData:[19,21,23,25,27,22,8] },
          3: { revenue:19800, cups:368, orders:137, avgOrder:"₱144.53", peak:"Sat ₱3,400", change:"-6%", changeText:"lower than Week 2", dailyLabels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], revData:[2400,2700,2850,2950,3100,3400,2400], cupData:[45,50,53,56,59,64,41], orderData:[17,19,20,22,23,25,11] },
          4: { revenue:20500, cups:380, orders:141, avgOrder:"₱145.39", peak:"Sun ₱3,500", change:"+4%", changeText:"higher than Week 3", dailyLabels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], revData:[2500,2650,2900,3000,3200,2750,3500], cupData:[47,49,54,57,61,52,60], orderData:[18,19,21,22,24,20,17] },
          5: { revenue:22300, cups:415, orders:152, avgOrder:"₱146.71", peak:"Sat ₱3,800", change:"+9%", changeText:"higher than Week 4 — BEST WEEK!", dailyLabels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], revData:[2700,3000,3200,3400,3600,3800,2600], cupData:[52,57,61,65,69,74,37], orderData:[19,22,24,26,28,30,13] }
        },
        totals: { rev:[18450,21100,19800,20500,22300], cup:[342,395,368,380,415], order:[128,145,137,141,152] }
      },
      monthly: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        periods: {
          1:{ revenue:54000, cups:1020, orders:380, avgOrder:"₱142.11", peak:"Jan 14 ₱2,800", change:"+3%", changeText:"steady start", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[13200,13800,13500,13500], cupData:[250,260,255,255], orderData:[92,98,95,95] },
          2:{ revenue:52500, cups:990, orders:370, avgOrder:"₱141.89", peak:"Feb 21 ₱2,700", change:"-3%", changeText:"slight dip", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[12800,13100,13400,13200], cupData:[240,245,255,250], orderData:[88,92,95,95] },
          3:{ revenue:58500, cups:1100, orders:410, avgOrder:"₱142.68", peak:"Mar 28 ₱3,100", change:"+11%", changeText:"growing", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[14200,14600,14900,14800], cupData:[265,275,285,275], orderData:[98,102,105,105] },
          4:{ revenue:61500, cups:1160, orders:430, avgOrder:"₱143.02", peak:"Apr 15 ₱3,300", change:"+5%", changeText:"steady growth", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[15000,15400,15600,15500], cupData:[280,290,300,290], orderData:[102,108,110,110] },
          5:{ revenue:64500, cups:1220, orders:450, avgOrder:"₱143.33", peak:"May 22 ₱3,500", change:"+5%", changeText:"strong", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[15800,16200,16400,16100], cupData:[295,305,315,305], orderData:[108,112,115,115] },
          6:{ revenue:67500, cups:1280, orders:470, avgOrder:"₱143.62", peak:"Jun 30 ₱3,700", change:"+5%", changeText:"mid-year high", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[16500,17000,17200,16800], cupData:[310,320,330,320], orderData:[112,118,120,120] },
          7:{ revenue:72000, cups:1360, orders:500, avgOrder:"₱144.00", peak:"Jul 18 ₱4,000", change:"+7%", changeText:"BEST MONTH", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[17500,18200,18500,17800], cupData:[330,345,355,330], orderData:[118,125,130,127] },
          8:{ revenue:69000, cups:1300, orders:480, avgOrder:"₱143.75", peak:"Aug 10 ₱3,800", change:"-4%", changeText:"slight slowdown", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[16800,17400,17800,17000], cupData:[315,330,340,315], orderData:[115,120,125,120] },
          9:{ revenue:66000, cups:1240, orders:460, avgOrder:"₱143.48", peak:"Sep 5 ₱3,600", change:"-4%", changeText:"steady", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[16000,16600,17000,16400], cupData:[300,315,325,300], orderData:[110,115,120,115] },
          10:{ revenue:70500, cups:1320, orders:490, avgOrder:"₱143.88", peak:"Oct 25 ₱3,900", change:"+7%", changeText:"holiday pickup", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[17200,17800,18000,17500], cupData:[320,335,345,320], orderData:[118,122,128,122] },
          11:{ revenue:75000, cups:1400, orders:520, avgOrder:"₱144.23", peak:"Nov 15 ₱4,200", change:"+6%", changeText:"holiday rush", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[18500,19000,19200,18300], cupData:[340,355,365,340], orderData:[125,130,135,130] },
          12:{ revenue:81000, cups:1520, orders:560, avgOrder:"₱144.64", peak:"Dec 24 ₱4,800", change:"+8%", changeText:"YEAR-END PEAK", dailyLabels:['Wk1','Wk2','Wk3','Wk4'], revData:[20000,20500,21000,19500], cupData:[370,380,390,380], orderData:[135,140,145,140] }
        },
        totals: { rev:[54000,52500,58500,61500,64500,67500,72000,69000,66000,70500,75000,81000], cup:[1020,990,1100,1160,1220,1280,1360,1300,1240,1320,1400,1520], order:[380,370,410,430,450,470,500,480,460,490,520,560] }
      },
      yearly: {
        labels: ['2025','2026'],
        periods: {
          1:{ revenue:580000, cups:10900, orders:4050, avgOrder:"₱143.21", peak:"Dec ₱81,000", change:"—", changeText:"baseline year", dailyLabels:['Q1','Q2','Q3','Q4'], revData:[164500,193500,207000,246000], cupData:[3110,3620,3880,4290], orderData:[1130,1340,1430,1590] },
          2:{ revenue:642000, cups:12050, orders:4510, avgOrder:"₱142.35", peak:"Dec ₱87,500", change:"+11%", changeText:"higher than 2025 — GROWTH!", dailyLabels:['Q1','Q2','Q3','Q4'], revData:[175500,204000,220500,262000], cupData:[3320,3880,4180,4670], orderData:[1220,1430,1540,1720] }
        },
        totals: { rev:[580000,642000], cup:[10900,12050], order:[4050,4510] }
      }
    };

    // RENDER BUTTONS + OVERALL BUTTON AT END
    function renderButtons() {
      const box = document.getElementById('periodButtons');
      box.innerHTML = '';

      // Show buttons ONLY for currently selected view
      const list = analyticsData[mainView].labels;
      
      list.forEach((name, i) => {
        const btn = document.createElement('button');
        btn.className = `period-btn ${(i + 1 === selectedPeriod && !showOverall) ? 'active' : ''}`;
        btn.dataset.period = i + 1;
        btn.textContent = name;
        btn.onclick = () => selectPeriod(i + 1);
        box.appendChild(btn);
      });

      // Always add Overall Comparison button last
      const overallBtn = document.createElement('button');
      overallBtn.className = `period-btn ${showOverall ? 'active' : ''}`;
      overallBtn.textContent = 'Overall Comparison';
      overallBtn.onclick = () => {
        showOverall = true;
        isDrilldown = false;
        selectedPeriod = null;
        renderButtons();
        updateAll();
      };
      box.appendChild(overallBtn);
    }

    function updateAll() {
      // Get all containers to toggle
      const statCards = document.querySelector('.stats-grid');
      const chartRow = document.querySelector('.stat-card:has(#revenueChart)').parentElement;
      const summarySection = document.getElementById('summaryBox').parentElement;
      const overallSection = document.querySelector('.stat-card:has(#overallRevChart)').parentElement;

      if (showOverall) {
        statCards.style.display = 'none';
        chartRow.style.display = 'none';
        summarySection.style.display = 'none';
        overallSection.style.display = 'block';
        [revenueChart, cupsChart, ordersChart].forEach(c => { if(c) c.destroy(); });
        revenueChart = cupsChart = ordersChart = null;
        renderOverallBar();
      } else {
        statCards.style.display = 'grid';
        chartRow.style.display = 'grid';
        summarySection.style.display = 'block';
        overallSection.style.display = 'none';

        const d = analyticsData[mainView].periods[selectedPeriod];
        document.getElementById('statRevenue').textContent = `₱${d.revenue.toLocaleString()}`;
        document.getElementById('statCups').textContent = d.cups.toLocaleString();
        document.getElementById('statOrders').textContent = d.orders.toLocaleString();
        document.getElementById('summaryBox').innerHTML = `
          <li><strong>View:</strong> ${mainView.charAt(0).toUpperCase()+mainView.slice(1)} — ${analyticsData[mainView].labels[selectedPeriod-1]}</li>
          <li><strong>Total Revenue:</strong> ₱${d.revenue.toLocaleString()}</li>
          <li><strong>Total Cups Sold:</strong> ${d.cups.toLocaleString()}</li>
          <li><strong>Total Orders:</strong> ${d.orders.toLocaleString()}</li>
          <li><strong>Average Order:</strong> ${d.avgOrder}</li>
          <li><strong>Peak:</strong> ${d.peak}</li>
          <li style="margin-top:0.5rem;"><strong>Conclusion:</strong> ${d.changeText} (${d.change}). Milk Tea = 68% of sales.</li>
        `;
        renderCharts(d);
      }
    }

    function renderCharts(d) {
      const revOpt = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '₱' + value.toLocaleString();
              }
            }
          }
        }
      };

      const numOpt = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      };

      [revenueChart, cupsChart, ordersChart].forEach(c => { if(c) c.destroy() });

      revenueChart = new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: { labels: d.dailyLabels, datasets: [{ data: d.revData, borderColor: '#ff0015', backgroundColor: 'rgba(255,0,21,0.1)', tension: 0.4, fill: true }] },
        options: revOpt
      });

      cupsChart = new Chart(document.getElementById('cupsChart'), {
        type: 'line',
        data: { labels: d.dailyLabels, datasets: [{ data: d.cupData, borderColor: '#fca1c9', backgroundColor: 'rgba(252,161,201,0.1)', tension: 0.4, fill: true }] },
        options: numOpt
      });

      ordersChart = new Chart(document.getElementById('ordersChart'), {
        type: 'line',
        data: { labels: d.dailyLabels, datasets: [{ data: d.orderData, borderColor: '#ffe600', backgroundColor: 'rgba(255,230,0,0.1)', tension: 0.4, fill: true }] },
        options: numOpt
      });
    }

    function renderOverallBar() {
      const data = analyticsData[mainView];

      // Destroy ANY existing charts first
      if(overallRevChart) overallRevChart.destroy();
      if(overallCupChart) overallCupChart.destroy();
      if(overallOrdChart) overallOrdChart.destroy();

      // 1. REVENUE CHART + INSIGHT
      overallRevChart = new Chart(document.getElementById('overallRevChart'), {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{ data: data.totals.rev, backgroundColor: 'rgba(255,0,21,0.7)' }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return '₱' + v.toLocaleString(); } } } }
        }
      });
      const maxRev = Math.max(...data.totals.rev);
      const idxRev = data.totals.rev.indexOf(maxRev);
      document.getElementById('overallRevInsight').textContent = `${data.labels[idxRev]} had the highest revenue: ₱${maxRev.toLocaleString()}.`;

      // 2. CUPS SOLD CHART + INSIGHT
      overallCupChart = new Chart(document.getElementById('overallCupChart'), {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{ data: data.totals.cup, backgroundColor: 'rgba(252,161,201,0.7)' }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
      });
      const maxCup = Math.max(...data.totals.cup);
      const idxCup = data.totals.cup.indexOf(maxCup);
      document.getElementById('overallCupInsight').textContent = `${data.labels[idxCup]} had the most cups sold: ${maxCup.toLocaleString()} cups.`;

      // 3. ORDERS COMPLETED CHART + INSIGHT
      overallOrdChart = new Chart(document.getElementById('overallOrdChart'), {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{ data: data.totals.order, backgroundColor: 'rgba(255,230,0,0.7)' }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
      });
      const maxOrd = Math.max(...data.totals.order);
      const idxOrd = data.totals.order.indexOf(maxOrd);
      document.getElementById('overallOrdInsight').textContent = `${data.labels[idxOrd]} had the most orders completed: ${maxOrd.toLocaleString()} orders.`;
    }

    function selectPeriod(num) {
      selectedPeriod = num; 
      showOverall = false; 
      isDrilldown = true;
      renderButtons(); 
      updateAll();
    }

    document.querySelectorAll('.view-btn').forEach(btn => {
      btn.onclick = () => {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        mainView = btn.dataset.main;
        selectedPeriod = 1;
        showOverall = false;
        isDrilldown = false;
        renderButtons();
        updateAll();
      };
    });

    renderButtons(); updateAll();
  </script>
</body>
</html>