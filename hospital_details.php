<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nawodya Hospital – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hospital-details {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
        }
        .hospital-details .header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .hospital-details .header i {
            font-size: 3rem;
            color: #2563eb;
            background: #eff6ff;
            padding: 0.8rem;
            border-radius: 60px;
        }
        .hospital-details .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .hospital-details .details-list {
            list-style: none;
            padding: 0;
        }
        .hospital-details .details-list li {
            padding: 0.7rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: #1e293b;
            font-size: 0.95rem;
        }
        .hospital-details .details-list li:last-child { border-bottom: none; }
        .hospital-details .details-list li i {
            color: #2563eb;
            width: 24px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.6rem 1.5rem;
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            color: #fff;
            border-radius: 60px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37,99,235,0.3);
        }
    </style>
</head>
<body>
    <nav class="navbar-modern" style="background:rgba(255,255,255,0.92);backdrop-filter:blur(12px);padding:0.6rem 2rem;box-shadow:0 2px 20px rgba(0,0,0,0.04);border-bottom:1px solid rgba(226,232,240,0.6);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.8rem 1.5rem;position:sticky;top:0;z-index:1000;">
        <div class="brand" style="font-size:1.7rem;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:0.5rem;">
            <i class="fas fa-stethoscope" style="color:#2563eb;"></i> DOC.<span style="color:#2563eb;">lk</span>
        </div>
        <div style="display:flex;align-items:center;gap:0.8rem;">
            <a href="main_dashboard.php" style="color:#475569;text-decoration:none;font-weight:500;">← Back to Dashboard</a>
            <a href="logout.php" class="btn btn-danger" style="background:#ef4444;color:#fff;padding:0.3rem 1rem;border-radius:60px;text-decoration:none;font-weight:600;">Logout</a>
        </div>
    </nav>

    <div class="container" style="padding-top:2rem;">
        <div class="hospital-details">
            <div class="header">
                <i class="fas fa-hospital"></i>
                <h1>Nawodya Hospital</h1>
            </div>
            <p style="color:#475569;margin-bottom:1.5rem;">Your trusted healthcare partner in Sri Lanka.</p>
            <ul class="details-list">
                <li><i class="fas fa-map-marker-alt"></i> No. 01, Galle Road, Colombo 07, Sri Lanka</li>
                <li><i class="fas fa-phone"></i> +94 112 345 678</li>
                <li><i class="fas fa-envelope"></i> info@nawodyahospital.lk</li>
                <li><i class="fas fa-globe"></i> www.nawodyahospital.lk</li>
                <li><i class="fas fa-calendar-alt"></i> Established: 2010</li>
                <li><i class="fas fa-stethoscope"></i> Specialties: Cardiology, Pediatrics, Dermatology, Neurology, Orthopedics</li>
                <li><i class="fas fa-clock"></i> Visiting Hours: 8:00 AM - 8:00 PM (Mon-Sat)</li>
                <li><i class="fas fa-ambulance"></i> Emergency: 24/7 Emergency Services Available</li>
                <li><i class="fas fa-flask"></i> Modern Diagnostic Facilities</li>
                <li><i class="fas fa-prescription-bottle"></i> In-house Pharmacy</li>
                <li><i class="fas fa-bed"></i> 50+ Patient Beds</li>
                <li><i class="fas fa-user-md"></i> 25+ Specialist Doctors</li>
            </ul>
            <a href="main_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>