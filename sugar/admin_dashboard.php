<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Store Manager';
$admin_email = $_SESSION['user_email'] ?? 'admin@sugarbaby.clsu.edu.ph';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugar Baby Admin Panel</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        :root {
            --bg-main: #fcfbfa; --bg-sidebar: #cbebff; --bg-card: #ffffff;
            --text-main: #2d3748; --text-muted: #62728d;
            --pastel-yellow: #fff2a8; --pastel-yellow-dark: #f0db6e;
            --pastel-pink: #ffd6e7; --pastel-pink-dark: #fca1c9;
            --border: #f0e6db;
            --sidebar-text: #2c3e50; --sidebar-active-bg: #fff2a8;
            --sidebar-active-text: #2c3e50; --card-shadow: 0 4px 15px rgba(203, 235, 255, 0.4);
        }

        /* Dark Mode Variables */
        body.dark-mode {
            --bg-main: #1a1e24; --bg-sidebar: #13171c; --bg-card: #22272e;
            --text-main: #f0f4f8; --text-muted: #9fb3c8; --border: #2d3748;
            --sidebar-text: #e2e8f0; --sidebar-active-bg: #3b4a6b;
            --sidebar-active-text: #fff2a8; --card-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { 
            background-color: var(--bg-main); 
            color: var(--text-main); 
            display: flex; 
            height: 100vh; 
            overflow: hidden; 
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
            height: 100vh; 
            overflow-y: auto;
        }
        .brand-container { text-align: center; margin-bottom: 2rem; }
        .logo-holder { width: 95px; height: 95px; background: #fff; border: 3px solid var(--pastel-pink-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.85rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .brand-title-red { font-family: 'Fredoka', cursive; font-size: 2.2rem; font-weight: 800; color: #ff0015; text-transform: uppercase; text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000; }
        .brand-title-yellow { font-family: 'Fredoka', cursive; font-size: 2.2rem; font-weight: 800; color: #ffe600; text-transform: uppercase; text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000; }
        .brand-subtitle-white { font-family: 'Fredoka', cursive; font-size: 0.85rem; font-weight: 700; color: #ffffff; text-transform: uppercase; text-shadow: -1.5px -1.5px 0 #000, 1.5px -1.5px 0 #000, -1.5px 1.5px 0 #000, 1.5px 1.5px 0 #000; }

        /* --- SIDEBAR NAVIGATION --- */
        .nav-links { list-style: none; }
        .nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--sidebar-text); text-decoration: none; border-radius: 12px; font-weight: 600; margin-bottom: 0.35rem; cursor: pointer; }
        .nav-item:hover, .nav-item.active { background-color: var(--sidebar-active-bg); color: var(--sidebar-active-text); font-weight: 700; }

        /* --- SYSTEM DIVIDER STYLES --- */
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

        .logout-btn { background-color: var(--pastel-pink); color: #2c3e50; border: none; padding: 0.75rem 1rem; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s ease; margin-top: auto; text-decoration: none; }
        .logout-btn:hover { background-color: var(--pastel-pink-dark); color: #ffffff; }

        .main-wrapper { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            height: 100vh; 
        }
        header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 0.75rem 2rem; 
            background-color: var(--bg-card); 
            border-bottom: 2px solid var(--border); 
            flex-shrink: 0;
            height: 70px;
        }
        .search-bar { display: flex; align-items: center; background: var(--bg-main); padding: 0.5rem 1.2rem; border-radius: 20px; width: 320px; border: 2px solid var(--pastel-yellow-dark); }
        .search-bar input { border: none; background: transparent; outline: none; margin-left: 0.5rem; color: var(--text-main); width: 100%; }
        .user-profile { display: flex; align-items: center; gap: 1rem; position: relative; }

        /* Notification Bell Icon */
        .notification-btn { background: var(--pastel-pink); border: none; color: var(--text-main); font-size: 1rem; cursor: pointer; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        .user-trigger { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.4rem 0.6rem; border-radius: 10px; }
        .user-info { text-align: right; }
        .user-name { font-weight: 700; font-size: 0.875rem; color: var(--text-main); }
        .user-role { font-size: 0.75rem; color: var(--text-muted); }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--pastel-yellow); color: #2c3e50; border: 2px solid var(--pastel-yellow-dark); display: flex; align-items: center; justify-content: center; font-weight: 800; }
        
        /* --- UPDATED USER POPUP BOX WITH DARK MODE --- */
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
        .popup-user-header h4 { font-size: 1rem; color: var(--text-main); font-weight: 700; }
        .popup-user-header p { font-size: 0.75rem; color: var(--text-muted); }
        
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
            cursor: pointer;
        }
        .popup-menu-item:hover { color: var(--pastel-pink-dark); }
        .popup-logout {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 2px solid var(--bg-main);
            color: #e53e3e;
            font-weight: 700;
        }

        /* --- DARK MODE SWITCH STYLES --- */
        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
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

        .hidden { display: none !important; }

        /* IFRAME Fix to fill the rest of the screen */
        .iframe-wrapper {
            flex: 1;
            display: flex;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        iframe[name="adminFrame"] {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>
<body>

    <aside>
        <div>
            <div class="brand-container">
                <div class="logo-holder"><i class="fa-solid fa-mug-hot" style="font-size:2.2rem; color:#ff007f;"></i></div>
                <div class="brand-title-red">SUGAR</div>
                <div class="brand-title-yellow">BABY</div>
                <div class="brand-subtitle-white">MILK TEA & COFFEE</div>
            </div>

            <!-- MAIN MENU -->
            <ul class="nav-links">
                <!-- ✅ UPDATED TO home.php -->
                <li><a href="admin_page/home.php?iframe=1" class="nav-item active" target="adminFrame"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="admin_page/menu.php?iframe=1" class="nav-item" target="adminFrame"><i class="fa-solid fa-glass-water"></i> Menu</a></li>
                <li><a href="admin_page/orders.php?iframe=1" class="nav-item" target="adminFrame"><i class="fa-solid fa-receipt"></i> Orders</a></li>
                <li><a href="admin_page/history.php?iframe=1" class="nav-item" target="adminFrame"><i class="fa-solid fa-clock-rotate-left"></i> History</a></li>
            </ul>

            <!-- SYSTEM DIVIDER -->
            <div class="nav-section">
                <div class="nav-section-title">SYSTEM</div>
                <ul class="nav-links">
                    <li><a href="admin_page/analytics.php?iframe=1" class="nav-item" target="adminFrame"><i class="fa-solid fa-chart-pie"></i> Analytics</a></li>
                    <li><a href="admin_page/settings.php?iframe=1" class="nav-item" target="adminFrame"><i class="fa-solid fa-gear"></i> Settings</a></li>
                    <li><a href="admin_page/about.php?iframe=1" class="nav-item" target="adminFrame"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
                </ul>
            </div>
        </div>

        <!-- LOGOUT BUTTON -->
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
    </aside>

    <div class="main-wrapper">
        <header>
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted);"></i>
                <input type="text" placeholder="Search menu or orders...">
            </div>
            <div class="user-profile">
                <!-- Notification Bell Icon -->
                <div class="notification-wrapper">
                    <button class="notification-btn"><i class="fa-regular fa-bell"></i></button>
                </div>
                
                <div class="user-trigger" id="userTrigger">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 2)); ?></div>
                </div>

                <!-- ✅ UPDATED USER POPUP: Added Dark Mode Switch & Settings -->
                <div class="user-popup-box hidden" id="userPopup">
                    <div class="popup-user-header">
                        <h4><?php echo htmlspecialchars($admin_name); ?></h4>
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
                        <li class="popup-menu-item popup-logout" onclick="window.location.href='logout.php'">
                            <span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="iframe-wrapper">
            <iframe name="adminFrame" src="admin_page/home.php?iframe=1"></iframe>
        </div>
    </div>

    <script>
        // User Popup Toggle
        document.getElementById('userTrigger').addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('userPopup').classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            const popup = document.getElementById('userPopup');
            if(!popup.contains(e.target) && !document.getElementById('userTrigger').contains(e.target)) popup.classList.add('hidden');
        });

        // Sidebar Link Click: Update iframe and active class
        document.querySelectorAll('.nav-item').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); 
                document.querySelectorAll('.nav-item').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const targetUrl = this.getAttribute('href');
                document.querySelector('iframe[name="adminFrame"]').src = targetUrl;
            });
        });

        // --- DARK MODE TOGGLE (SYNC ALL SWITCHES) ---
        const darkToggles = document.querySelectorAll('.darkModeToggle');
        darkToggles.forEach(toggle => {
            toggle.addEventListener('change', function () {
                const isDark = this.checked;
                document.body.classList.toggle('dark-mode', isDark);
                darkToggles.forEach(t => t.checked = isDark);
            });
        });
        
        // Helper function to switch tabs from within the popup
        function switchTab(tabName) {
            const navItem = document.querySelector(`.nav-item[href*="${tabName}.php"]`);
            if (navItem) {
                navItem.click();
            }
            document.getElementById('userPopup').classList.add('hidden');
        }
    </script>
</body>
</html>