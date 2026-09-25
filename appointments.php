<?php
require_once 'DOCTOR_BOOKING/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: login.php');
    exit;
}


$patient = [];
$stmt = $conn->prepare("SELECT full_name, email, contact_no, address FROM user WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

$pre_selected_doctor = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;


$doctors = [];
$res = $conn->query("
    SELECT d.doctor_id, d.name, d.contact_no, s.service_name
    FROM doctor d
    LEFT JOIN service s ON d.service_id = s.service_id
    ORDER BY d.name
");
while ($row = $res->fetch_assoc()) {
    $doctors[] = $row;
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment – DOC.lk</title>
    <link rel="stylesheet" href="style.css">
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

        /* ====== BOOKING FORM CARD ====== */
        .booking-wrapper {
            max-width: 750px;
            margin: 0 auto;
            padding-top: 1.5rem;
        }
        .booking-card {
            background: #ffffff;
            border-radius: 32px;
            padding: 2.5rem 3rem;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.08), 0 8px 20px rgba(0,0,0,0.02);
            border: 1px solid rgba(226, 232, 240, 0.4);
        }
        .booking-card .form-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 0.3rem;
        }
        .booking-card .form-header i {
            font-size: 2rem;
            color: #2563eb;
            background: #eff6ff;
            padding: 0.6rem;
            border-radius: 60px;
        }
        .booking-card .form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .booking-card .subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 2rem;
            margin-left: 0.5rem;
        }
        .booking-card .form-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 1.8rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eff6ff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .booking-card .form-group {
            margin-bottom: 1.2rem;
        }
        .booking-card .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
            margin-bottom: 0.4rem;
        }
        .booking-card .form-group label i {
            color: #2563eb;
            margin-right: 0.4rem;
        }
        .booking-card .form-control {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1rem;
            background: #f8fafc;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }
        .booking-card .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
            background: #ffffff;
        }
        .booking-card .form-control[readonly] {
            background: #f1f5f9;
            color: #475569;
            cursor: not-allowed;
        }
        .booking-card .form-control::placeholder {
            color: #94a3b8;
        }
        .booking-card .btn-submit {
            width: 100%;
            padding: 0.9rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1.05rem;
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.2);
            margin-top: 1rem;
        }
        .booking-card .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(37, 99, 235, 0.35);
        }
        .booking-card .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
        }

        /* ====== ALERTS ====== */
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 14px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-left: 5px solid;
        }
        .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #dc2626;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-color: #22c55e;
        }

        @media (max-width: 600px) {
            .booking-card { padding: 1.5rem; }
            .booking-card .form-row { grid-template-columns: 1fr; }
            .booking-card .form-header h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

    <!-- ====== MODERN NAVBAR ====== -->
    <nav class="navbar-modern">
        <div class="brand">
            <i class="fas fa-stethoscope"></i>
            DOC.<span>lk</span>
        </div>
        <button class="hamburger" onclick="toggleNav()" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-center" id="navCenter">
            <a href="DOCTOR_BOOKING/main_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="appointments.php" class="active"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
        </div>
        <div class="nav-right">
            <div class="profile-dropdown">
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            </div>
            <a href="DOCTOR_BOOKING/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="booking-wrapper">

            <!-- ====== BOOKING CARD ====== -->
            <div class="booking-card">

                <div class="form-header">
                    <i class="fas fa-calendar-plus"></i>
                    <h2>New Appointment</h2>
                </div>
                <p class="subtitle">Fill in the details to book with a doctor</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                <?php endif; ?>

                <form action="DOCTOR_BOOKING/book_process.php" method="POST">

                    <!-- ====== PATIENT INFORMATION ====== -->
                    <div class="form-section-title">
                        <i class="fas fa-user"></i> Patient Information
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Patient Name</label>
                        <input type="text" name="patient_name" class="form-control"
                               value="<?php echo htmlspecialchars($patient['full_name'] ?? ''); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Address</label>
                        <input type="text" name="address" class="form-control"
                               placeholder="Enter your address"
                               value="<?php echo htmlspecialchars($patient['address'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                               placeholder="Enter your phone number"
                               value="<?php echo htmlspecialchars($patient['contact_no'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>" readonly>
                        <small style="color:#64748b;font-size:0.8rem;">Confirmation email will be sent to this address</small>
                    </div>

                    <!-- ====== APPOINTMENT DETAILS ====== -->
                    <div class="form-section-title">
                        <i class="fas fa-calendar-check"></i> Appointment Details
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-user-md"></i> Select Doctor</label>
                        <select name="doctor_id" class="form-control" required>
                            <option value="">— Select Doctor —</option>
                            <?php foreach ($doctors as $doc): ?>
                                <option value="<?php echo $doc['doctor_id']; ?>" <?php echo ($pre_selected_doctor == $doc['doctor_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($doc['name'] . ' (' . ($doc['service_name'] ?? 'General') . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-day"></i> Date</label>
                            <input type="date" name="app_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-clock"></i> Time</label>
                            <input type="time" name="app_time" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check"></i> Book Appointment
                    </button>
                </form>

                <div style="text-align:center;margin-top:1.5rem;font-size:0.9rem;color:#94a3b8;">
                    <i class="fas fa-shield-alt" style="color:#2563eb;"></i> Your data is secure and confidential
                </div>
            </div>

        </div>
    </div>

    <!-- ====== FOOTER ====== -->
    <div class="footer">
        <div class="container" style="padding: 0;">
            <div class="footer-grid">
                <div>
                    <h4>DOC.<span style="color: #2563eb;">lk</span></h4>
                    <p>Your trusted partner for hassle-free doctor channeling. Book appointments instantly with top specialists.</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <a href="#">About Us</a><br>
                    <a href="#">Contact Us</a><br>
                    <a href="#">Terms of Service</a><br>
                    <a href="#">Privacy Policy</a>
                </div>
                <div>
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-map-marker-alt" style="color:#2563eb;"></i> No. 01, Galle Road, Colombo 07</p>
                    <p><i class="fas fa-phone" style="color:#2563eb;"></i> +94 112 345 678</p>
                    <p><i class="fas fa-envelope" style="color:#2563eb;"></i> info@doc.lk</p>
                </div>
                <div>
                    <h4>Follow Us</h4>
                    <p style="font-size: 1.8rem; letter-spacing: 0.5rem;">
                        <i class="fab fa-facebook"></i>
                        <i class="fab fa-twitter"></i>
                        <i class="fab fa-instagram"></i>
                        <i class="fab fa-youtube"></i>
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> DOC.lk. All rights reserved. | Made with <i class="fas fa-heart" style="color:#ef4444;"></i> in Sri Lanka
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