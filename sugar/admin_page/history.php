<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$in_iframe = isset($_GET['iframe']) && $_GET['iframe'] === '1';
$admin_name = $_SESSION['user_name'] ?? 'Store Manager';

// --- FETCH ALL HISTORY (Completed, Cancelled, Refunded) ---
$history_query = $conn->query("
    SELECT * FROM orders 
    WHERE status IN ('completed', 'cancelled', 'refunded') 
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - Sugar Baby Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- HEADER --- */
        .history-header {
            margin-bottom: 1.5rem;
        }
        .history-header h2 {
            font-family: 'Fredoka', cursive;
            color: var(--text-main);
        }
        .history-header p {
            color: var(--text-muted);
        }

        /* --- EMPTY STATE --- */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border);
        }

        /* --- HISTORY TABLE --- */
        .table-container {
            background: var(--bg-card);
            border-radius: 16px;
            border: 2px solid var(--border);
            overflow-x: auto;
            box-shadow: var(--card-shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        thead {
            background-color: var(--pastel-blue);
            color: var(--sidebar-text);
            border-bottom: 2px solid var(--border);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: var(--bg-main);
        }

        th {
            font-weight: 700;
        }
        td {
            vertical-align: middle;
        }

        /* --- STATUS BADGES --- */
        .badge-status {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-refunded {
            background-color: #fff3cd;
            color: #856404;
        }

        /* --- ACTION BUTTONS --- */
        .btn-view {
            background-color: var(--pastel-yellow);
            color: #856404;
            border: none;
            padding: 0.3rem 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-view:hover {
            background-color: var(--pastel-yellow-dark);
        }

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
            <ul class="nav-links">
                <li><a class="nav-item active" data-tab="history"><i class="fa-solid fa-clock-rotate-left"></i> History</a></li>
            </ul>
        </div>
        <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
    </aside>
<?php endif; ?>

    <div class="main-wrapper">
<?php if (!$in_iframe): ?>
        <header>
            <div class="search-bar"><i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted)"></i><input type="text" placeholder="Search..."></div>
            <div class="user-profile">
                <div class="user-trigger">
                    <div class="user-info"><div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div><div class="user-role">Admin</div></div>
                    <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 2)); ?></div>
                </div>
            </div>
        </header>
<?php endif; ?>

        <div class="content-container">

            <div class="history-header">
                <h2>Order History</h2>
                <p>Chronological record of all completed, cancelled, and refunded orders.</p>
            </div>

            <?php if ($history_query && $history_query->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $history_query->fetch_assoc()): 
                                $badge_class = match($order['status']) {
                                    'completed' => 'badge-completed',
                                    'cancelled' => 'badge-cancelled',
                                    'refunded'  => 'badge-refunded',
                                    default     => ''
                                };
                            ?>
                            <tr>
                                <td style="font-weight:700;">#ORD-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                                <td style="font-weight:700;">₱<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <span class="badge-status <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <button class="btn-view" onclick="viewHistoryOrder(<?php echo $order['id']; ?>)">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <p>No past orders found yet.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ORDER DETAIL MODAL (Reusing the one from Orders) -->
    <div id="orderModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; z-index:999; padding:16px;">
        <div class="modal-content" style="background:var(--bg-card); padding:24px; border-radius:16px; border:2px solid var(--border); width:100%; max-width:580px; max-height:85vh; overflow-y:auto; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                <h3>Order Details</h3>
                <button class="close-modal" onclick="closeHistoryModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-muted);">&times;</button>
            </div>
            <div id="modalBody"><p style="padding:40px; text-align:center;">Loading...</p></div>
        </div>
    </div>

    <script>
        function viewHistoryOrder(orderId) {
            const modal = document.getElementById('orderModal');
            const body = document.getElementById('modalBody');
            
            modal.style.display = 'flex';
            body.innerHTML = `<p style="padding:40px; text-align:center;">Loading order details...</p>`;

            fetch(`../api/get_order_details.php?id=${orderId}`)
                .then(r => r.text())
                .then(html => {
                    body.innerHTML = html;
                })
                .catch(err => {
                    body.innerHTML = `<p style="color:red; text-align:center;">Failed to load details.</p>`;
                });
        }

        function closeHistoryModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeHistoryModal();
            }
        });
    </script>
</body>
</html>