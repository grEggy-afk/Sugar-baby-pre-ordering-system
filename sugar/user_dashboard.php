<?php
session_start();
require_once 'db.php';

// Guard clause: Siguraduhing naka-login at Customer ang user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_email = $_SESSION['user_email'] ?? 'customer@sugarbaby.ph';
$user_id = $_SESSION['user_id'];

// --- HANDLE CHECKOUT / PLACE ORDER (Processes the form from the Cart tab) ---
if (isset($_POST['place_order'])) {
    $product_name = $_POST['product_name'] ?? 'Classic Milk Tea';
    $price = floatval($_POST['price'] ?? 60);
    $qty = intval($_POST['quantity'] ?? 1);
    $total = $price * $qty;
    $payment = $_POST['payment_method'] ?? 'GCash';

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, total_price, item_count, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, 'incoming', NOW())");
    $stmt->bind_param("isdss", $user_id, $user_name, $total, $qty, $payment);
    
    if ($stmt->execute()) {
        $order_success = "Order placed successfully! (Order #" . $conn->insert_id . ")";
    } else {
        $order_error = "Failed to place order.";
    }
    $stmt->close();
}

// --- KUNIN ANG MGA ORDERS NG USER NA ITO ---
$my_orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");

// --- DYNAMIC PAGE LOADING LOGIC ---
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$allowed_pages = ['home', 'menu', 'cart', 'orders', 'history', 'settings', 'about'];
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sugar Baby Shop | Customer Portal</title>
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

    /* --- CART BUTTON & BADGE (UPDATED FOR JS) --- */
    .cart-btn-wrapper {
      position: relative;
    }

    .header-icon-btn {
      background: var(--pastel-pink);
      border: none;
      color: var(--text-main);
      font-size: 1rem;
      cursor: pointer;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    
    .header-icon-btn:hover {
      background: var(--pastel-pink-dark);
      color: white;
    }

    .cart-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background-color: #ff0015;
      color: white;
      font-size: 0.7rem;
      font-weight: 800;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* --- USER POPUP --- */
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

    /* --- CONTENT AREA --- */
    .content-container {
      padding: 2rem;
      width: 100%;
      box-sizing: border-box;
    }

    /* --- DYNAMIC PAGE LOADING STYLES --- */
    .dynamic-page {
        width: 100%;
    }

    /* --- SWITCH COMPONENT --- */
    .switch {
      position: relative;
      display: inline-block;
      width: 46px;
      height: 24px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

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

    input:checked + .slider {
      background-color: var(--pastel-pink-dark);
    }

    input:checked + .slider:before {
      transform: translateX(22px);
    }

    .hidden {
      display: none !important;
    }

    /* =========================================
       CART PAGE STYLES (from cartUser.css) 
       ========================================= */
    .cart-page {
        width: 100%;
        padding: 0;
    }
    .cart-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .eyebrow {
        color: #1877F2;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1px;
    }
    .cart-heading h1 {
        font-size: 34px;
        font-weight: 800;
        margin-top: 4px;
        color: var(--text-main);
    }
    .cart-heading p {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 4px;
    }
    .clear-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        border: none;
        background: var(--pastel-yellow);
        color: var(--text-main);
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.25s;
    }
    .clear-btn:hover { background: var(--pastel-yellow-dark); }

    .cart-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 395px;
        gap: 28px;
        align-items: start;
    }
    .cart-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
    }

    /* CART ITEMS */
    .cart-items-card {
        padding: 24px;
        min-height: 350px;
    }
    .cart-item {
        display: grid;
        grid-template-columns: 82px minmax(0, 1fr) auto;
        gap: 18px;
        padding: 20px 0;
        border-bottom: 1px solid var(--border);
    }
    .cart-item:first-child { padding-top: 0; }
    .cart-item:last-child { border-bottom: none; padding-bottom: 0; }

    .item-image {
        width: 82px; height: 82px;
        background: var(--pastel-blue);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1877F2;
        font-size: 28px;
    }
    .item-category {
        display: inline-block;
        background: var(--pastel-pink);
        color: #d92d78;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .item-name {
        font-size: 18px;
        margin-top: 6px;
        color: var(--text-main);
    }
    .item-details {
        margin-top: 7px;
        color: var(--text-muted);
        font-size: 12px;
        line-height: 1.7;
    }
    .item-details strong { color: var(--text-main); }

    .addons {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 4px;
    }
    .cart-addon {
        background: #f5f8fa;
        border: 1px solid var(--border);
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 10px;
    }

    .item-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
    }
    .item-price {
        font-size: 18px;
        color: #1877F2;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f5f8fa;
        border: 1px solid var(--border);
        padding: 4px;
        border-radius: 12px;
    }
    .quantity-control span {
        min-width: 22px;
        text-align: center;
        font-weight: 700;
        font-size: 13px;
    }
    .cart-quantity-btn {
        width: 28px; height: 28px;
        border: none; background: var(--bg-card);
        border-radius: 8px;
        color: var(--text-main);
        cursor: pointer;
        font-size: 16px; font-weight: 700;
    }
    .cart-quantity-btn:hover { background: var(--pastel-blue); }

    .item-buttons {
        display: flex;
        gap: 7px;
    }
    .edit-btn, .remove-btn {
        border: none; border-radius: 10px;
        padding: 7px 10px;
        font-size: 11px; font-weight: 700;
        cursor: pointer;
    }
    .edit-btn { background: var(--pastel-blue); color: var(--text-main); }
    .edit-btn:hover { background: #b0d8eb; }
    .remove-btn { background: #ffe1ea; color: #d92862; }
    .remove-btn:hover { background: #ffcbdc; }

    /* SUMMARY CARD */
    .summary-card {
        padding: 30px;
        position: sticky;
        top: 20px;
    }
    .summary-card h2 { font-size: 23px; margin-bottom: 28px; color: var(--text-main); }
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        color: var(--text-muted);
        font-size: 14px;
    }
    .summary-row strong { color: var(--text-main); }
    .summary-divider { height: 1px; background: var(--border); margin: 24px 0; }
    .summary-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
    }
    .summary-total span { font-weight: 700; font-size: 16px; }
    .summary-total strong {
        color: #2388ed;
        font-size: 25px;
    }
    .checkout-btn {
        width: 100%;
        border: none;
        background: #17243a;
        color: white;
        padding: 15px;
        border-radius: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.25s;
    }
    .checkout-btn:hover:not(:disabled) {
        background: #263955;
        transform: translateY(-1px);
    }
    .checkout-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .continue-btn {
        width: 100%;
        margin-top: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        background: var(--pastel-blue);
        color: var(--text-main);
        text-decoration: none;
        padding: 14px;
        border-radius: 11px;
        font-size: 13px; font-weight: 700;
    }
    .continue-btn:hover { background: #b0d8eb; }

    /* EMPTY CART STATE */
    .empty-cart {
        text-align: center;
        padding: 70px 20px;
    }
    .empty-cart-icon {
        width: 70px; height: 70px;
        border-radius: 50%;
        background: var(--pastel-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: auto;
        color: #1877F2;
        font-size: 27px;
    }
    .empty-cart h2 { margin-top: 15px; font-size: 20px; color: var(--text-main); }
    .empty-cart p { color: var(--text-muted); font-size: 13px; margin: 7px 0 18px; }
    .empty-cart button {
        border: none;
        background: var(--pastel-yellow);
        padding: 11px 20px;
        border-radius: 20px;
        font-weight: 700;
        cursor: pointer;
    }

    /* =========================================
       NEW DESIGN CSS (FROM YOUR SCREENSHOTS)
       ========================================= */

    /* --- PRODUCT CARDS --- */
    .product-card {
        background: var(--bg-card);
        border: 2px solid var(--border);
        border-radius: 20px;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-6px);
        border-color: var(--pastel-yellow-dark);
        box-shadow: 0 12px 24px rgba(255, 242, 168, 0.3);
    }
    .product-image-container {
        width: 100%;
        height: 110px;
        background-color: var(--pastel-blue);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    .product-image-container i {
        font-size: 2.8rem;
        color: #2d3748;
        transition: transform 0.4s ease;
    }
    .product-card:hover .product-image-container i {
        transform: scale(1.1) rotate(-5deg);
    }
    .badge {
        align-self: flex-start;
        background-color: var(--pastel-yellow);
        color: #2c3e50;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .product-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.2rem;
    }
    .product-price {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
        margin-top: 4px;
    }

    /* --- CART & CHECKOUT BUTTONS --- */
    .btn-toggle {
        background-color: var(--pastel-pink);
        color: #2c3e50;
        border: none;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }
    .btn-toggle:hover {
        background-color: var(--pastel-pink-dark);
        color: #ffffff;
        transform: translateY(-2px);
    }
    .order-btn {
        background-color: var(--pastel-pink);
        color: #2c3e50;
    }
    .order-btn:hover {
        background-color: var(--pastel-pink-dark);
        color: white;
    }

    /* --- ORDER MODAL --- */
    #editModalOverlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    #editModalOverlay .modal-card {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 24px;
        width: 90%;
        max-width: 440px;
        border: 1px solid var(--border);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        max-height: 90vh;
        overflow-y: auto;
        text-align: center;
    }
    #editModalOverlay .modal-card h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1.25rem;
    }
    #editModalOverlay .modal-card label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        text-align: left;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }
    #editModalOverlay .modal-card select,
    #editModalOverlay .modal-card input[type="number"] {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--bg-main);
        color: var(--text-main);
        font-weight: 600;
        outline: none;
        margin-bottom: 1rem;
    }
    #editModalOverlay .modal-card .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    #editModalOverlay .modal-card .cancel-btn {
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: transparent;
        color: var(--text-main);
        font-weight: 600;
        cursor: pointer;
    }
    #editModalOverlay .modal-card .submit-btn {
        padding: 0.6rem 1.4rem;
        border-radius: 10px;
        border: none;
        background: var(--pastel-pink-dark);
        color: white;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    #editModalOverlay .modal-card .total-price {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--pastel-pink-dark);
        margin: 1rem 0;
        text-align: right;
    }

    /* --- QR PAYMENT MODAL --- */
    #qrPaymentModalOverlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    #qrPaymentModalOverlay .modal-card {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 24px;
        width: 90%;
        max-width: 440px;
        border: 1px solid var(--border);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        text-align: center;
    }
    #qrPaymentModalOverlay .modal-card h3 {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.2rem;
    }
    #qrPaymentModalOverlay .modal-card .ref-code {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 1.25rem;
    }
    #qrPaymentModalOverlay .modal-card .qr-box {
        background: var(--bg-main);
        padding: 1.25rem;
        display: inline-block;
        border-radius: 16px;
        border: 1px solid var(--border);
        margin-bottom: 1rem;
    }
    #qrPaymentModalOverlay .modal-card .qr-box div {
        width: 130px; height: 130px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: white; color: #2d3748; font-weight: bold; font-size: 0.8rem; border-radius: 12px;
    }
    #qrPaymentModalOverlay .modal-card .qr-box i {
        font-size: 3rem; margin-bottom: 6px; color: var(--pastel-pink-dark);
    }
    #qrPaymentModalOverlay .modal-card .account-name {
        font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;
    }
    #qrPaymentModalOverlay .modal-card .upload-label {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 16px; border: 2px dashed var(--border); border-radius: 12px;
        background: var(--bg-main); cursor: pointer; margin-bottom: 1rem;
    }
    #qrPaymentModalOverlay .modal-card .upload-label i {
        font-size: 1.5rem; color: var(--pastel-pink-dark); margin-bottom: 6px;
    }
    #qrPaymentModalOverlay .modal-card .upload-label span {
        font-size: 0.85rem; color: var(--text-main); font-weight: 600;
    }
    #qrPaymentModalOverlay .modal-card .order-summary-box {
        background: var(--bg-main);
        padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border);
        margin-bottom: 1.25rem; text-align: left; font-size: 0.85rem;
    }
    #qrPaymentModalOverlay .modal-card .order-summary-box .row {
        display: flex; justify-content: space-between; margin-bottom: 4px; color: var(--text-muted);
    }
    #qrPaymentModalOverlay .modal-card .order-summary-box .total-row {
        display: flex; justify-content: space-between;
        border-top: 1px solid var(--border); padding-top: 6px; margin-top: 6px;
    }
    #qrPaymentModalOverlay .modal-card .order-summary-box .total-row span {
        font-weight: 700; color: var(--text-main);
    }
    #qrPaymentModalOverlay .modal-card .order-summary-box .total-row strong {
        font-weight: 700; color: var(--pastel-pink-dark); font-size: 1.05rem;
    }

    /* --- SUCCESS MODALS --- */
    .success-modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1100;
    }
    .success-modal-overlay .success-card {
        background: var(--bg-card);
        padding: 2.5rem 2rem;
        border-radius: 24px;
        width: 90%; max-width: 340px;
        text-align: center;
        border: 1px solid var(--border);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    .success-modal-overlay .success-card h3 {
        font-size: 1.4rem; font-weight: 800; margin-bottom: 1.5rem; letter-spacing: 0.5px; color: var(--text-main);
    }
    .success-modal-overlay .success-card button {
        width: 100%; padding: 12px; justify-content: center;
        background: var(--pastel-pink-dark); color: white;
        border-radius: 10px; font-weight: 700; border: none; cursor: pointer; font-size: 1rem;
    }

    /* =========================================
       ORDERS & HISTORY PAGE UI EXACTLY LIKE PICTURE
       ========================================= */
    .order-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        transition: transform 0.2s;
    }
    .order-card:hover {
        transform: translateY(-2px);
    }

    .order-status {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    /* Active Order Statuses */
    .status-pending     { background-color: #e9ecef; color: #495057; }
    .status-preparing    { background-color: #fff3cd; color: #856404; }
    .status-ready-pickup { background-color: #cce5ff; color: #004085; }
    
    /* History Order Statuses */
    .status-completed  { background-color: #d4edda; color: #155724; }
    .status-cancelled  { background-color: #f8d7da; color: #721c24; }
    .status-refunded   { background-color: #fff3cd; color: #856404; }

    .btn-view-order {
        background: var(--pastel-blue);
        color: #2c3e50;
        border: none;
        padding: 0.35rem 0.75rem;
        font-weight: 700;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.75rem;
    }
    .btn-view-order:hover { background: #a8dfff; }

    .btn-cancel-order {
        background: #f8d7da;
        color: #721c24;
        border: none;
        padding: 0.35rem 0.75rem;
        font-weight: 700;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.75rem;
    }
    .btn-cancel-order:hover { background: #f5c6cb; }
    .btn-cancel-order.hidden { display: none !important; }

    @media (max-width: 900px) {
        .cart-layout { grid-template-columns: 1fr; }
        .summary-card { position: static; }
    }
  </style>
</head>
<body>

  <!-- LEFT SIDEBAR -->
  <aside>
    <div>
      <!-- LOGO HOLDER & BRANDING -->
      <div class="brand-container">
        <div class="logo-holder">
          <img src="images/SUGAR BABY 2.png" alt="Sugar Baby Logo" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
        </div>

        <div class="brand-title-red">SUGAR</div>
        <div class="brand-title-yellow">BABY</div>
        <div class="brand-subtitle-white">MILK TEA & COFFEE</div>
      </div>

      <div class="nav-section">
        <div class="nav-section-title">Menu & Ordering</div>
        <ul class="nav-links">
          <li>
            <a class="nav-item <?php echo $page == 'home' ? 'active' : ''; ?>" href="?page=home">
              <i class="fa-solid fa-house"></i> Home
            </a>
          </li>
          <li>
            <a class="nav-item <?php echo $page == 'menu' ? 'active' : ''; ?>" href="?page=menu">
              <i class="fa-solid fa-glass-water"></i> Menu
            </a>
          </li>
          <li>
            <a class="nav-item <?php echo $page == 'cart' ? 'active' : ''; ?>" href="?page=cart">
              <i class="fa-solid fa-cart-shopping"></i> Cart
            </a>
          </li>
          <li>
            <a class="nav-item <?php echo $page == 'orders' ? 'active' : ''; ?>" href="?page=orders">
              <i class="fa-solid fa-receipt"></i> Orders
            </a>
          </li>
          <li>
            <a class="nav-item <?php echo $page == 'history' ? 'active' : ''; ?>" href="?page=history">
              <i class="fa-solid fa-clock-rotate-left"></i> History
            </a>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <div class="nav-section-title">Support</div>
        <ul class="nav-links">
          <li>
            <a class="nav-item <?php echo $page == 'settings' ? 'active' : ''; ?>" href="?page=settings">
               <i class="fa-solid fa-gear"></i> Settings
            </a>
          </li>
          <li>
            <a class="nav-item <?php echo $page == 'about' ? 'active' : ''; ?>" href="?page=about">
              <i class="fa-solid fa-circle-info"></i> About Us
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- LOGOUT BUTTON -->
    <a href="logout.php" class="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Log Out</span>
    </a>
  </aside>

  <!-- MAIN WRAPPER -->
  <div class="main-wrapper">
    <!-- HEADER BAR -->
    <header>
      <div class="search-bar">
        <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted)"></i>
        <input type="text" id="searchInput" placeholder="Search milk tea & coffee...">
      </div>

      <div class="user-profile">
        <!-- CART BUTTON -->
        <div class="cart-btn-wrapper">
            <button class="header-icon-btn" id="cartSummaryBtn">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-badge hidden" id="cartBadge">0</span>
            </button>
        </div>

        <!-- CLICKABLE USER TRIGGER -->
        <div class="user-trigger" id="userTrigger">
          <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="user-role">Valued Customer</div>
          </div>
          <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
        </div>

        <!-- USER POPUP BOX -->
        <div class="user-popup-box hidden" id="userPopup">
          <div class="popup-user-header">
            <h4><?php echo htmlspecialchars($user_name); ?></h4>
            <p><?php echo htmlspecialchars($user_email); ?></p>
          </div>
          <ul class="popup-menu">
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

    <!-- CONTENT AREA -->
    <div class="content-container">
      <!-- DITO MAG-LO-LOAD ANG MGA PAGES Galing sa customer_page/ -->
      <div class="dynamic-page">
        <?php include "customer_page/$page.php"; ?>
      </div>
    </div>
  </div>

  <!-- IMPORT NG JAVASCRIPT -->
  <script src="js/user_menu.js"></script>
  
  <script>
    // Dark Mode Toggle (Sync)
    const darkModeToggles = document.querySelectorAll('.darkModeToggle');
    darkModeToggles.forEach(toggle => {
      toggle.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        document.body.classList.toggle('dark-mode', isChecked);
        darkModeToggles.forEach(t => t.checked = isChecked);
      });
    });

    // User Profile Popup Box Toggle
    const userTrigger = document.getElementById('userTrigger');
    const userPopup = document.getElementById('userPopup');

    userTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      userPopup.classList.toggle('hidden');
    });

    // Close Dropdown on Click Outside
    document.addEventListener('click', (e) => {
      if (!userPopup.contains(e.target) && !userTrigger.contains(e.target)) {
        userPopup.classList.add('hidden');
      }
    });
  </script>
</body>
</html>