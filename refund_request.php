<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle refund request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_refund'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $reason = trim($_POST['reason']);
    
    if (empty($reason)) {
        $message = 'Please provide a reason for the refund.';
        $message_type = 'error';
    } else {
        // In real scenario, insert into refund_requests table
        // For demo, just show success message
        $message = 'Your refund request has been submitted successfully. You will be contacted within 24-48 hours.';
        $message_type = 'success';
    }
}

// Get eligible appointments for refund (cancelled or completed within last 30 days)
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$appointments = [];
$stmt = $conn->prepare("
    SELECT a.id AS appointment_id, a.date AS app_date, a.time AS app_time, a.status,
           d.name AS doctor_name,
           s.service_name
    FROM appointment a
    JOIN doctor d ON a.doctor_id = d.doctor_id
    LEFT JOIN service s ON d.service_id = s.service_id
    WHERE a.user_id = ? AND (a.status = 'Cancelled' OR (a.status = 'Confirmed' AND a.date >= ?))
    ORDER BY a.date DESC, a.time DESC
");
$stmt->bind_param("is", $user_id, $thirty_days_ago);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Request – DOC.lk</title>
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

        .page-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .page-header h1 i { color: #2563eb; margin-right: 0.5rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: #2563eb; font-weight: 600; text-decoration: none; margin-bottom: 1.5rem; }
        .back-link:hover { text-decoration: underline; }

        .alert { padding: 0.8rem 1.2rem; border-radius: 14px; font-size: 0.9rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem; border-left: 5px solid; }
        .alert-success { background: #f0fdf4; color: #166534; border-color: #22c55e; }
        .alert-danger { background: #fef2f2; color: #b91c1c; border-color: #dc2626; }

        .refund-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 1.5rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            margin-bottom: 1rem;
        }
        .refund-card .appointment-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .refund-card .appointment-info .doc-name { font-weight: 700; color: #0f172a; font-size: 1.05rem; }
        .refund-card .appointment-info .doc-specialty { color: #2563eb; font-size: 0.9rem; }
        .refund-card .appointment-info .date-time { color: #64748b; font-size: 0.85rem; }

        .refund-form { display: flex; gap: 0.8rem; flex-wrap: wrap; align-items: center; margin-top: 0.8rem; }
        .refund-form textarea {
            flex: 1;
            min-width: 200px;
            padding: 0.6rem 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            resize: vertical;
        }
        .refund-form textarea:focus {
            outline: none;
            border-color: #2563eb;
        }
        .btn-submit-refund {
            background: #f59e0b;
            color: #fff;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn-submit-refund:hover {
            background: #d97706;
            transform: translateY(-2px);
        }

        .empty-state { text-align: center; padding: 3rem 0; color: #94a3b8; }
        .empty-state i { font-size: 3rem; display: block; margin-bottom: 0.5rem; color: #cbd5e1; }

        @media (max-width: 768px) {
            .refund-card .appointment-info { flex-direction: column; align-items: flex-start; }
            .refund-form { flex-direction: column; }
            .refund-form textarea { width: 100%; }
        }

        .footer { background: #0f172a; color: #cbd5e1; padding: 2.5rem 2rem 1rem; border-radius: 28px 28px 0 0; margin-top: 2.5rem; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem; }
        .footer-grid h4 { color: #fff; margin-bottom: 0.8rem; font-size: 1.1rem; }
        .footer-grid p, .footer-grid a { color: #94a3b8; font-size: 0.9rem; line-height: 1.8; }
        .footer-grid a:hover { color: #fff; }
        .footer-bottom { border-top: 1px solid #1e293b; padding-top: 1.2rem; text-align: center; font-size: 0.85rem; color: #64748b; }
    </style>
</head>
<body>

    <nav class="navbar-modern">
        <div class="brand"><i class="fas fa-stethoscope"></i> DOC.<span>lk</span></div>
        <button class="hamburger" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
        <div class="nav-center" id="navCenter">
            <a href="main_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="my_booking.php"><i class="fas fa-calendar-check"></i> My Booking</a>
            <a href="refund_request.php" class="active"><i class="fas fa-undo-alt"></i> Refund Request</a>
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

        <div class="page-header">
            <h1><i class="fas fa-undo-alt"></i> Refund Request</h1>
            <span style="color:#64748b;font-size:0.9rem;"><?php echo count($appointments); ?> eligible appointments</span>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (count($appointments) > 0): ?>
            <?php foreach ($appointments as $app): ?>
                <div class="refund-card">
                    <div class="appointment-info">
                        <div>
                            <div class="doc-name"><?php echo htmlspecialchars($app['doctor_name']); ?></div>
                            <div class="doc-specialty"><?php echo htmlspecialchars($app['service_name'] ?? 'General Medicine'); ?></div>
                        </div>
                        <div class="date-time">
                            <?php echo date('Y-m-d', strtotime($app['app_date'])); ?> at <?php echo date('h:i A', strtotime($app['app_time'])); ?>
                            <span style="margin-left:0.5rem;background:<?php echo ($app['status']=='Cancelled')?'#ef4444':'#22c55e'; ?>;color:#fff;padding:0.2rem 0.6rem;border-radius:40px;font-size:0.7rem;"><?php echo $app['status']; ?></span>
                        </div>
                    </div>
                    <form method="POST" class="refund-form">
                        <input type="hidden" name="appointment_id" value="<?php echo $app['appointment_id']; ?>">
                        <textarea name="reason" placeholder="Reason for refund (e.g., duplicate booking, change of plans, etc.)" rows="1" required></textarea>
                        <button type="submit" name="submit_refund" class="btn-submit-refund">
                            <i class="fas fa-paper-plane"></i> Request Refund
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <p>No appointments available for refund request.</p>
                <p style="font-size:0.9rem;color:#94a3b8;">You can request a refund for cancelled or recent confirmed appointments.</p>
            </div>
        <?php endif; ?>

    </div>

    <div class="footer">
        <div class="container" style="padding:0;">
            <div class="footer-grid">
                <div><h4>DOC.<span style="color:#2563eb;">lk</span></h4><p>Your trusted partner for hassle-free doctor channeling.</p></div>
                <div><h4>Quick Links</h4><a href="#">About Us</a><br><a href="#">Contact Us</a><br><a href="#">Terms of Service</a></div>
                <div><h4>Contact Info</h4><p><i class="fas fa-map-marker-alt" style="color:#2563eb;"></i> Colombo 07, Sri Lanka</p><p><i class="fas fa-phone" style="color:#2563eb;"></i> +94 112 345 678</p><p><i class="fas fa-envelope" style="color:#2563eb;"></i> info@doc.lk</p></div>
            </div>
            <div class="footer-bottom">&copy; <?php echo date('Y'); ?> DOC.lk. All rights reserved.</div>
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