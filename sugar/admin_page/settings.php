<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$in_iframe = isset($_GET['iframe']) && $_GET['iframe'] === '1';
$admin_name = $_SESSION['user_name'] ?? 'Store Manager';
$admin_email = $_SESSION['user_email'] ?? 'admin@sugarbaby.clsu.edu.ph';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - Sugar Baby Admin</title>
  
  <!-- Google Fonts & Font Awesome Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- ✅ FIXED: Added the global style.css path -->
  <link rel="stylesheet" href="../css/style.css">
  
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
      --border: #f0e6db;            /* Light Neutral Border */
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
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* --- SIDEBAR STYLES --- */
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

    /* LOGO & BRAND SECTION */
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

    .logo-holder img {
      width: 80%;
      height: 80%;
      object-fit: contain;
    }

    .logo-placeholder-icon {
      font-size: 2.2rem;
      color: #ff007f;
    }

    .brand-title-red {
      font-family: 'Fredoka', cursive, sans-serif;
      font-size: 2.2rem;
      font-weight: 800;
      color: #ff0015;
      text-transform: uppercase;
      line-height: 0.9;
      letter-spacing: 0.5px;
      text-shadow: 
        -2px -2px 0 #000,  
         2px -2px 0 #000,
        -2px  2px 0 #000,
         2px  2px 0 #000,
         0px  3px 0 #000;
    }

    .brand-title-yellow {
      font-family: 'Fredoka', cursive, sans-serif;
      font-size: 2.2rem;
      font-weight: 800;
      color: #ffe600;
      text-transform: uppercase;
      line-height: 0.95;
      margin-bottom: 0.35rem;
      text-shadow: 
        -2px -2px 0 #000,  
         2px -2px 0 #000,
        -2px  2px 0 #000,
         2px  2px 0 #000,
         0px  3px 0 #000;
    }

    .brand-subtitle-white {
      font-family: 'Fredoka', cursive, sans-serif;
      font-size: 0.85rem;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      text-shadow: 
        -1.5px -1.5px 0 #000,  
         1.5px -1.5px 0 #000,
        -1.5px  1.5px 0 #000,
         1.5px  1.5px 0 #000;
    }

    .nav-section {
      margin-bottom: 1.5rem;
    }

    .nav-section-title {
      font-size: 0.75rem;
      text-transform: uppercase;
      color: var(--text-muted);
      letter-spacing: 0.05em;
      margin-bottom: 0.75rem;
      font-weight: 700;
      padding-left: 0.5rem;
    }

    .nav-links {
      list-style: none;
    }

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
      margin-top: auto;
    }

    .logout-btn:hover {
      background-color: var(--pastel-pink-dark);
      color: #ffffff;
    }

    /* --- MAIN CONTENT LAYOUT --- */
    .main-wrapper {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

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

    .user-profile {
      display: flex;
      align-items: center;
      gap: 1rem;
      position: relative;
    }

    .notification-wrapper {
      position: relative;
    }

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
      transition: background 0.2s;
    }

    .user-trigger:hover {
      background-color: var(--bg-main);
    }

    .user-info {
      text-align: right;
    }

    .user-name {
      font-weight: 700;
      font-size: 0.875rem;
      color: var(--text-main);
    }

    .user-role {
      font-size: 0.75rem;
      color: var(--text-muted);
    }

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

    .popup-user-header h4 {
      font-size: 1rem;
      color: var(--text-main);
      font-weight: 700;
    }

    .popup-user-header p {
      font-size: 0.75rem;
      color: var(--text-muted);
    }

    .popup-menu {
      list-style: none;
    }

    .popup-menu-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.6rem 0;
      color: var(--text-main);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
    }

    .popup-menu-item:hover {
      color: var(--pastel-pink-dark);
    }

    .popup-logout {
      margin-top: 0.75rem;
      padding-top: 0.75rem;
      border-top: 2px solid var(--bg-main);
      color: #e53e3e;
      font-weight: 700;
    }

    .content-container {
      padding: 2rem;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    /* --- Settings Components --- */
    .settings-big-card {
        background: var(--bg-card);
        border-radius: 20px;
        border: 2px solid var(--pastel-pink-dark);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        max-width: 900px;
    }

    .avatar-profile-group {
        text-align: center;
        margin: 25px 0;
    }

    #settingsBigAvatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #99ccff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        font-weight: bold;
        color: white;
        margin: 0 auto 10px;
    }

    #settingsFullName {
        font-size: 28px;
        font-weight: bold;
        color: #222;
    }

    .settings-main-card {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: 16px;
        border: 2px solid var(--border);
        max-width: 900px;
        box-shadow: var(--card-shadow);
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--bg-main);
    }
    .setting-item:last-child { border-bottom: none; }

    .settings-action-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        font-size: 1rem;
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .settings-action-btn:hover {
        background-color: var(--pastel-pink);
        color: var(--sidebar-active-text);
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--pastel-pink-dark); }
    input:checked + .slider:before { transform: translateX(22px); }

    .settings-card-header {
        border-bottom: 2px solid var(--border);
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .hidden { display: none !important; }
  </style>
</head>
<body>

<?php if (!$in_iframe): ?>
  <!-- LEFT SIDEBAR (Itatago kung iframe) -->
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
        <ul class="nav-links">
          <li>
            <a class="nav-item active" data-tab="settings">
              <i class="fa-solid fa-gear"></i> Settings
            </a>
          </li>
        </ul>
      </div>
    </div>
    <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
  </aside>
<?php endif; ?>

  <!-- MAIN WRAPPER -->
  <div class="main-wrapper">
<?php if (!$in_iframe): ?>
    <header>
      <div class="search-bar">
        <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted)"></i>
        <input type="text" id="searchInput" placeholder="Search milk tea & coffee...">
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
            <h4 id="popupUserName"><?php echo htmlspecialchars($admin_name); ?></h4>
            <p><?php echo htmlspecialchars($admin_email); ?></p>
          </div>
          <ul class="popup-menu">
            <li>
              <a class="popup-menu-item" onclick="switchTab('settings')">
                <span><i class="fa-solid fa-gear"></i> Settings</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
              </a>
            </li>
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

      <!-- 5. SETTINGS VIEW -->
      <section id="settings" class="tab-content active">
        <h2 style="margin-bottom: 1.5rem; color: var(--text-main);">Settings & Account</h2>
        
        <!-- SEPARATE TOP CONTAINER: Account Switch & Management -->
        <div class="settings-big-card">

          <!-- Current Active Account -->
            <div class="avatar-profile-group">
              <div id="settingsBigAvatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
              <div id="settingsFullName" style="font-size: 28px; font-weight: bold; color: #222;"><?php echo htmlspecialchars($admin_name); ?></div>
              <div style="font-size: 20px; color: #555; margin-top: 5px;">Account</div>
              <div style="display:none;" id="currentActiveAcc"><?php echo htmlspecialchars($admin_name); ?></div>
            </div>

          <!-- Add Account Button -->
          <div class="setting-item">
            <div>
              <strong>Add New Account</strong>
              <p style="font-size: 0.8rem; color: var(--text-muted);">Add user accounts</p>
            </div>
            <button class="settings-action-btn" onclick="openModal('addAccount')"><i class="fa-solid fa-user-plus"></i></button>
          </div>

          <!-- Added Accounts List -->
          <div style="margin-top:1rem;">
            <h5 style="font-size:0.85rem; color:var(--text-muted); margin:0.5rem 0 1rem 0;">Saved Accounts</h5>
            <div id="addedAccountsList" style="display:flex; flex-direction:column; gap:0.6rem;">
              <!-- New accounts load here automatically via JS -->
            </div>
          </div>
        </div>

        <!-- SINGLE MAIN CONTAINER ONLY -->
        <div class="settings-main-card">
          <h3 class="settings-card-header">
            <i class="fa-solid fa-gear" style="color: var(--pastel-pink-dark);"></i> Account & System Settings
          </h3>

          <!-- 2-COLUMN LAYOUT INSIDE ONE CONTAINER -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            
            <!-- LEFT: ACCOUNT MANAGEMENT -->
            <div>
              <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-user-circle" style="color: var(--pastel-pink-dark);"></i> Account Management
              </h4>

              <div class="setting-item" style="margin-top:1.5rem;">
                <div>
                  <strong>Edit Profile</strong>
                  <p style="font-size: 0.8rem; color: var(--text-muted);">Name: <span id="exName"><?php echo htmlspecialchars($admin_name); ?></span> | Email: <span id="exEmail"><?php echo htmlspecialchars($admin_email); ?></span></p>
                </div>
                <button class="settings-action-btn" onclick="openModal('editProfile')"><i class="fa-solid fa-chevron-right"></i></button>
              </div>

              <div class="setting-item">
                <div>
                  <strong>Change Password</strong>
                  <p style="font-size: 0.8rem; color: var(--text-muted);">Update login password</p>
                </div>
                <button class="settings-action-btn" onclick="openModal('changePassword')"><i class="fa-solid fa-chevron-right"></i></button>
              </div>

              <div class="setting-item">
                <div>
                  <strong>Delete Account</strong>
                  <p style="font-size: 0.8rem; color: #e53e3e;">Permanently remove account</p>
                </div>
                <button class="settings-action-btn" style="color:#e53e3e;" onclick="confirmDelete()"><i class="fa-solid fa-trash"></i></button>
              </div>
            </div>

            <!-- RIGHT: APPEARANCE & SYSTEM -->
            <div>
              <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-palette" style="color: var(--pastel-yellow-dark);"></i> Appearance & System
              </h4>

              <div class="setting-item">
                <div>
                  <strong>Dark Mode</strong>
                  <p style="font-size: 0.8rem; color: var(--text-muted);">Switch light / dark theme</p>
                </div>
                <label class="switch">
                  <input type="checkbox" class="darkModeToggle">
                  <span class="slider"></span>
                </label>
              </div>

              <div class="setting-item">
                <div>
                  <strong>Email Notifications</strong>
                  <p style="font-size: 0.8rem; color: var(--text-muted);">Receive order alerts & updates</p>
                </div>
                <label class="switch">
                  <input type="checkbox" id="notifToggle" checked>
                  <span class="slider"></span>
                </label>
              </div>

              <div class="setting-item">
                <div>
                  <strong>Language</strong>
                  <p style="font-size: 0.8rem; color: var(--text-muted);">System language</p>
                </div>
                <span style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border); color: var(--text-main);">English</span>
              </div>
            </div>
          </div>
        </div>
      </section>

    </div>
  </div>

  <!-- ADD / EDIT MODAL OVERLAY -->
  <div id="editModalOverlay" class="hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: var(--bg-card); padding: 2rem; border-radius: 16px; width: 400px; border: 2px solid var(--border); box-shadow: var(--card-shadow);">
      <h3 style="margin-bottom: 1rem; color: var(--text-main);">Product Details</h3>
      <form id="editForm">
        <input type="hidden" id="editItemIndex">
        
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-main);">Item Name</label>
          <input type="text" id="editItemName" required style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
        </div>

        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-main);">Category</label>
          <input type="text" id="editItemCategory" required style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
        </div>

        <div style="margin-bottom: 1.5rem;">
          <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-main);">Availability</label>
          <select id="editItemAvailability" style="width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            <option value="true">Available</option>
            <option value="false">Unavailable</option>
          </select>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
          <button type="button" id="closeModalBtn" style="padding: 0.6rem 1.2rem; border-radius: 8px; border: 1px solid var(--border); background: transparent; color: var(--text-main); cursor: pointer;">Cancel</button>
          <button type="submit" style="padding: 0.6rem 1.2rem; border-radius: 8px; border: none; background: var(--pastel-pink-dark); color: white; font-weight: 700; cursor: pointer;">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- SETTINGS FORM MODAL -->
  <div id="settingsModal" class="hidden" style="position: fixed; inset:0; background: rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
    <div style="background:var(--bg-card); border-radius:16px; padding:2rem; width:420px; border:2px solid var(--border);">
      <h3 id="modalTitle" style="margin-bottom:1rem; color:var(--text-main);"></h3>
      <form id="settingsForm">
        <div id="modalFields"></div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
          <button type="button" onclick="closeSettingsModal()" style="padding:0.6rem 1.2rem; border-radius:8px; border:1px solid var(--border); background:transparent; color:var(--text-main);">Cancel</button>
          <button type="submit" style="padding:0.6rem 1.2rem; border-radius:8px; border:none; background:var(--pastel-pink-dark); color:white; font-weight:700;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- FULL WORKING SCRIPT: Tabs + Dark Mode + Popups -->
  <script>
    // Store all added user accounts
    let savedAccounts = [];

    function updateAvatar(fullName) {
        const firstLetter = fullName.trim().charAt(0).toUpperCase();
        const bigAvatar = document.getElementById('settingsBigAvatar');
        const nameDisplay = document.getElementById('settingsFullName');
        if (bigAvatar) bigAvatar.textContent = firstLetter;
        if (nameDisplay) nameDisplay.textContent = fullName;
    }

    // --------------------------
    // TAB SWITCHING SYSTEM
    // --------------------------
    const navItems = document.querySelectorAll('.nav-item');
    const tabContents = document.querySelectorAll('.tab-content');

    navItems.forEach(navLink => {
      navLink.addEventListener('click', function () {
        navItems.forEach(link => link.classList.remove('active'));
        tabContents.forEach(section => section.classList.remove('active'));
        this.classList.add('active');
        const tabId = this.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');
      });
    });

    function switchTab(tabName) {
      navItems.forEach(link => link.classList.remove('active'));
      tabContents.forEach(section => section.classList.remove('active'));
      document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
      document.getElementById(tabName).classList.add('active');
      hideUserPopup();
    }

    // --------------------------
    // DARK MODE TOGGLE (SYNC ALL SWITCHES)
    // --------------------------
    const darkToggles = document.querySelectorAll('.darkModeToggle');
    darkToggles.forEach(toggle => {
      toggle.addEventListener('change', function () {
        const isDark = this.checked;
        document.body.classList.toggle('dark-mode', isDark);
        darkToggles.forEach(t => t.checked = isDark);
      });
    });

    // --------------------------
    // USER PROFILE POPUP SHOW/HIDE
    // --------------------------
    const userTrigger = document.getElementById('userTrigger');
    const userPopup = document.getElementById('userPopup');

    function hideUserPopup() {
      userPopup.classList.add('hidden');
    }
    function toggleUserPopup() {
      userPopup.classList.toggle('hidden');
    }

    if(userTrigger && userPopup){
      userTrigger.addEventListener('click', toggleUserPopup);
      document.addEventListener('click', function(e){
        if(!userPopup.contains(e.target) && !userTrigger.contains(e.target)){
          hideUserPopup();
        }
      });
    }

    // --------------------------
    // NOTIFICATION POPUP TOGGLE
    // --------------------------
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    if(notifBtn && notifDropdown){
      notifBtn.addEventListener('click', () => notifDropdown.classList.toggle('hidden'));
    }

    let activeModalType = '';
    function openModal(type){
      activeModalType = type;
      const modal = document.getElementById('settingsModal');
      const title = document.getElementById('modalTitle');
      const fields = document.getElementById('modalFields');
      fields.innerHTML = '';

      if(type === 'addAccount'){
        title.textContent = 'Add New Account';
        fields.innerHTML = `
          <div style="margin-bottom:1rem;">
            <label>Full Name</label>
            <input type="text" id="newAccName" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
          </div>
          <div style="margin-bottom:1rem;">
            <label>Email Address</label>
            <input type="email" id="newAccEmail" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
          </div>
          <div style="margin-bottom:1rem;">
            <label>Set Password</label>
            <input type="password" id="newAccPass" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
          </div>`;
      }
      if(type === 'editProfile'){
        title.textContent = 'Edit Profile';
        fields.innerHTML = `
          <div style="margin-bottom:1rem;">
            <label>Full Name</label>
            <input type="text" id="inpName" value="${document.getElementById('exName').textContent}" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
          </div>
          <div style="margin-bottom:1rem;">
            <label>Email Address</label>
            <input type="email" id="inpEmail" value="${document.getElementById('exEmail').textContent}" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
          </div>`;
      }
      if(type === 'changePassword'){
        title.textContent = 'Change Password';
        fields.innerHTML = `
          <div style="margin-bottom:1rem;">
            <label>Current Password</label>
            <input type="password" id="oldPass" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
          </div>
          <div style="margin-bottom:1rem;">
            <label>New Password</label>
            <input type="password" id="newPass" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
          </div>`;
      }
      document.getElementById('settingsModal').classList.remove('hidden');
    }

    function closeSettingsModal(){
      document.getElementById('settingsModal').classList.add('hidden');
      activeModalType = '';
    }

    function confirmDelete(){
      if(confirm("⚠️ Are you sure? This will delete your account permanently!")){
        alert("Account deleted.");
      }
    }

    document.getElementById('settingsForm').addEventListener('submit', function(e){
      e.preventDefault();

      if(activeModalType === 'addAccount'){
        const newName = document.getElementById('newAccName').value;
        const newEmail = document.getElementById('newAccEmail').value;
        const newPass = document.getElementById('newAccPass').value;

        savedAccounts.push({ name: newName, email: newEmail, password: newPass });

        const listContainer = document.getElementById('addedAccountsList');
        const accItem = document.createElement('div');
        accItem.className = 'setting-item';
        accItem.style.padding = '0.6rem 0.8rem';
        accItem.innerHTML = `
          <div>
            <strong>${newName}</strong>
            <p style="font-size:0.75rem; color:var(--text-muted);">${newEmail}</p>
          </div>
          <button class="settings-action-btn" onclick="switchToThisAccount(this, '${newName}', '${newEmail}')">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Switch
          </button>
        `;
        listContainer.appendChild(accItem);

        alert(`New account added: ${newName}`);
        closeSettingsModal();
      }

      if(activeModalType === 'editProfile'){
        document.getElementById('exName').textContent = document.getElementById('inpName').value;
        document.getElementById('exEmail').textContent = document.getElementById('inpEmail').value;
        alert('Profile updated!');
      }
      if(activeModalType === 'changePassword') alert('Password changed!');
      closeSettingsModal();
    });

    function switchToThisAccount(buttonEl, accName, accEmail){
      const oldActiveName = document.getElementById('currentActiveAcc').textContent;

      if (oldActiveName.trim() !== accName.trim()) {
        const oldActiveEmail = document.getElementById('exEmail').textContent;
        savedAccounts.push({ name: oldActiveName, email: oldActiveEmail });

        const listContainer = document.getElementById('addedAccountsList');
        const accItem = document.createElement('div');
        accItem.className = 'setting-item';
        accItem.style.padding = '0.6rem 0.8rem';
        accItem.innerHTML = `
          <div>
            <strong>${oldActiveName}</strong>
            <p style="font-size:0.75rem; color:var(--text-muted);">${oldActiveEmail}</p>
          </div>
          <button class="settings-action-btn" onclick="switchToThisAccount(this, '${oldActiveName}', '${oldActiveEmail}')">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Switch
          </button>
        `;
        listContainer.appendChild(accItem);
      }

      document.getElementById('currentActiveAcc').textContent = accName;
      document.getElementById('exName').textContent = accName;
      document.getElementById('exEmail').textContent = accEmail;
      updateAvatar(accName);

      const accountRow = buttonEl.closest('.setting-item');
      if(accountRow) accountRow.remove();
      savedAccounts = savedAccounts.filter(acc => acc.name !== accName);

      alert(`Switched successfully to: ${accName}`);
    }
  </script>
</body>
</html>