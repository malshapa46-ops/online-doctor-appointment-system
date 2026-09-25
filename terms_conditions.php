<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --navy: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --soft-blue: #eff6ff;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--navy);
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 8% 8%, rgba(37, 99, 235, 0.12), transparent 28rem),
                linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%);
        }
        .page-wrap {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 2.5rem 0 3.5rem;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .brand {
            color: var(--navy);
            font-size: 1.35rem;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: -0.04em;
        }
        .brand span { color: var(--primary); }
        .back-link {
            color: var(--primary-dark);
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        .hero {
            position: relative;
            overflow: hidden;
            color: #fff;
            padding: clamp(1.6rem, 4vw, 3rem);
            border-radius: 30px;
            background: linear-gradient(125deg, #0b2b5c, #2563eb);
            box-shadow: 0 20px 45px rgba(30, 64, 175, 0.2);
        }
        .hero::after {
            content: '';
            position: absolute;
            width: 240px;
            height: 240px;
            right: -70px;
            top: -95px;
            border: 28px solid rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .hero-content { position: relative; z-index: 1; max-width: 720px; }
        .hero-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            margin-bottom: 1rem;
            border-radius: 17px;
            color: var(--primary);
            background: #fff;
            font-size: 1.5rem;
        }
        .hero h1 {
            margin: 0 0 0.7rem;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            letter-spacing: -0.04em;
        }
        .hero p { margin: 0; color: #dbeafe; line-height: 1.7; }
        .updated {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 1.25rem;
            padding: 0.45rem 0.8rem;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 999px;
            color: #e0ecff;
            font-size: 0.75rem;
        }
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            align-items: start;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .card {
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(226,232,240,0.9);
            border-radius: 24px;
            padding: clamp(1.3rem, 3vw, 2rem);
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.06);
        }
        .intro {
            margin-bottom: 1.5rem;
            color: #475569;
            line-height: 1.75;
        }
        .section {
            padding: 1.35rem 0;
            border-bottom: 1px solid #edf2f7;
        }
        .section:first-of-type { padding-top: 0; }
        .section:last-child { border-bottom: 0; padding-bottom: 0; }
        .section-heading {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.65rem;
        }
        .section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            color: var(--primary);
            background: var(--soft-blue);
            border-radius: 12px;
        }
        .section h2 {
            margin: 0;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }
        .section p {
            margin: 0 0 0.55rem;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.75;
        }
        .section ul {
            display: grid;
            gap: 0.65rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .section li {
            position: relative;
            padding-left: 1.4rem;
            color: #334155;
            font-size: 0.9rem;
            line-height: 1.65;
        }
        .section li::before {
            content: '\f00c';
            position: absolute;
            left: 0;
            top: 0.25rem;
            color: #22c55e;
            font: 700 0.72rem/1 'Font Awesome 6 Free';
        }
        .side-card {
            position: sticky;
            top: 1.25rem;
        }
        .side-card h3 {
            margin: 0 0 1rem;
            font-size: 0.95rem;
        }
        .quick-item {
            display: flex;
            gap: 0.65rem;
            align-items: flex-start;
            margin-bottom: 0.9rem;
            color: var(--muted);
            font-size: 0.8rem;
            line-height: 1.5;
        }
        .quick-item i { width: 18px; color: var(--primary); text-align: center; }
        .notice {
            margin-top: 1.3rem;
            padding: 1rem;
            color: #1e40af;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            font-size: 0.8rem;
            line-height: 1.6;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
            margin-top: 1.5rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.78rem 1.15rem;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 999px;
            box-shadow: 0 8px 18px rgba(37,99,235,0.2);
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(37,99,235,0.28); }
        @media (max-width: 760px) {
            .page-wrap { padding-top: 1.2rem; }
            .topbar { margin-bottom: 1rem; }
            .content-grid { grid-template-columns: 1fr; }
            .side-card { position: static; }
        }
        @media (max-width: 480px) {
            .page-wrap { width: min(100% - 1rem, 1120px); }
            .hero, .card { border-radius: 20px; }
            .hero { padding: 1.4rem; }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="topbar">
            <a class="brand" href="main_dashboard.php">DOC.<span>lk</span></a>
            <a class="back-link" href="main_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <header class="hero">
            <div class="hero-content">
                <div class="hero-icon"><i class="fas fa-file-contract"></i></div>
                <h1>Terms &amp; Conditions</h1>
                <p>These guidelines help us provide a safe, transparent, and reliable doctor-channeling experience for every DOC.lk user.</p>
                <span class="updated"><i class="fas fa-calendar-check"></i> Please review before using our services</span>
            </div>
        </header>

        <div class="content-grid">
            <main class="card">
                <p class="intro">By registering, browsing, or booking an appointment through DOC.lk, you agree to follow the terms below. Please read them carefully and contact our support team if you need clarification.</p>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-handshake"></i></span><h2>1. Acceptance of Terms</h2></div>
                    <p>These terms apply to everyone who uses the DOC.lk website and appointment services.</p>
                    <ul>
                        <li>Using the platform means that you accept these terms and any related service policies.</li>
                        <li>If you do not agree with a term, please do not use the booking services.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-user-check"></i></span><h2>2. Account &amp; Personal Information</h2></div>
                    <p>Accurate information helps doctors and support staff manage your appointments correctly.</p>
                    <ul>
                        <li>Users must provide accurate personal and contact information when registering or booking appointments.</li>
                        <li>You are responsible for keeping your account information up to date and protecting your login details.</li>
                        <li>Please notify DOC.lk if you believe your account has been accessed without permission.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-calendar-check"></i></span><h2>3. Appointments &amp; Doctor Availability</h2></div>
                    <ul>
                        <li>All bookings are subject to doctor availability and the platform’s service policies.</li>
                        <li>An appointment is confirmed only after the booking is successfully recorded by the platform.</li>
                        <li>Doctor schedules, consultation times, and availability may change due to circumstances outside DOC.lk’s control.</li>
                        <li>Users should arrive on time and follow the instructions provided for the selected hospital or doctor.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-clock"></i></span><h2>4. Cancellation &amp; Rescheduling</h2></div>
                    <ul>
                        <li>Appointments should be cancelled or rescheduled at least 24 hours in advance whenever possible.</li>
                        <li>Late cancellations, missed appointments, or repeated no-shows may affect future booking access.</li>
                        <li>Changes depend on the doctor’s availability and the applicable hospital policy.</li>
                        <li>DOC.lk is not responsible for missed appointments caused by user delays, network issues, or late arrival.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-undo-alt"></i></span><h2>5. Payments &amp; Refund Requests</h2></div>
                    <ul>
                        <li>Any applicable booking or service charges will be shown according to the available service information.</li>
                        <li>Any refund requests must be submitted through the refund request section and may require review by the support team.</li>
                        <li>Refund eligibility, processing time, and the final amount may depend on the reason for cancellation and the relevant service policy.</li>
                        <li>Users should keep booking details or payment references when contacting support about a refund.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-shield-alt"></i></span><h2>6. Responsible Use</h2></div>
                    <ul>
                        <li>Users agree to use the platform responsibly and not misuse the appointment system.</li>
                        <li>Creating false accounts, submitting misleading information, or attempting to disrupt the service is not permitted.</li>
                        <li>Users must not use DOC.lk to harass doctors, staff, hospitals, or other users.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-heartbeat"></i></span><h2>7. Service Limitations</h2></div>
                    <ul>
                        <li>DOC.lk helps users connect with healthcare providers and manage bookings; it does not replace professional medical advice.</li>
                        <li>For emergencies, contact the appropriate emergency service or visit the nearest emergency department.</li>
                        <li>Service interruptions may occur because of maintenance, technical issues, or factors beyond our reasonable control.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-sync-alt"></i></span><h2>8. Updates to These Terms</h2></div>
                    <p>DOC.lk may update these terms when services, policies, or legal requirements change. The latest version will be displayed on this page, and continued use of the platform means you accept the updated terms.</p>
                </section>

                <div class="notice"><i class="fas fa-info-circle"></i> If you have a question about a booking, cancellation, or refund, please contact the DOC.lk support team with your appointment details.</div>
                <div class="actions">
                    <a href="main_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    <a href="contact_us.php" class="btn"><i class="fas fa-headset"></i> Contact Support</a>
                </div>
            </main>

            <aside class="card side-card">
                <h3><i class="fas fa-list-ul" style="color:var(--primary); margin-right:0.4rem;"></i> Quick Overview</h3>
                <div class="quick-item"><i class="fas fa-user-check"></i><span>Keep your account information accurate.</span></div>
                <div class="quick-item"><i class="fas fa-calendar-check"></i><span>Confirm appointments and doctor availability.</span></div>
                <div class="quick-item"><i class="fas fa-clock"></i><span>Cancel or reschedule as early as possible.</span></div>
                <div class="quick-item"><i class="fas fa-undo-alt"></i><span>Submit refund requests through the correct section.</span></div>
                <div class="quick-item"><i class="fas fa-shield-alt"></i><span>Use the platform respectfully and responsibly.</span></div>
                <div class="notice"><strong>Need help?</strong><br>Our support team is ready to assist you with your DOC.lk services.</div>
            </aside>
        </div>
    </div>
</body>
</html>
