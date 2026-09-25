<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = isset($_GET['success']) ? trim($_GET['success']) : '';

// Get appointment count
$stmt = $conn->prepare("SELECT COUNT(*) FROM appointment WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($appointment_count);
$stmt->fetch();
$stmt->close();

// Get upcoming appointments count
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) FROM appointment WHERE user_id = ? AND date >= ? AND status != 'Cancelled'");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$stmt->bind_result($upcoming_count);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .navbar-modern {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            padding: 0.6rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.04);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-modern .brand {
            font-size: 1.7rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-modern .brand i { color: #2563eb; font-size: 1.8rem; }
        .navbar-modern .brand span { color: #2563eb; }
        .navbar-modern .nav-center {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            flex-wrap: wrap;
        }
        .navbar-modern .nav-center a {
            font-weight: 500;
            color: #475569;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-modern .nav-center a:hover {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.06);
        }
        .navbar-modern .nav-center a.active {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.08);
            font-weight: 600;
        }
        .navbar-modern .nav-right {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .navbar-modern .profile-dropdown {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(0,0,0,0.02);
            padding: 0.3rem 0.8rem 0.3rem 0.5rem;
            border-radius: 60px;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .navbar-modern .profile-dropdown .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .navbar-modern .profile-dropdown .user-name {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.9rem;
        }
        .navbar-modern .btn-logout {
            background: #ef4444;
            color: #fff !important;
            padding: 0.4rem 1.2rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            border: none;
            cursor: pointer;
        }
        .navbar-modern .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }
        .navbar-modern .hamburger {
            display: none;
            font-size: 1.5rem;
            color: #0f172a;
            cursor: pointer;
            background: none;
            border: none;
        }
        @media (max-width: 820px) {
            .navbar-modern .hamburger { display: block; }
            .navbar-modern .nav-center {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 0.3rem;
                padding: 0.5rem 0;
                border-top: 1px solid rgba(0,0,0,0.04);
                margin-top: 0.5rem;
            }
            .navbar-modern .nav-center.open { display: flex; }
            .navbar-modern .nav-center a { justify-content: center; padding: 0.6rem; }
            .navbar-modern .nav-right { flex-wrap: wrap; }
        }
        @media (max-width: 480px) {
            .navbar-modern { padding: 0.6rem 1rem; }
            .navbar-modern .brand { font-size: 1.3rem; }
            .navbar-modern .profile-dropdown .user-name { display: none; }
            .navbar-modern .btn-logout { padding: 0.3rem 0.8rem; font-size: 0.8rem; }
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
        }
        .page-header h1 i { color: #2563eb; margin-right: 0.5rem; }
        .page-header .stats {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .page-header .stats .stat-item {
            background: #f8fafc;
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #1e293b;
        }
        .page-header .stats .stat-item strong {
            color: #2563eb;
            font-size: 1.1rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 1.5rem;
        }
        .back-link:hover { text-decoration: underline; }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .feature-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 2rem 1.5rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: all 0.25s;
            text-decoration: none;
            color: #0f172a;
            display: block;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(37,99,235,0.06);
            border-color: #2563eb;
        }
        .feature-card .icon {
            font-size: 2.5rem;
            color: #2563eb;
            background: #eff6ff;
            width: 60px;
            height: 60px;
            border-radius: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .feature-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .feature-card p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }
        .feature-card .arrow {
            float: right;
            color: #94a3b8;
            font-size: 1.2rem;
            transition: 0.2s;
        }
        .feature-card:hover .arrow {
            color: #2563eb;
            transform: translateX(4px);
        }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
            .feature-grid { grid-template-columns: 1fr; }
        }

        .footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 2.5rem 2rem 1rem;
            border-radius: 28px 28px 0 0;
            margin-top: 2.5rem;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .footer-grid h4 { color: #fff; margin-bottom: 0.8rem; font-size: 1.1rem; }
        .footer-grid p, .footer-grid a { color: #94a3b8; font-size: 0.9rem; line-height: 1.8; }
        .footer-grid a:hover { color: #fff; }
        .footer-bottom {
            border-top: 1px solid #1e293b;
            padding-top: 1.2rem;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- ====== NAVBAR ====== -->
    <nav class="navbar-modern">
        <div class="brand">
            <i class="fas fa-stethoscope"></i>
            DOC.<span>lk</span>
        </div>
        <button class="hamburger" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
        <div class="nav-center" id="navCenter">
            <a href="main_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="my_booking.php" class="active"><i class="fas fa-calendar-check"></i> My Booking</a>
            <a href="../appointments.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
        </div>
        <div class="nav-right">
            <div class="profile-dropdown">
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            </div>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">

        <a href="main_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

        <!-- ====== PAGE HEADER ====== -->
        <div class="page-header">
            <h1><i class="fas fa-calendar-check"></i> My Booking</h1>
            <div class="stats">
                <span class="stat-item"><strong><?php echo $appointment_count; ?></strong> Total Appointments</span>
                <span class="stat-item"><strong><?php echo $upcoming_count; ?></strong> Upcoming</span>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" style="margin-bottom:1.5rem; padding:1rem 1.25rem; border-radius:18px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <p style="color:#64748b;margin-bottom:2rem;">Manage your appointments, view history, and rate your doctors.</p>

        <!-- ====== FEATURES GRID ====== -->
        <div class="feature-grid">
            <a href="view_appointments.php" class="feature-card">
                <div class="icon"><i class="fas fa-list"></i></div>
                <h3>View Upcoming Appointments</h3>
                <p>See all your upcoming appointments</p>
                <span class="arrow"><i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="cancel_reschedule.php" class="feature-card">
                <div class="icon"><i class="fas fa-sync-alt"></i></div>
                <h3>Cancel or Reschedule</h3>
                <p>Cancel or reschedule your bookings</p>
                <span class="arrow"><i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="appointment_history.php" class="feature-card">
                <div class="icon"><i class="fas fa-history"></i></div>
                <h3>View Appointment History</h3>
                <p>View your past appointments</p>
                <span class="arrow"><i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="download_receipt.php" class="feature-card">
                <div class="icon"><i class="fas fa-download"></i></div>
                <h3>Download Receipts</h3>
                <p>Download your appointment receipts</p>
                <span class="arrow"><i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="rate_doctor.php" class="feature-card">
                <div class="icon"><i class="fas fa-star"></i></div>
                <h3>Rate Your Doctor</h3>
                <p>Share your experience with doctors</p>
                <span class="arrow"><i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="terms_conditions.php" class="feature-card">
                <div class="icon"><i class="fas fa-file-contract"></i></div>
                <h3>Terms & Conditions</h3>
                <p>Read the platform policies and booking terms</p>
                <span class="arrow"><i class="fas fa-arrow-right"></i></span>
            </a>
        </div>

    </div>

    <!-- ====== FOOTER ====== -->
    <div class="footer">
        <div class="container" style="padding:0;">
            <div class="footer-grid">
                <div>
                    <h4>DOC.<span style="color:#2563eb;">lk</span></h4>
                    <p>Your trusted partner for hassle-free doctor channeling.</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <a href="#">About Us</a><br>
                    <a href="#">Contact Us</a><br>
                    <a href="#">Terms of Service</a>
                </div>
                <div>
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-map-marker-alt" style="color:#2563eb;"></i> Colombo 07, Sri Lanka</p>
                    <p><i class="fas fa-phone" style="color:#2563eb;"></i> +94 112 345 678</p>
                    <p><i class="fas fa-envelope" style="color:#2563eb;"></i> info@doc.lk</p>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> DOC.lk. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        function toggleNav() {
            document.getElementById('navCenter').classList.toggle('open');
        }
        document.querySelectorAll('.nav-center a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navCenter').classList.remove('open');
            });
        });
    </script>
</body>
</html>