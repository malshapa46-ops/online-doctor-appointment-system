<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - DOC.lk</title>
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
            --green: #16a34a;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--navy);
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 8%, rgba(37, 99, 235, 0.13), transparent 28rem),
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
            letter-spacing: -0.04em;
            text-decoration: none;
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
            width: 250px;
            height: 250px;
            right: -75px;
            top: -105px;
            border: 28px solid rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .hero-content { position: relative; z-index: 1; max-width: 760px; }
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
        .availability {
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
        .availability i { color: #86efac; }
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            align-items: start;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .card {
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(226,232,240,0.9);
            border-radius: 24px;
            padding: clamp(1.3rem, 3vw, 2rem);
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.06);
        }
        .intro {
            margin: 0 0 1.5rem;
            color: #475569;
            line-height: 1.75;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.9rem;
            margin-bottom: 1.6rem;
        }
        .contact-box {
            padding: 1.1rem;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: linear-gradient(135deg, #f8fbff, #eff6ff);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .contact-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(37,99,235,0.1);
        }
        .contact-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            margin-bottom: 0.75rem;
            color: var(--primary);
            background: #fff;
            border-radius: 12px;
        }
        .contact-box strong { display: block; font-size: 0.84rem; }
        .contact-box a, .contact-box span {
            display: block;
            margin-top: 0.3rem;
            color: var(--muted);
            font-size: 0.78rem;
            line-height: 1.55;
            text-decoration: none;
        }
        .contact-box a:hover { color: var(--primary); }
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
            margin: 0 0 0.7rem;
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
            color: var(--green);
            font: 700 0.72rem/1 'Font Awesome 6 Free';
        }
        .side-card { position: sticky; top: 1.25rem; }
        .side-card h3 { margin: 0 0 1rem; font-size: 0.95rem; }
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
        .hours {
            display: grid;
            gap: 0.6rem;
            margin-top: 1.2rem;
        }
        .hours-row {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            color: var(--muted);
            font-size: 0.78rem;
        }
        .hours-row strong { color: #334155; }
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
        .btn.secondary { color: var(--primary-dark); background: #eff6ff; box-shadow: none; border: 1px solid #bfdbfe; }
        @media (max-width: 760px) {
            .page-wrap { padding-top: 1.2rem; }
            .topbar { margin-bottom: 1rem; }
            .content-grid { grid-template-columns: 1fr; }
            .contact-grid { grid-template-columns: 1fr; }
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
                <div class="hero-icon"><i class="fas fa-envelope"></i></div>
                <h1>Contact Us</h1>
                <p>We’re here to help with your appointments, bookings, refunds, and healthcare service questions.</p>
                <span class="availability"><i class="fas fa-circle"></i> Support team available Monday to Saturday</span>
            </div>
        </header>

        <div class="content-grid">
            <main class="card">
                <p class="intro">Whether you need help booking a doctor, checking an appointment, requesting a refund, or updating your account details, our support team is ready to guide you. Choose the most convenient way to reach us below.</p>

                <div class="contact-grid">
                    <div class="contact-box">
                        <span class="contact-icon"><i class="fas fa-envelope"></i></span>
                        <strong>Email Support</strong>
                        <a href="mailto:support@doc.lk">support@doc.lk</a>
                        <span>Best for detailed questions</span>
                    </div>
                    <div class="contact-box">
                        <span class="contact-icon"><i class="fas fa-phone"></i></span>
                        <strong>Call Us</strong>
                        <a href="tel:+94112345678">+94 112 345 678</a>
                        <span>For urgent booking support</span>
                    </div>
                    <div class="contact-box">
                        <span class="contact-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <strong>Visit Us</strong>
                        <span>No. 01, Galle Road, Colombo 07, Sri Lanka</span>
                        <span>Appointments recommended</span>
                    </div>
                </div>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-headset"></i></span><h2>How We Can Help</h2></div>
                    <p>Our support team can assist with the following DOC.lk services:</p>
                    <ul>
                        <li>Creating an account, updating your profile, or resolving login-related questions.</li>
                        <li>Finding a doctor, selecting a service, and completing an appointment booking.</li>
                        <li>Checking appointment dates, times, doctor availability, and booking status.</li>
                        <li>Understanding cancellation, rescheduling, queue number, and refund request processes.</li>
                        <li>Reporting a technical issue or sharing feedback about your DOC.lk experience.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-paper-plane"></i></span><h2>When You Contact Us</h2></div>
                    <p>To help us respond faster, please include the following information where relevant:</p>
                    <ul>
                        <li>Your full name and the email address or phone number connected to your account.</li>
                        <li>Your appointment or booking reference, selected doctor, and appointment date.</li>
                        <li>A clear description of the issue and any error message you received.</li>
                        <li>For refund questions, include the cancellation details and available payment reference.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-clock"></i></span><h2>Response Times</h2></div>
                    <p>We aim to acknowledge support requests as quickly as possible. Response time may vary depending on the type and complexity of your request.</p>
                    <ul>
                        <li>General email questions are usually reviewed during support hours.</li>
                        <li>Appointment and same-day booking issues should be raised by phone when possible.</li>
                        <li>Refund requests may require verification and review before a final response is provided.</li>
                        <li>Messages received outside support hours will be handled on the next available working period.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-comments"></i></span><h2>Before You Contact Support</h2></div>
                    <ul>
                        <li>Check the My Booking section for your latest appointment status and details.</li>
                        <li>Review the FAQ page for common booking, cancellation, and refund answers.</li>
                        <li>Do not share your password or sensitive payment credentials with anyone.</li>
                        <li>For medical emergencies, contact emergency services or visit the nearest emergency department immediately.</li>
                    </ul>
                </section>

                <div class="notice"><i class="fas fa-info-circle"></i> DOC.lk support helps with bookings and platform services. For urgent medical care, please contact the appropriate emergency service or a qualified healthcare professional.</div>
                <div class="actions">
                    <a href="main_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    <a href="faq.php" class="btn secondary"><i class="fas fa-question-circle"></i> View FAQs</a>
                    <a href="mailto:support@doc.lk" class="btn secondary"><i class="fas fa-envelope"></i> Email Us</a>
                </div>
            </main>

            <aside class="card side-card">
                <h3><i class="fas fa-life-ring" style="color:var(--primary); margin-right:0.4rem;"></i> Support Directory</h3>
                <div class="quick-item"><i class="fas fa-calendar-check"></i><span>Booking help and appointment status</span></div>
                <div class="quick-item"><i class="fas fa-undo-alt"></i><span>Cancellation and refund guidance</span></div>
                <div class="quick-item"><i class="fas fa-user-cog"></i><span>Account and profile assistance</span></div>
                <div class="quick-item"><i class="fas fa-bug"></i><span>Technical issue reporting</span></div>

                <h3 style="margin-top:1.4rem;"><i class="fas fa-business-time" style="color:var(--primary); margin-right:0.4rem;"></i> Support Hours</h3>
                <div class="hours">
                    <div class="hours-row"><span>Monday - Friday</span><strong>8:00 AM - 8:00 PM</strong></div>
                    <div class="hours-row"><span>Saturday</span><strong>8:00 AM - 8:00 PM</strong></div>
                    <div class="hours-row"><span>Sunday</span><strong>Limited support</strong></div>
                </div>
                <div class="notice"><strong>Quick tip</strong><br>Include your booking details in every message so our team can assist you faster.</div>
            </aside>
        </div>
    </div>
</body>
</html>
