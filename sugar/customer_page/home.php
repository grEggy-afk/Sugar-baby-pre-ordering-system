<!-- customer_page/home.php -->
<style>
    /* Ensure these styles match your customer dashboard's variables */
    .dashboard-wrap { padding: 0; width: 100%; max-width: 100%; }

    /* ===== HOME FEATURES ===== */
    .feature-banner {
        background: linear-gradient(135deg, var(--pastel-yellow), var(--pastel-pink));
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
        background: var(--bg-card);
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

<div class="dashboard-wrap">

    <!-- HERO BANNER (Customized for customers) -->
    <div class="feature-banner">
        <p style="font-size:0.75rem;font-weight:700;color:#ff4d7a;margin-bottom:0.45rem;">GREAT DEALS</p>
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <h2>Hot now & new</h2>
                <p style="color:var(--text-muted);max-width:400px;">Discover fresh favorites, limited offers, and the easiest way to order your next drink.</p>
            </div>
            <div style="display:flex;gap:0.75rem;align-items:center;">
                <a href="?page=menu" style="background:transparent;border:none;font-weight:700;cursor:pointer;color:var(--text-main);text-decoration:none;">Browse menu</a>
                <a href="?page=menu" style="background:#ff5a8f;color:#fff;border:none;padding:0.9rem 1.3rem;border-radius:999px;font-weight:700;cursor:pointer;text-decoration:none;">Order now</a>
            </div>
        </div>
    </div>

    <!-- FEATURE CONTAINER -->
    <div class="feature-container">
        <!-- Loyalty Card -->
        <div class="feature-card">
            <p style="font-size:0.75rem;font-weight:700;color:#ff4d7a;margin-bottom:0.4rem;">LOYALTY REWARDS</p>
            <h3 style="margin:0 0 1rem 0;">Loyalty Card</h3>
            <p style="color:var(--text-muted);margin-bottom:1.2rem;">Show the physical loyalty card in-store to claim free toppings, upsizes, and XL upgrades.</p>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="display:flex;gap:0.4rem;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-pink);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-pink);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-pink);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-pink);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-pink);display:flex;align-items:center;justify-content:center;font-weight:700;">5</div>
                    </div>
                    <span style="font-weight:700;color:#a33c5e;">Extra toppings</span>
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="display:flex;gap:0.4rem;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-blue);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-blue);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-blue);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-blue);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-blue);display:flex;align-items:center;justify-content:center;font-weight:700;">10</div>
                    </div>
                    <span style="font-weight:700;color:#3f6f91;">Free upsize</span>
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="display:flex;gap:0.4rem;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-yellow);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-yellow);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-yellow);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-yellow);"></div><div style="width:40px;height:40px;border-radius:50%;background:var(--pastel-yellow);display:flex;align-items:center;justify-content:center;font-weight:700;">15</div>
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
            <a href="?page=menu" style="border:none;border-radius:999px;padding:0.8rem 1.2rem;background:#5c6ac4;color:#fff;font-weight:700;cursor:pointer;display:inline-block;text-decoration:none;">See the menu</a>
        </div>
    </div>

    <!-- Hot Now Products -->
    <div style="margin-bottom: 2rem;">
        <h3 style="color:var(--text-main);margin-bottom:0.35rem;">Hot now products</h3>
        <p style="color:var(--text-muted);margin-bottom:1rem;">Only items featured here are available through the quick order panel.</p>
        <div class="hot-products-scroll">
            <!-- Product 1 -->
            <div class="hot-product-item">
                <div style="height:80px;background:var(--pastel-blue);border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                <div class="badge">Classic Series</div>
                <strong>Classic Pearl</strong>
                <div style="font-weight:800;margin-top:0.3rem;">₱110.00</div>
            </div>
            <!-- Product 2 -->
            <div class="hot-product-item">
                <div style="height:80px;background:var(--pastel-blue);border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                <div class="badge">Specialty</div>
                <strong>Okinawa Brown Sugar</strong>
                <div style="font-weight:800;margin-top:0.3rem;">₱130.00</div>
            </div>
            <!-- Product 3 -->
            <div class="hot-product-item">
                <div style="height:80px;background:var(--pastel-blue);border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                <div class="badge">Flavored Tea</div>
                <strong>Taro Milk Tea</strong>
                <div style="font-weight:800;margin-top:0.3rem;">₱120.00</div>
            </div>
            <!-- Product 4 -->
            <div class="hot-product-item">
                <div style="height:80px;background:var(--pastel-blue);border-radius:12px;margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-mug-hot" style="font-size:2rem;color:#2c3e50;"></i></div>
                <div class="badge">Cheese Foam</div>
                <strong>Matcha Cream</strong>
                <div style="font-weight:800;margin-top:0.3rem;">₱140.00</div>
            </div>
        </div>
    </div>

    <!-- Shop Highlight -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.5rem;">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
            <div>
                <p style="font-size:0.75rem;font-weight:700;color:#5e7cff;margin-bottom:0.35rem;">SHOP HIGHLIGHT</p>
                <h3 style="margin:0;">Why customers keep coming back</h3>
            </div>
            <a href="?page=menu" style="border:none;border-radius:999px;padding:0.85rem 1.2rem;background:#ff5a8f;color:#fff;font-weight:700;cursor:pointer;display:inline-block;text-decoration:none;">Order your favorite</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
            <div style="background:#f8f7ff;border-radius:16px;padding:1rem;"><h4 style="margin:0 0 0.5rem 0;">Fast pickup</h4><p style="color:var(--text-muted);font-size:0.9rem;">Grab your drink quickly with our ready-to-go service for busy hours.</p></div>
            <div style="background:var(--pastel-yellow);border-radius:16px;padding:1rem;"><h4 style="margin:0 0 0.5rem 0;">Daily rewards</h4><p style="color:#2c3e50;font-size:0.9rem;">Earn stamps and redeem special perks each time you visit.</p></div>
            <div style="background:var(--pastel-pink);border-radius:16px;padding:1rem;"><h4 style="margin:0 0 0.5rem 0;">Staff favorites</h4><p style="color:#2c3e50;font-size:0.9rem;">Our top picks right now, curated by the shop team.</p></div>
        </div>
    </div>

</div>