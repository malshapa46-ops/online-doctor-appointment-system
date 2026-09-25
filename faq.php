<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --navy: #0f172a;
            --muted: #64748b;
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
                radial-gradient(circle at 88% 7%, rgba(37, 99, 235, 0.13), transparent 28rem),
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
        .hero-badge {
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
            margin: 0 0 1.5rem;
            color: #475569;
            line-height: 1.75;
        }
        .faq-list {
            display: grid;
            gap: 0.8rem;
        }
        details {
            overflow: hidden;
            border: 1px solid #dbeafe;
            border-radius: 17px;
            background: #fff;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        details[open] {
            border-color: #93c5fd;
            box-shadow: 0 8px 22px rgba(37,99,235,0.08);
        }
        summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.15rem;
            cursor: pointer;
            list-style: none;
            color: #172554;
            font-size: 0.9rem;
            font-weight: 700;
        }
        summary::-webkit-details-marker { display: none; }
        summary::after {
            content: '\f078';
            color: var(--primary);
            font: 700 0.75rem/1 'Font Awesome 6 Free';
            transition: transform 0.2s ease;
        }
        details[open] summary::after { transform: rotate(180deg); }
        .answer {
            padding: 0 1.15rem 1.05rem;
            color: var(--muted);
            font-size: 0.85rem;
            line-height: 1.75;
        }
        .category {
            margin: 1.8rem 0 0.8rem;
            color: var(--primary-dark);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
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
        .support-box {
            margin-top: 1.4rem;
            padding: 1rem;
            border-radius: 17px;
            color: #1e40af;
            background: var(--soft-blue);
            border: 1px solid #bfdbfe;
            font-size: 0.8rem;
            line-height: 1.6;
        }
        .support-box strong { display: block; margin-bottom: 0.25rem; }
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
                <div class="hero-icon"><i class="fas fa-question-circle"></i></div>
                <h1>Frequently Asked Questions</h1>
                <p>Find quick answers about booking appointments, cancellations, refunds, accounts, and using DOC.lk services.</p>
                <span class="hero-badge"><i class="fas fa-lightbulb"></i> Helpful answers for a smoother booking experience</span>
            </div>
        </header>

        <div class="content-grid">
            <main class="card">
                <p class="intro">Browse the questions below to find guidance about common DOC.lk tasks. If your question is not listed, our support team will be happy to assist you.</p>

                <div class="category"><i class="fas fa-calendar-check"></i> Appointments &amp; Booking</div>
                <div class="faq-list">
                    <details open>
                        <summary>How do I book an appointment?</summary>
                        <div class="answer">Sign in to your DOC.lk account, open the dashboard, choose a doctor or service, select your preferred date and time, and complete the booking steps. Review the details before confirming the appointment.</div>
                    </details>
                    <details>
                        <summary>How do I find the right doctor?</summary>
                        <div class="answer">Use the “Channel Your Doctor” search area to select a doctor or filter by available specialization. Review the displayed service and doctor details before selecting a suitable appointment.</div>
                    </details>
                    <details>
                        <summary>How do I know if my appointment is confirmed?</summary>
                        <div class="answer">Check the My Booking section after completing the booking. The appointment record will show the selected doctor, service, date, time, and current status when available.</div>
                    </details>
                    <details>
                        <summary>Can I book more than one appointment?</summary>
                        <div class="answer">You may make additional bookings when suitable slots are available. Please check your existing appointments first and avoid creating duplicate bookings for the same doctor and time.</div>
                    </details>
                </div>

                <div class="category"><i class="fas fa-clock"></i> Changes &amp; Cancellations</div>
                <div class="faq-list">
                    <details>
                        <summary>Can I cancel or reschedule?</summary>
                        <div class="answer">Yes. You can manage eligible appointments from the My Booking section. Appointments should be cancelled or rescheduled at least 24 hours in advance whenever possible, and changes depend on doctor availability and service policies.</div>
                    </details>
                    <details>
                        <summary>What if I miss my appointment?</summary>
                        <div class="answer">Please contact support as soon as possible so we can help you understand the next available option. DOC.lk may not be responsible for missed appointments caused by late arrival, user delays, or network issues.</div>
                    </details>
                    <details>
                        <summary>Can I change the doctor after booking?</summary>
                        <div class="answer">A doctor change may require cancelling the existing appointment and creating a new booking, subject to available slots and the applicable service policy. Contact support if you are unsure what to do.</div>
                    </details>
                </div>

                <div class="category"><i class="fas fa-undo-alt"></i> Refunds &amp; Payments</div>
                <div class="faq-list">
                    <details>
                        <summary>How do I get a refund?</summary>
                        <div class="answer">Use the Refund Request option after a cancelled appointment and provide the relevant booking details. Requests may require review by the support team before a decision is made.</div>
                    </details>
                    <details>
                        <summary>How long does a refund take?</summary>
                        <div class="answer">Processing time depends on the reason for cancellation, the applicable service policy, and any payment verification required. Support will provide guidance after reviewing your request.</div>
                    </details>
                    <details>
                        <summary>What information should I include in a refund request?</summary>
                        <div class="answer">Include your name, appointment reference, selected doctor, appointment date, cancellation details, and any available payment reference. Do not send passwords or sensitive account credentials.</div>
                    </details>
                </div>

                <div class="category"><i class="fas fa-user-cog"></i> Account &amp; Support</div>
                <div class="faq-list">
                    <details>
                        <summary>What should I do if I cannot log in?</summary>
                        <div class="answer">Check that your username or email and password are entered correctly. If the issue continues, contact support with your registered contact details. Never share your password with anyone.</div>
                    </details>
                    <details>
                        <summary>How can I update my personal information?</summary>
                        <div class="answer">Contact the DOC.lk support team if you need to review or update your personal information. Include the account detail that needs to be corrected and enough information to verify your request.</div>
                    </details>
                    <details>
                        <summary>How do I contact DOC.lk support?</summary>
                        <div class="answer">Use the Contact Us page to email support@doc.lk or call +94 112 345 678. For faster assistance, include your booking details and a clear description of the issue.</div>
                    </details>
                </div>

                <div class="support-box"><i class="fas fa-info-circle"></i> Still need help? Our support team can assist with booking, appointment status, cancellations, refunds, and account questions.</div>
                <div class="actions">
                    <a href="main_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    <a href="contact_us.php" class="btn secondary"><i class="fas fa-headset"></i> Contact Support</a>
                </div>
            </main>

            <aside class="card side-card">
                <h3><i class="fas fa-compass" style="color:var(--primary); margin-right:0.4rem;"></i> Quick Guide</h3>
                <div class="quick-item"><i class="fas fa-search"></i><span>Search for a doctor or specialization.</span></div>
                <div class="quick-item"><i class="fas fa-calendar-plus"></i><span>Select an available appointment slot.</span></div>
                <div class="quick-item"><i class="fas fa-list"></i><span>Review your booking in My Booking.</span></div>
                <div class="quick-item"><i class="fas fa-life-ring"></i><span>Contact support when you need help.</span></div>

                <h3 style="margin-top:1.4rem;"><i class="fas fa-layer-group" style="color:var(--primary); margin-right:0.4rem;"></i> Popular Topics</h3>
                <div class="quick-item"><i class="fas fa-calendar-check"></i><span>Appointments and doctor availability</span></div>
                <div class="quick-item"><i class="fas fa-undo-alt"></i><span>Cancellations and refunds</span></div>
                <div class="quick-item"><i class="fas fa-user-shield"></i><span>Account and privacy support</span></div>
                <div class="support-box"><strong>Tip</strong>Open a question above to read the complete answer.</div>
            </aside>
        </div>
    </div>
</body>
</html>
