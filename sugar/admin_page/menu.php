<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Store Manager';

// ✅ MAGIC FIX: Kung may ?iframe=1 sa URL, itago ang sidebar at header
$in_iframe = isset($_GET['iframe']) && $_GET['iframe'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sugar Baby Menu</title>
  <!-- Google Fonts & Font Awesome Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
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
    body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

    aside { width: 270px; background-color: var(--bg-sidebar); padding: 1.75rem 1.25rem; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; border-right: 2px solid var(--border); }
    .brand-container { display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 2rem; }
    .logo-holder { width: 95px; height: 95px; background: #fff; border: 3px solid var(--pastel-pink-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.85rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
    .brand-title-red { font-family: 'Fredoka', cursive; font-size: 2.2rem; font-weight: 800; color: #ff0015; text-transform: uppercase; text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000; }
    .brand-title-yellow { font-family: 'Fredoka', cursive; font-size: 2.2rem; font-weight: 800; color: #ffe600; text-transform: uppercase; text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000; }
    .brand-subtitle-white { font-family: 'Fredoka', cursive; font-size: 0.85rem; font-weight: 700; color: #ffffff; text-transform: uppercase; text-shadow: -1.5px -1.5px 0 #000, 1.5px -1.5px 0 #000, -1.5px 1.5px 0 #000, 1.5px 1.5px 0 #000; }

    .nav-links { list-style: none; }
    .nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--sidebar-text); text-decoration: none; border-radius: 12px; font-weight: 600; margin-bottom: 0.35rem; cursor: pointer; }
    .nav-item:hover, .nav-item.active { background-color: var(--sidebar-active-bg); color: var(--sidebar-active-text); font-weight: 700; }
    .logout-btn { background-color: var(--pastel-pink); color: #2c3e50; border: none; padding: 0.75rem 1rem; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s ease; margin-top: auto; }
    .logout-btn:hover { background-color: var(--pastel-pink-dark); color: #ffffff; }

    .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
    header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 2rem; background-color: var(--bg-card); border-bottom: 2px solid var(--border); }
    .search-bar { display: flex; align-items: center; background: var(--bg-main); padding: 0.6rem 1.2rem; border-radius: 20px; width: 320px; border: 2px solid var(--pastel-yellow-dark); }
    .search-bar input { border: none; background: transparent; outline: none; margin-left: 0.5rem; color: var(--text-main); width: 100%; }
    .user-profile { display: flex; align-items: center; gap: 1rem; position: relative; }
    .notification-btn { background: var(--pastel-pink); border: none; color: var(--text-main); font-size: 1rem; cursor: pointer; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .notification-dropdown { position: absolute; right: 0; top: 2.75rem; width: 250px; background: var(--bg-card); border: 2px solid var(--pastel-pink); border-radius: 12px; box-shadow: var(--card-shadow); padding: 1rem; font-size: 0.875rem; color: var(--text-muted); text-align: center; z-index: 100; }
    .user-trigger { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.4rem 0.6rem; border-radius: 10px; }
    .user-info { text-align: right; }
    .user-name { font-weight: 700; font-size: 0.875rem; color: var(--text-main); }
    .user-role { font-size: 0.75rem; color: var(--text-muted); }
    .avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--pastel-yellow); color: #2c3e50; border: 2px solid var(--pastel-yellow-dark); display: flex; align-items: center; justify-content: center; font-weight: 800; }
    .user-popup-box { position: absolute; top: 3.5rem; right: 0; width: 260px; background: var(--bg-card); border: 2px solid var(--pastel-yellow-dark); border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); padding: 1.25rem; z-index: 200; }
    .hidden { display: none !important; }

    .content-container { padding: 2rem; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* ✅ UPDATED: Inventory header with Add button */
    .inventory-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .action-controls { display: flex; align-items: center; gap: 1rem; }
    .sort-select { background-color: var(--bg-card); color: var(--text-main); border: 2px solid var(--pastel-yellow-dark); padding: 0.6rem 1rem; font-size: 0.875rem; font-weight: 600; border-radius: 10px; cursor: pointer; outline: none; }
    .btn-toggle { background-color: var(--pastel-pink); color: #2c3e50; border: none; padding: 0.6rem 1.2rem; font-size: 0.875rem; font-weight: 700; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    .btn-toggle:hover { background-color: var(--pastel-pink-dark); color: #ffffff; }

    /* ✅ UPDATED: Product Grid */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem; }
    .product-card { background: var(--bg-card); border: 2px solid var(--border); border-radius: 16px; padding: 1.25rem; box-shadow: var(--card-shadow); display: flex; flex-direction: column; position: relative; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s; }
    .product-card:hover { transform: translateY(-6px); border-color: var(--pastel-yellow-dark); box-shadow: 0 12px 20px rgba(255, 242, 168, 0.5); }
    .product-image-container { width: 100%; height: 100px; background-color: var(--pastel-blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; overflow: hidden; }
    .product-image-container i { font-size: 2.5rem; color: var(--text-muted); opacity: 0.5; transition: transform 0.4s ease; }
    .product-card:hover .product-image-container i { transform: scale(1.1) rotate(-5deg); }
    .badge { align-self: flex-start; background-color: var(--pastel-yellow); color: #2c3e50; font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px; margin-bottom: 0.5rem; text-transform: uppercase; }
    .product-title { font-size: 1rem; font-weight: 700; margin-bottom: 0; }

    /* ✅ UPDATED: Category Tabs */
    #categoryTabs button {
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        border: 2px solid var(--border);
        font-weight: 600;
        cursor: pointer;
        background: var(--bg-card);
        color: var(--text-main);
        transition: all 0.2s;
        white-space: nowrap;
    }
    #categoryTabs button:hover {
        background: var(--pastel-yellow);
        border-color: var(--pastel-yellow-dark);
    }

    .hidden { display: none !important; }
  </style>
</head>
<body>

<?php if (!$in_iframe): ?>
  <!-- SIDEBAR (ITATAGO KUNG IFRAME) -->
  <aside>
    <div>
      <div class="brand-container">
        <div class="logo-holder"><img src="../logo.png" alt="Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"><i class="fa-solid fa-mug-hot logo-placeholder-icon" style="display: none;"></i></div>
        <div class="brand-title-red">SUGAR</div>
        <div class="brand-title-yellow">BABY</div>
        <div class="brand-subtitle-white">MILK TEA & COFFEE</div>
      </div>
      <ul class="nav-links">
        <li><a class="nav-item active" data-tab="menu"><i class="fa-solid fa-glass-water"></i> Menu</a></li>
      </ul>
    </div>
    <button class="logout-btn" onclick="alert('Logging out...')"><i class="fa-solid fa-right-from-bracket"></i> <span>Log Out</span></button>
  </aside>
<?php endif; ?>

  <div class="main-wrapper">
<?php if (!$in_iframe): ?>
    <header>
      <div class="search-bar"><i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted)"></i><input type="text" id="searchInput" placeholder="Search milk tea & coffee..."></div>
      <div class="user-profile">
        <div class="notification-wrapper"><button class="notification-btn" id="notifBtn"><i class="fa-regular fa-bell"></i></button><div class="notification-dropdown hidden" id="notifDropdown">No notifications to show</div></div>
        <div class="user-trigger" id="userTrigger"><div class="user-info"><div class="user-name">John Laurenz Sunga</div><div class="user-role">Store Manager</div></div><div class="avatar">SB</div></div>
        <div class="user-popup-box hidden" id="userPopup"><div class="popup-user-header"><h4 id="popupUserName">John Laurenz Sunga</h4><p>sungajohnlaurenzso@gmail.com</p></div><ul class="popup-menu"><li><a class="popup-menu-item" onclick="switchTab('settings')"><span><i class="fa-solid fa-gear"></i> Settings</span><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i></a></li><li class="popup-menu-item"><span><i class="fa-solid fa-moon"></i> Dark Mode</span><label class="switch"><input type="checkbox" class="darkModeToggle"><span class="slider"></span></label></li><li class="popup-menu-item popup-logout" onclick="alert('Logging out...')"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></li></ul></div>
      </div>
    </header>
<?php endif; ?>

    <!-- ✅ MENU CONTENT -->
    <div class="content-container">
      <section id="menu" class="tab-content active">
        <div class="inventory-header">
          <div><h2 style="color: var(--text-main);">Sugar Baby Menu</h2><p style="color: var(--text-muted); font-size: 0.875rem;">Explore our Milk Tea & Coffee offerings</p></div>
          <div class="action-controls">
            <select id="sortSelect" class="sort-select">
              <option value="" disabled selected>Sort By</option>
              <option value="price-low">▼ Price Low to High</option>
              <option value="price-high">▼ Price High to Low</option>
              <option value="name-az">▼ Name A-Z</option>
              <option value="name-za">▼ Name Z-A</option>
            </select>
            <!-- ✅ ADD NEW ITEM BUTTON -->
            <button id="addNewItemBtn" class="btn-toggle" style="background-color: var(--pastel-yellow); border: 2px solid var(--pastel-yellow-dark);"><i class="fa-solid fa-plus"></i> <span>Add New Item</span></button>
          </div>
        </div>
        <!-- ✅ CATEGORY TABS -->
        <div id="categoryTabs" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 0.5rem;"></div>
        <!-- ✅ PRODUCTS GRID -->
        <div id="productsGrid" class="products-grid"></div>
      </section>
    </div>
  </div>

  <!-- ✅ ADD/EDIT MODAL OVERLAY -->
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

  <!-- ✅ UPDATED SCRIPT: ../js/menu.js -->
  <script src="../js/menu.js"></script>
</body>
</html>