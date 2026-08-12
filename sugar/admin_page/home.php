<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$in_iframe = isset($_GET['iframe']) && $_GET['iframe'] === '1';

// --- DASHBOARD METRICS QUERIES ---
$rev_query = $conn->query("SELECT SUM(total_price) AS revenue FROM orders WHERE status = 'completed'");
$total_revenue = $rev_query ? ($rev_query->fetch_assoc()['revenue'] ?? 0) : 0;

$pending_query = $conn->query("SELECT COUNT(*) AS pending FROM orders WHERE status = 'incoming'");
$pending_orders = $pending_query ? ($pending_query->fetch_assoc()['pending'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Sugar Baby Admin</title>
    
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
            --pastel-pink: #ffd6e7; --pastel-pink-dark: #fca1c9;
            --pastel-blue: #cbebff; --border: #f0e6db;
            --card-shadow: 0 4px 15px rgba(203, 235, 255, 0.4);
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); padding: 0; margin: 0; display: flex; flex-direction: column; }
        
        .dashboard-wrap { padding: 1.5rem 2rem; width: 100%; max-width: 100%; }

        /* ===== HOME FEATURES ===== */
        .feature-banner {
            background: linear-gradient(135deg, #fff2a8, #ffd6e7);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem 1.6rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .feature-banner h2 { color: var(--text-main); margin: 0 0 0.3rem 0; }
        
        .feature-container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .feature-card {
            flex: 1 1 48%;
            min-width: 280px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.3rem;
            box-shadow: 0 8px 24px rgba(255, 171, 193, 0.14);
        }
        .feature-card-purple {
            background: #f8f7ff;
            border-color: #e5e3ff;
        }
        
        .hot-products-scroll {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding: 0.3rem 0.2rem 1rem;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }
        .hot-product-item {
            min-width: 200px;
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            flex-shrink: 0;
        }
        .hot-product-item .badge {
            display: inline-block;
            background-color: var(--pastel-yellow);
            color: #2c3e50;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
        }
        
        .hidden { display: none !important; }
    </style>
</head>
<body>

    <div class="dashboard-wrap">

        <!-- ✅ HOME FEATURES (Stats Cards and Title Removed) -->

        <div class="feature-banner">
            <p style="font-size:0.75rem;font-weight:700;color:#ff4d7a;margin-bottom:0.45rem;">GREAT DEALS</p>
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h2>Hot now & new</h2>
                    <p style="color:var(--text-muted);max-width:400px;">Discover fresh favorites, limited offers, and the easiest way to order your next drink.</p>
                </div>
                <div style="display:flex;gap:0.75rem;align-items:center;">
                    <button onclick="window.parent.location.href='admin_page/menu.php?iframe=1'" style="background:transparent;border:none;font-weight:700;cursor:pointer;color:var(--text-main);">Browse menu</button>
                    <button onclick="window.parent.location.href='admin_page/orders.php?iframe=1'" style="background:#ff5a8f;color:#fff;border:none;padding:0.9rem 1.3rem;border-radius:999px;font-weight:700;cursor:pointer;">Order now</button>
                </div>
            </div>
        </div>

        <div class="feature-container">
            <!-- Loyalty Card -->
            <div class="feature-card">
                <p style="font-size:0.75rem;font-weight:700;color:#ff4d7a;margin-bottom:0.4rem;">LOYALTY REWARDS</p>
                <h3 style="margin:0 0 1rem 0;">Loyalty Card</h3>
                <p style="color:var(--text-muted);margin-bottom:1.2rem;">Show the physical loyalty card in-store to claim free toppings, upsizes, and XL upgrades.</p>
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="display:flex;gap:0.4rem;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#ffd6e7;"></div><div style="width:40px;height:40px;border-radius:50%;background:#ffd6e7;"></div><div style="width:40px;height:40px;border-radius:50%;background:#ffd6e7;"></div><div style="width:40px;height:40px;border-radius:50%;background:#ffd6e7;"></div><div style="width:40px;height:40px;border-radius:50%;background:#ffd6e7;display:flex;align-items:center;justify-content:center;font-weight:700;">5</div>
                        </div>
                        <span style="font-weight:700;color:#a33c5e;">Extra toppings</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="display:flex;gap:0.4rem;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#cbebff;"></div><div style="width:40px;height:40px;border-radius:50%;background:#cbebff;"></div><div style="width:40px;height:40px;border-radius:50%;background:#cbebff;"></div><div style="width:40px;height:40px;border-radius:50%;background:#cbebff;"></div><div style="width:40px;height:40px;border-radius:50%;background:#cbebff;display:flex;align-items:center;justify-content:center;font-weight:700;">10</div>
                        </div>
                        <span style="font-weight:700;color:#3f6f91;">Free upsize</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="display:flex;gap:0.4rem;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#fff2a8;"></div><div style="width:40px;height:40px;border-radius:50%;background:#fff2a8;"></div><div style="width:40px;height:40px;border-radius:50%;background:#fff2a8;"></div><div style="width:40px;height:40px;border-radius:50%;background:#fff2a8;"></div><div style="width:40px;height:40px;border-radius:50%;background:#fff2a8;display:flex;align-items:center;justify-content:center;font-weight:700;">15</div>
                        </div>
                        <span style="font-weight:700;color:#8d6b00;">Free XL</span>
                    </div>
                </div>
            </div>

            <!-- New Arrival -->
            <div class="feature-card feature-card-purple">
                <p style="font-size:0.75rem;font-weight:700;color:#5c6ac4;margin-bottom:0.35rem;">NEW ARRIVAL</p>
                <h3 style="margin:0 0 1rem 0;">Peach & Honey Breeze</h3>
                <div style="height:150px;border-radius:20px;background:#dde7ff;display:flex;align-items:center;justify-content:center;text-align:center;padding:1rem;margin-bottom:1rem;font-weight:700;color:#3f587e;">A creamy seasonal pick with peach, honey, and chill vibes.</div>
                <p style="color:var(--text-muted);margin-bottom:1.25rem;">Try the new flavor and collect extra loyalty stamps when you order today.</p>
                <button onclick="window.parent.location.href='admin_page/menu.php?iframe=1'" style="border:none;border-radius:999px;padding:0.8rem 1.2rem;background:#5c6ac4;color:#fff;font-weight:700;cursor:pointer;">See the menu</button>
            </div>
        </div>

        <!-- Hot Now Products -->
        <div style="margin-bottom: 2rem;">
            <h3 style="color:var(--text-main);margin-bottom:0.35rem;">Hot now products</h3>
            <p style="color:var(--text-muted);margin-bottom:1rem;">Only items featured here are available through the quick order panel.</p>
            <div class="hot-products-scroll">
                <div class="hot-product-item">
                    <div style="height:80px;background:#cbebff;border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                    <div class="badge">Classic Series</div>
                    <strong>Classic Pearl</strong>
                    <div style="font-weight:800;margin-top:0.3rem;">₱110.00</div>
                </div>
                <div class="hot-product-item">
                    <div style="height:80px;background:#cbebff;border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                    <div class="badge">Specialty</div>
                    <strong>Okinawa Brown Sugar</strong>
                    <div style="font-weight:800;margin-top:0.3rem;">₱130.00</div>
                </div>
                <div class="hot-product-item">
                    <div style="height:80px;background:#cbebff;border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                    <div class="badge">Flavored Tea</div>
                    <strong>Taro Milk Tea</strong>
                    <div style="font-weight:800;margin-top:0.3rem;">₱120.00</div>
                </div>
                <div class="hot-product-item">
                    <div style="height:80px;background:#cbebff;border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                    <div class="badge">Cheese Foam</div>
                    <strong>Matcha Cream</strong>
                    <div style="font-weight:800;margin-top:0.3rem;">₱140.00</div>
                </div>
            </div>
        </div>

        <!-- Shop Highlight -->
        <div style="background:#ffffff;border:1px solid #f0e6db;border-radius:20px;padding:1.5rem;">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
                <div><p style="font-size:0.75rem;font-weight:700;color:#5e7cff;margin-bottom:0.35rem;">SHOP HIGHLIGHT</p><h3 style="margin:0;">Why customers keep coming back</h3></div>
                <button onclick="window.parent.location.href='admin_page/menu.php?iframe=1'" style="border:none;border-radius:999px;padding:0.85rem 1.2rem;background:#ff5a8f;color:#fff;font-weight:700;cursor:pointer;">Order your favorite</button>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                <div style="background:#f8f7ff;border-radius:16px;padding:1rem;"><h4 style="margin:0 0 0.5rem 0;">Fast pickup</h4><p style="color:var(--text-muted);font-size:0.9rem;">Grab your drink quickly with our ready-to-go service for busy hours.</p></div>
                <div style="background:#fff2a8;border-radius:16px;padding:1rem;"><h4 style="margin:0 0 0.5rem 0;">Daily rewards</h4><p style="color:#2c3e50;font-size:0.9rem;">Earn stamps and redeem special perks each time you visit.</p></div>
                <div style="background:#ffd6e7;border-radius:16px;padding:1rem;"><h4 style="margin:0 0 0.5rem 0;">Staff favorites</h4><p style="color:#2c3e50;font-size:0.9rem;">Our top picks right now, curated by the shop team.</p></div>
            </div>
        </div>

    </div>

</body>
</html>