<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$appointment_no = isset($_GET['appointment_no']) ? trim($_GET['appointment_no']) : '';
if (empty($appointment_no)) {
    header('Location: main_dashboard.php');
    exit;
}


$stmt = $conn->prepare("
    SELECT a.id, a.appointment_no, a.date, a.time, a.status,
           d.name AS doctor_name, d.contact_no, s.service_name
    FROM appointment a
    JOIN doctor d ON a.doctor_id = d.doctor_id
    LEFT JOIN service s ON d.service_id = s.service_id
    WHERE a.appointment_no = ? AND a.user_id = ?
");
$stmt->bind_param("si", $appointment_no, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

if (!$appointment) {
    header('Location: main_dashboard.php');
    exit;
}

$stmt = $conn->prepare("SELECT payment_id FROM payment WHERE appointment_no = ? AND status = 'Paid'");
$stmt->bind_param("s", $appointment_no);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header('Location: main_dashboard.php?success=Appointment already paid');
    exit;
}
$stmt->close();

// Default amount
$amount = 2500.00;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .navbar-modern {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px) saturate(180%);
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
        }
        .navbar-modern .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-1px);
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
                gap: 0.3rem;
                padding: 0.5rem 0;
                border-top: 1px solid rgba(0,0,0,0.04);
                margin-top: 0.5rem;
            }
            .navbar-modern .nav-center.open { display: flex; }
            .navbar-modern .nav-center a { justify-content: center; padding: 0.6rem; }
        }

        .payment-container {
            max-width: 600px;
            margin: 2rem auto;
            background: #ffffff;
            border-radius: 32px;
            padding: 2.5rem;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.08);
            border: 1px solid #f1f5f9;
        }
        .payment-container h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .payment-container h2 i { color: #2563eb; }
        .payment-container .subtitle {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
        .payment-container .appointment-details {
            background: #f8fafc;
            padding: 1.2rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .payment-container .appointment-details p {
            margin: 0.3rem 0;
            font-size: 0.95rem;
            color: #1e293b;
        }
        .payment-container .appointment-details strong { color: #0f172a; }
        .payment-container .amount-box {
            text-align: center;
            padding: 1.2rem;
            background: #eff6ff;
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .payment-container .amount-box .amount {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2563eb;
        }
        .payment-container .amount-box .label {
            font-size: 0.9rem;
            color: #475569;
        }
        .payment-form .form-group {
            margin-bottom: 1.2rem;
        }
        .payment-form .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
            margin-bottom: 0.3rem;
        }
        .payment-form .form-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
            background: #f8fafc;
            transition: 0.2s;
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }
        .payment-form .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
            background: #ffffff;
        }
        .payment-form .btn-pay {
            width: 100%;
            padding: 0.9rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1.05rem;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
        }
        .payment-form .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(34, 197, 94, 0.4);
        }
        .back-link {
            display: inline-block;
            margin-top: 1.2rem;
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        @media (max-width: 600px) {
            .payment-container { padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <nav class="navbar-modern">
        <div class="brand">
            <i class="fas fa-stethoscope"></i>
            DOC.<span>lk</span>
        </div>
        <button class="hamburger" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
        <div class="nav-center" id="navCenter">
            <a href="main_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
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

        <div class="payment-container">
            <h2><i class="fas fa-credit-card"></i> Complete Payment</h2>
            <p class="subtitle">Please confirm your appointment details and make the payment.</p>

            <div class="appointment-details">
                <p><strong>Appointment No:</strong> <?php echo htmlspecialchars($appointment['appointment_no']); ?></p>
                <p><strong>Doctor:</strong> <?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                <p><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name'] ?? 'General'); ?></p>
                <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($appointment['date'])); ?></p>
                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($appointment['time'])); ?></p>
                <p><strong>Status:</strong> <?php echo $appointment['status']; ?></p>
            </div>

            <div class="amount-box">
                <div class="label">Total Amount</div>
                <div class="amount">LKR <?php echo number_format($amount, 2); ?></div>
            </div>

            <form action="payment_process.php" method="POST" class="payment-form">
                <input type="hidden" name="appointment_no" value="<?php echo htmlspecialchars($appointment_no); ?>">
                <input type="hidden" name="amount" value="<?php echo $amount; ?>">

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Cardholder Name</label>
                    <input type="text" name="card_name" placeholder="e.g. John Doe" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-credit-card"></i> Card Number</label>
                    <input type="text" name="card_number" placeholder="1234 5678 9012 3456" required maxlength="19">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="text" name="expiry" placeholder="MM/YY" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="cvv" placeholder="123" required maxlength="4">
                    </div>
                </div>

                <button type="submit" class="btn-pay">
                    <i class="fas fa-lock"></i> Pay Now
                </button>
            </form>

            <a href="main_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Cancel &amp; Go Back</a>
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