<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Notice - DOC.lk</title>
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
                radial-gradient(circle at 88% 5%, rgba(37, 99, 235, 0.13), transparent 28rem),
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
            background: rgba(255,255,255,0.94);
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
        .privacy-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }
        .summary-item {
            padding: 1rem;
            border: 1px solid #dbeafe;
            border-radius: 17px;
            background: linear-gradient(135deg, #f8fbff, #eff6ff);
        }
        .summary-item i { color: var(--primary); font-size: 1.15rem; }
        .summary-item strong {
            display: block;
            margin-top: 0.55rem;
            font-size: 0.8rem;
        }
        .summary-item span {
            display: block;
            margin-top: 0.25rem;
            color: var(--muted);
            font-size: 0.72rem;
            line-height: 1.5;
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
        .data-flow {
            display: grid;
            gap: 0.6rem;
            margin: 1.1rem 0 0;
        }
        .flow-step {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.7rem 0.8rem;
            border-radius: 13px;
            color: #334155;
            background: #f8fafc;
            font-size: 0.78rem;
        }
        .flow-step i { color: var(--primary); width: 18px; text-align: center; }
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
            .privacy-summary { grid-template-columns: 1fr; }
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
                <div class="hero-icon"><i class="fas fa-shield-alt"></i></div>
                <h1>Privacy Notice</h1>
                <p>Your privacy and personal information are important to us. This notice explains what information DOC.lk handles and how we protect it.</p>
                <span class="updated"><i class="fas fa-lock"></i> Privacy-first healthcare booking</span>
            </div>
        </header>

        <div class="content-grid">
            <main class="card">
                <p class="intro">This Privacy Notice applies when you create an account, browse DOC.lk, book an appointment, or contact our support team. We collect and use information only for legitimate healthcare booking and service-support purposes.</p>

                <div class="privacy-summary">
                    <div class="summary-item"><i class="fas fa-database"></i><strong>Collect</strong><span>Only information needed for our services.</span></div>
                    <div class="summary-item"><i class="fas fa-cogs"></i><strong>Use</strong><span>To manage bookings and improve support.</span></div>
                    <div class="summary-item"><i class="fas fa-user-shield"></i><strong>Protect</strong><span>With appropriate security safeguards.</span></div>
                </div>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-info-circle"></i></span><h2>1. Information We Collect</h2></div>
                    <p>We collect information that helps us create your account and provide doctor-channeling services.</p>
                    <ul>
                        <li>We collect your name, email, phone number, and appointment details only to provide healthcare booking services.</li>
                        <li>Account information may include your username, contact details, and basic profile information you submit.</li>
                        <li>Booking information may include the selected doctor, service, hospital, date, time, and appointment status.</li>
                        <li>Messages or details you send to our support team may be retained to resolve your request.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-bullseye"></i></span><h2>2. How We Use Your Information</h2></div>
                    <ul>
                        <li>Your information is used to manage appointments, contact you about bookings, and improve service quality.</li>
                        <li>We use contact details to send important booking updates, reminders, cancellations, or support responses.</li>
                        <li>We may use service information to understand usage trends and improve the DOC.lk experience.</li>
                        <li>We may use information to prevent misuse, investigate technical issues, and maintain platform security.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-share-alt"></i></span><h2>3. Sharing &amp; Disclosure</h2></div>
                    <p>We aim to keep your information private and share it only when there is a valid service or legal reason.</p>
                    <ul>
                        <li>We do not share your personal data with third parties without your consent, except where required by law.</li>
                        <li>Relevant booking details may be made available to the selected doctor, hospital, or service provider to support your appointment.</li>
                        <li>Service providers who help operate the platform may process limited information only for the service they provide.</li>
                        <li>We may disclose information when necessary to comply with a legal obligation or protect users and the platform.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-lock"></i></span><h2>4. Data Security</h2></div>
                    <ul>
                        <li>Your data is stored securely and protected using appropriate technical and organizational safeguards.</li>
                        <li>Access to account and booking information should be limited to authorized people who need it for service delivery.</li>
                        <li>Users should keep passwords confidential and log out from shared or public devices.</li>
                        <li>No online service can guarantee absolute security, but we work to identify and reduce reasonable risks.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-clock"></i></span><h2>5. Data Retention</h2></div>
                    <p>We keep information for as long as it is reasonably required to provide services, maintain records, resolve disputes, and meet legal or operational requirements.</p>
                    <ul>
                        <li>Appointment and support records may be retained for service history and customer support purposes.</li>
                        <li>When information is no longer required, it may be securely deleted, anonymized, or archived according to applicable requirements.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-user-cog"></i></span><h2>6. Your Choices &amp; Rights</h2></div>
                    <ul>
                        <li>You may contact our support team if you want to review or update your personal information.</li>
                        <li>You can request correction of inaccurate account or contact details.</li>
                        <li>You may ask questions about how your information is used or request assistance with an account concern.</li>
                        <li>Some information may need to be retained where required for legal, security, or legitimate business purposes.</li>
                    </ul>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-cookie-bite"></i></span><h2>7. Cookies &amp; Technical Data</h2></div>
                    <p>DOC.lk may use essential cookies or similar technical information to keep the website functional, remember preferences, improve performance, and help protect accounts. You can manage cookies through your browser settings, although disabling essential cookies may affect some features.</p>
                </section>

                <section class="section">
                    <div class="section-heading"><span class="section-icon"><i class="fas fa-sync-alt"></i></span><h2>8. Changes to This Notice</h2></div>
                    <p>We may update this Privacy Notice when our services, security practices, or legal requirements change. The latest version will be displayed on this page. We encourage you to review it periodically.</p>
                </section>

                <div class="notice"><i class="fas fa-info-circle"></i> If you have a privacy question or want to update your information, please contact the DOC.lk support team with enough details for us to assist you.</div>
                <div class="actions">
                    <a href="main_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    <a href="contact_us.php" class="btn"><i class="fas fa-headset"></i> Contact Support</a>
                </div>
            </main>

            <aside class="card side-card">
                <h3><i class="fas fa-user-shield" style="color:var(--primary); margin-right:0.4rem;"></i> Your Privacy Matters</h3>
                <div class="quick-item"><i class="fas fa-check-circle"></i><span>We collect information for clear service purposes.</span></div>
                <div class="quick-item"><i class="fas fa-check-circle"></i><span>Your booking details help manage appointments.</span></div>
                <div class="quick-item"><i class="fas fa-check-circle"></i><span>We limit sharing to service and legal needs.</span></div>
                <div class="quick-item"><i class="fas fa-check-circle"></i><span>You can ask to review or update your information.</span></div>

                <h3 style="margin-top:1.4rem;"><i class="fas fa-route" style="color:var(--primary); margin-right:0.4rem;"></i> Information Flow</h3>
                <div class="data-flow">
                    <div class="flow-step"><i class="fas fa-user-plus"></i> You provide details</div>
                    <div class="flow-step"><i class="fas fa-calendar-check"></i> DOC.lk manages booking</div>
                    <div class="flow-step"><i class="fas fa-user-md"></i> Provider supports appointment</div>
                    <div class="flow-step"><i class="fas fa-shield-alt"></i> Data remains protected</div>
                </div>
                <div class="notice"><strong>Need assistance?</strong><br>Contact support to ask about your account or personal information.</div>
            </aside>
        </div>
    </div>
</body>
</html>
