<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$in_iframe = isset($_GET['iframe']) && $_GET['iframe'] === '1';
$admin_name = $_SESSION['user_name'] ?? 'Store Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Sugar Baby Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
       
        .about-hero { 
            background: linear-gradient(135deg, var(--pastel-pink) 0%, var(--pastel-yellow) 100%); 
            padding: 3rem 2rem; 
            border-radius: 20px; 
            text-align: center; 
            margin-bottom: 2rem; 
            border: 2px solid var(--pastel-pink-dark); 
        }
        .about-hero h1 { 
            font-family: 'Fredoka', cursive; 
            font-size: 2.8rem; 
            color: #2c3e50; 
            margin-bottom: 0.3rem; 
        }
        .about-hero p { 
            font-size: 1.1rem; 
            color: var(--text-main); 
        }
        .about-hero .btn-back {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
            background: #2c3e50;
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
        }
        .about-hero .btn-back:hover {
            background: #1a252f;
        }

        .about-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 2rem; 
            margin-bottom: 3rem; 
            align-items: center; 
        }
        .about-image-box { 
            background: var(--pastel-blue); 
            height: 250px; 
            border-radius: 20px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border: 2px solid var(--border); 
        }
        .about-image-box i { 
            font-size: 6rem; 
            color: #ffffff; 
            text-shadow: 0 4px 10px rgba(0,0,0,0.1); 
        }
        .about-text-box h2 { 
            font-family: 'Fredoka', cursive; 
            color: var(--text-main); 
            margin-bottom: 0.5rem; 
        }
        .about-text-box p { 
            color: var(--text-muted); 
            line-height: 1.7; 
            margin-bottom: 1rem; 
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2.5rem;
        }
        .info-card {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: 16px;
            border: 2px solid var(--border);
            box-shadow: var(--card-shadow);
        }
        .info-card h3 {
            font-family: 'Fredoka', cursive;
            margin-bottom: 0.8rem;
        }
        .info-card i {
            color: var(--pastel-pink-dark);
            margin-right: 0.5rem;
        }

      
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }
        .stat-box {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            border: 2px solid var(--border);
            box-shadow: var(--card-shadow);
        }
        .stat-box h3 {
            font-family: 'Fredoka', cursive;
            font-size: 2rem;
            color: var(--pastel-pink-dark);
            margin-bottom: 0.2rem;
        }
        .stat-box p {
            color: var(--text-muted);
        }

      
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .team-card {
            background: var(--bg-card);
            padding: 1rem;
            border-radius: 16px;
            text-align: center;
            border: 2px solid var(--border);
            box-shadow: var(--card-shadow);
            transition: transform 0.2s;
        }
        .team-card:hover {
            transform: translateY(-5px);
            border-color: var(--pastel-yellow-dark);
        }
        .team-card strong {
            display: block;
            color: var(--text-main);
        }
        .team-card small {
            color: var(--text-muted);
        }

        .page-footer {
            margin-top: 2rem;
            padding: 1.5rem;
            text-align: center;
            background: var(--bg-card);
            border-radius: 16px;
            border: 2px solid var(--border);
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .page-footer strong {
            color: var(--text-main);
        }

        
        @media (max-width: 768px) {
            .about-grid { grid-template-columns: 1fr; }
            .info-grid { grid-template-columns: 1fr; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
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
                <li><a class="nav-item active" data-tab="about"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
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

           
            <div class="about-hero">
                <h1>About Us</h1>
                <p>We make every cup part of your journey.</p>
                <a href="../admin_dashboard.php" class="btn-back">Back</a>
            </div>

           
            <div class="about-grid">
                <div class="about-image-box">
                    <i class="fa-solid fa-mug-hot"></i>
                </div>
                <div class="about-text-box">
                    <h2>Your Daily Cup of Motivation.</h2>
                    <p>Sugar Baby Milk Tea & Coffee is dedicated to serving happiness in every cup. Our passion for quality ingredients and creative flavors makes every sip special.</p>
                    <p style="margin-top: 0.5rem;"><strong>Marvin M. bayan</strong><br><span style="color:var(--text-muted);">— Sugar Baby Founder</span></p>
                </div>
            </div>

            <div class="about-grid">
                <div class="about-text-box">
                    <h2>Your Campus Fuel Station.</h2>
                    <p>We aim to provide affordable, delicious milk tea and coffee that energize, inspire productivity, and create a motivation for learning, connection, and success.</p>
                </div>
                <div class="about-image-box">
                    <i class="fa-solid fa-school"></i>
                </div>
            </div>

       
            <div class="about-grid">
                <div class="about-image-box">
                    <i class="fa-solid fa-rocket"></i>
                </div>
                <div class="about-text-box">
                    <h2>We help businesses grow faster and bigger</h2>
                    <p>With innovative flavors and strong partnerships, we help businesses expand their reach and achieve long-term success.</p>
                </div>
            </div>

           
            <div class="stats-row">
                <div class="stat-box">
                    <h3>hehe</h3>
                    <p>what</p>
                </div>
                <div class="stat-box">
                    <h3>haha</h3>
                    <p>what</p>
                </div>
                <div class="stat-box">
                    <h3>huhu</h3>
                    <p>what</p>
                </div>
                <div class="stat-box">
                    <h3>hihi</h3>
                    <p>what</p>
                </div>
            </div>

        
            <div class="info-grid">
              
                <div class="info-card">
                    <h3>Store Info Management</h3>
                    <p><i class="fa-solid fa-phone"></i> <strong>Store Contact Info:</strong><br>+63 111 111 1111 | sugarbaby@example.com</p>
                    <p><i class="fa-solid fa-location-dot"></i> <strong>Location Details:</strong><br>ALmuni Canteen (beside hostel), Science City Of Munoz, Nueva Ecija</p>
                    <p><i class="fa-solid fa-bullhorn"></i> <strong>Announcements:</strong><br>New flavor launch coming soon! Stay tuned for updates.</p>
                </div>

               
                <div class="info-card">
                    <h3>System Info & Credits</h3>
                    <p><i class="fa-solid fa-code"></i> <strong>System Version:</strong> v1.0.0</p>
                    <p><strong>Development Team:</strong></p>
                    <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.2rem;">
                        <span style="background:var(--pastel-blue); padding:0.2rem 0.6rem; border-radius:12px; font-size:0.8rem;">Almirol, Johnelle C.</span>
                        <span style="background:var(--pastel-blue); padding:0.2rem 0.6rem; border-radius:12px; font-size:0.8rem;">Banaag, Justine T.</span>
                        <span style="background:var(--pastel-blue); padding:0.2rem 0.6rem; border-radius:12px; font-size:0.8rem;">Duran, Rexyl DC.</span>
                        <span style="background:var(--pastel-blue); padding:0.2rem 0.6rem; border-radius:12px; font-size:0.8rem;">Fronda, Greg James L.</span>
                        <span style="background:var(--pastel-blue); padding:0.2rem 0.6rem; border-radius:12px; font-size:0.8rem;">Quinto, Arlyn C.</span>
                        <span style="background:var(--pastel-blue); padding:0.2rem 0.6rem; border-radius:12px; font-size:0.8rem;">Saturno, Ariangayle C.</span>
                        <span style="background:var(--pastel-blue); padding:0.2rem 0.6rem; border-radius:12px; font-size:0.8rem;">Sunga, John Laurenz S.</span>
                    </div>
                    <p style="margin-top:0.5rem;"><i class="fa-solid fa-user-tie"></i> <strong>Store Owner:</strong> Marvin M. bayan</p>
                </div>
            </div>

          
            <div class="page-footer">
                &copy; 2026 <strong>Sugar Baby Milk Tea & Coffee drinks</strong> — All Rights Reserved
            </div>

        </div>
    </div>
</body>
</html>