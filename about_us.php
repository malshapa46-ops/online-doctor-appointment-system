<?php
require_once 'db.php';

$hospital_name = 'Nawodya Hospital';
$hospital_doctors = [];
$stmt = $conn->prepare("SELECT d.name, d.contact_no, d.email, s.service_name FROM doctor d LEFT JOIN service s ON d.service_id = s.service_id WHERE d.hospital = ? ORDER BY d.name");
$stmt->bind_param('s', $hospital_name);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $hospital_doctors[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us – DOC.lk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin:0; font-family:'Inter',sans-serif; background:linear-gradient(135deg,#f8fbff 0%,#eef4ff 100%); color:#0f172a; }
        .container { max-width: 1100px; margin:0 auto; padding:2rem 1rem 3rem; }
        .navbar { background:#fff; border-bottom:1px solid #e2e8f0; padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; }
        .brand { font-weight:800; font-size:1.4rem; color:#0f172a; }
        .brand span { color:#2563eb; }
        .nav-links a { text-decoration:none; color:#475569; font-weight:600; margin-left:1rem; }
        .nav-links a.active { color:#2563eb; }
        .hero { background:linear-gradient(135deg,#0b2b5c,#1a4a8a); color:#fff; border-radius:28px; padding:2rem; box-shadow:0 16px 40px rgba(0,0,0,0.08); }
        .hero h1 { margin:0 0 0.5rem; font-size:2rem; }
        .hero p { margin:0; color:#dbeafe; line-height:1.7; }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:24px; padding:1.2rem 1.3rem; box-shadow:0 10px 30px rgba(15,23,42,0.04); margin-top:1rem; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .stat { padding:1rem; border-radius:18px; background:#f8fbff; border:1px solid #dbeafe; }
        .stat strong { font-size:1.2rem; color:#2563eb; }
        .muted { color:#64748b; line-height:1.7; }
        .btn { display:inline-block; margin-top:0.8rem; text-decoration:none; background:#2563eb; color:#fff; padding:0.65rem 1rem; border-radius:999px; font-weight:600; }
        @media (max-width:768px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="brand">DOC.<span>lk</span></div>
    <div class="nav-links">
        <a href="main_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="about_us.php" class="active"><i class="fas fa-info-circle"></i> About Us</a>
    </div>
</nav>

<div class="container">
    <div class="hero">
        <h1><i class="fas fa-heart"></i> About DOC.lk</h1>
        <p>DOC.lk is a modern healthcare platform created to make doctor appointments faster, easier, and more reliable for patients across Sri Lanka.</p>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><i class="fas fa-hospital"></i> Who We Are</h3>
        <p class="muted">We connect patients with trusted doctors and hospitals through a simple online experience. Our goal is to bring quality healthcare closer to every family with a smooth booking process and better communication.</p>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0;"><i class="fas fa-bullseye"></i> Our Mission</h3>
            <p class="muted">To simplify medical access by offering fast appointment booking, reliable doctor information, and user-friendly healthcare support for everyone.</p>
        </div>
        <div class="card">
            <h3 style="margin-top:0;"><i class="fas fa-eye"></i> Our Vision</h3>
            <p class="muted">To become one of Sri Lanka’s most trusted digital healthcare platforms, making healthcare services easier to access and manage online.</p>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><i class="fas fa-hospital"></i> Nawodya Hospital</h3>
        <p class="muted" style="margin-top:0.2rem;"><strong>Address:</strong> No. 01, Galle Road, Colombo 07, Sri Lanka<br>
        <strong>Phone:</strong> +94 112 345 678<br>
        <strong>Email:</strong> info@nawodyahospital.lk</p>

        <h4 style="margin-bottom:0.5rem;"><i class="fas fa-user-md"></i> Our Specialist Doctors</h4>
        <?php if (count($hospital_doctors) > 0): ?>
            <div class="grid">
                <?php foreach ($hospital_doctors as $doctor): ?>
                    <div class="stat">
                        <strong><?php echo htmlspecialchars($doctor['name']); ?></strong>
                        <div class="muted"><?php echo htmlspecialchars($doctor['service_name'] ?? 'General Medicine'); ?></div>
                        <div class="muted"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor['contact_no'] ?? 'N/A'); ?></div>
                        <div class="muted"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($doctor['email'] ?? 'N/A'); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">No doctors are currently listed for Nawodya Hospital.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><i class="fas fa-chart-line"></i> Why Choose Us</h3>
        <div class="grid">
            <div class="stat"><strong>100+</strong><div class="muted">Partner hospitals</div></div>
            <div class="stat"><strong>500+</strong><div class="muted">Specialist doctors</div></div>
            <div class="stat"><strong>24/7</strong><div class="muted">Online support</div></div>
            <div class="stat"><strong>Easy</strong><div class="muted">Appointment booking</div></div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;"><i class="fas fa-file-contract"></i> Policies</h3>
        <p class="muted">Please review our platform policies before using the booking services.</p>
        <a href="terms_conditions.php" class="btn" style="margin-right:0.6rem;"><i class="fas fa-file-contract"></i> Terms & Conditions</a>
        <a href="privacy_notice.php" class="btn"><i class="fas fa-shield-alt"></i> Privacy Notice</a>
    </div>

    <div class="card">
        <a href="main_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>
</body>
</html>
