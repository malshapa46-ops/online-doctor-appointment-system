<?php
require_once 'db.php';

$services = [
    'health-mart' => [
        'icon' => 'fa-cart-shopping',
        'title' => 'Health Mart – Your Healthcare Essentials in One Place',
        'hero' => 'Everything You Need for Better Healthcare',
        'intro' => 'Find essential healthcare and wellness products conveniently through our online platform. Browse trusted healthcare items and make your healthcare journey easier.',
        'button' => 'Browse Products',
        'features_title' => 'Why Use Our Health Mart?',
        'features' => [
            ['icon' => 'fa-shield-heart', 'title' => 'Trusted Healthcare Products', 'text' => 'Choose products from trusted healthcare providers and suppliers.'],
            ['icon' => 'fa-globe', 'title' => 'Easy Online Access', 'text' => 'Browse available healthcare products without visiting multiple stores.'],
            ['icon' => 'fa-basket-shopping', 'title' => 'Convenient Ordering', 'text' => 'Select your required items and submit your order through our online platform.'],
            ['icon' => 'fa-lock', 'title' => 'Secure Service', 'text' => 'Your account and order information are handled securely.']
        ],
        'categories' => ['Personal Care', 'First Aid Essentials', 'Healthcare Equipment', 'Wellness Products', 'Medical Accessories', 'Elderly Care Products'],
        'steps' => ['Browse Products', 'Select Your Product', 'Place Your Order', 'Receive Your Order'],
        'step_text' => ['Explore available healthcare products.', 'View product information and choose the required item.', 'Submit your order through your account.', 'Track the order and receive it through the available delivery service.'],
        'cta' => 'Looking for healthcare essentials? Explore our Health Mart today.'
    ],
    'medicine' => [
        'icon' => 'fa-pills',
        'title' => 'Medicine to Your Doorstep',
        'hero' => 'Get Your Prescribed Medicines Conveniently',
        'intro' => 'Save time by arranging your prescribed medicines through our online healthcare platform and having them delivered through available delivery services.',
        'button' => 'Arrange Medicine Support',
        'features_title' => 'Medicine Service Features',
        'features' => [
            ['icon' => 'fa-file-prescription', 'title' => 'Prescription-Based Ordering', 'text' => 'Submit eligible prescriptions through your patient account.'],
            ['icon' => 'fa-truck-fast', 'title' => 'Convenient Delivery', 'text' => 'Have approved medicine orders delivered to your selected address.'],
            ['icon' => 'fa-location-dot', 'title' => 'Order Tracking', 'text' => 'Check the progress of your medicine order.'],
            ['icon' => 'fa-folder-open', 'title' => 'Digital Records', 'text' => 'Keep your prescriptions and order information organized in your account.']
        ],
        'steps' => ['Consult a Doctor', 'Receive Your Prescription', 'Submit the Prescription', 'Select Delivery', 'Receive Your Medicine'],
        'step_text' => ['Book an appointment with a suitable doctor.', 'The doctor provides the appropriate prescription when clinically suitable.', 'Upload or submit the prescription through the platform.', 'Choose the available delivery option.', 'Receive the approved order at your selected location.'],
        'notice' => 'Medicines are supplied according to applicable prescriptions, professional requirements and platform policies.',
        'cta' => 'Need medicine support? Start with a doctor consultation.'
    ],
    'audio-video' => [
        'icon' => 'fa-video',
        'title' => 'Online Audio & Video Doctor Consultation',
        'hero' => 'Talk to a Doctor From the Comfort of Your Home',
        'intro' => 'Book an online appointment with a qualified healthcare professional and attend your consultation through a secure audio or video session.',
        'button' => 'Find a Doctor',
        'features_title' => 'Find the Right Doctor',
        'feature_intro' => 'Search doctors by specialty, name, availability, consultation type, language, appointment date, and consultation fee.',
        'features' => [
            ['icon' => 'fa-stethoscope', 'title' => 'General Medicine', 'text' => 'For common health concerns and general medical guidance.'],
            ['icon' => 'fa-child', 'title' => 'Pediatrics', 'text' => 'Healthcare consultations relating to children.'],
            ['icon' => 'fa-heart-pulse', 'title' => 'Cardiology', 'text' => 'Consultations relating to heart and cardiovascular health.'],
            ['icon' => 'fa-hand-dots', 'title' => 'Dermatology', 'text' => 'Online consultations for appropriate skin-related concerns.'],
            ['icon' => 'fa-brain', 'title' => 'Psychiatry / Mental Wellness', 'text' => 'Professional support for appropriate mental and emotional health concerns.'],
            ['icon' => 'fa-person-dress', 'title' => 'Gynecology', 'text' => 'Women’s healthcare consultations with qualified professionals.']
        ],
        'steps' => ['Find a Doctor', 'Select an Appointment', 'Confirm Booking', 'Join Consultation', 'Follow-up'],
        'step_text' => ['Search for a doctor according to specialty and availability.', 'Choose a suitable available date and time.', 'Enter your required information and confirm the appointment.', 'At the appointment time, join the secure audio/video consultation.', 'View appropriate consultation documents and follow-up information from your account.'],
        'cta' => 'Need to speak with a doctor? Find a suitable online consultation.'
    ],
    'ayurvedic' => [
        'icon' => 'fa-leaf',
        'title' => 'Ayurvedic Consultations',
        'hero' => 'Connect With Qualified Ayurvedic Practitioners',
        'intro' => 'Discover convenient online access to Ayurvedic healthcare consultations through our appointment booking platform.',
        'button' => 'Explore Practitioners',
        'features_title' => 'Available Services',
        'features' => [
            ['icon' => 'fa-video', 'title' => 'Online Ayurvedic Consultation', 'text' => 'Connect with an available Ayurvedic practitioner online.'],
            ['icon' => 'fa-spa', 'title' => 'Wellness Guidance', 'text' => 'Discuss suitable general wellness goals with a professional.'],
            ['icon' => 'fa-person-walking', 'title' => 'Lifestyle Guidance', 'text' => 'Receive appropriate lifestyle and follow-up guidance.'],
            ['icon' => 'fa-calendar-check', 'title' => 'Follow-up Consultations', 'text' => 'Arrange future appointments through your account.'],
            ['icon' => 'fa-clock', 'title' => 'Appointment Scheduling', 'text' => 'Choose an available appointment slot conveniently.'],
            ['icon' => 'fa-user-md', 'title' => 'Practitioner Profiles', 'text' => 'Review available practitioner information before booking.']
        ],
        'steps' => ['Find a Practitioner', 'Choose a Time', 'Book Online', 'Attend Your Consultation'],
        'step_text' => ['Browse available Ayurvedic practitioners.', 'Select an available appointment slot.', 'Confirm your appointment through your account.', 'Join the online consultation at the scheduled time.'],
        'cta' => 'Explore Ayurvedic practitioners and choose a suitable consultation.'
    ],
    'sexual-wellness' => [
        'icon' => 'fa-venus-mars',
        'title' => 'Sexual Wellness & Confidential Healthcare',
        'hero' => 'Private, Respectful and Professional Healthcare',
        'intro' => 'Access appropriate confidential healthcare support and connect with qualified healthcare professionals in a respectful environment.',
        'button' => 'Find Professional Support',
        'features_title' => 'Our Approach',
        'features' => [
            ['icon' => 'fa-lock', 'title' => 'Privacy', 'text' => 'Personal healthcare information should be handled confidentially and securely.'],
            ['icon' => 'fa-user-doctor', 'title' => 'Professional Care', 'text' => 'Consult qualified healthcare professionals for appropriate concerns.'],
            ['icon' => 'fa-calendar-check', 'title' => 'Easy Appointment Booking', 'text' => 'Choose an available appointment through the online booking system.'],
            ['icon' => 'fa-heart', 'title' => 'Respectful Environment', 'text' => 'Our platform supports a comfortable and non-judgmental healthcare experience.']
        ],
        'categories_title' => 'Available Support',
        'categories' => ['General wellness guidance', 'Reproductive health information', 'Preventive healthcare guidance', 'Professional consultations', 'Follow-up appointments'],
        'steps' => ['Choose Support', 'Select a Professional', 'Book Privately', 'Attend Consultation'],
        'step_text' => ['Review the available general healthcare support options.', 'Choose a qualified professional where available.', 'Select an appropriate appointment date and time.', 'Attend the consultation in a respectful environment.'],
        'cta' => 'Speak with a qualified healthcare professional.'
    ],
    'visa-medical' => [
        'icon' => 'fa-plane-departure',
        'title' => 'Visa Medical Test Appointment',
        'hero' => 'Book Your Medical Examination Easily',
        'intro' => 'Schedule medical examinations required for visa or travel-related purposes through our online appointment booking system.',
        'button' => 'Book Your Medical Test',
        'features_title' => 'Available Services',
        'features' => [
            ['icon' => 'fa-calendar-check', 'title' => 'Medical Examination Booking', 'text' => 'Arrange an appointment for a required visa medical examination.'],
            ['icon' => 'fa-hospital', 'title' => 'Clinic Selection', 'text' => 'Review participating medical center options where available.'],
            ['icon' => 'fa-user-doctor', 'title' => 'Doctor Information', 'text' => 'View relevant clinic and doctor information before booking.'],
            ['icon' => 'fa-bell', 'title' => 'Booking Reminders', 'text' => 'Keep your appointment date and confirmation information organized.']
        ],
        'steps' => ['Select Medical Service', 'Select Location', 'Choose Date & Time', 'Enter Your Details', 'Confirm Appointment'],
        'step_text' => ['Choose the required visa medical examination.', 'Choose an available participating medical center.', 'Select a convenient appointment slot.', 'Provide the required information.', 'Receive your booking confirmation.'],
        'categories_title' => 'Before Your Appointment',
        'categories' => ['Required documents', 'Appointment date', 'Appointment time', 'Clinic location', 'Contact information', 'Preparation instructions'],
        'cta' => 'Prepare your documents and book your medical test appointment.'
    ],
    'marketplace' => [
        'icon' => 'fa-store',
        'title' => 'Health Packages',
        'hero' => 'Take a Proactive Approach to Your Health',
        'intro' => 'Explore healthcare screening and wellness packages designed to make routine health checks easier to arrange.',
        'button' => 'Explore Health Packages',
        'features_title' => 'Package Categories',
        'features' => [
            ['icon' => 'fa-heart-pulse', 'title' => 'Basic Health Screening', 'text' => 'General health screening services for routine checks.'],
            ['icon' => 'fa-briefcase-medical', 'title' => 'Executive Health Package', 'text' => 'A broader health assessment suitable for preventive care.'],
            ['icon' => 'fa-person-dress', 'title' => 'Women’s Health Package', 'text' => 'Selected health screening services for women.'],
            ['icon' => 'fa-person', 'title' => 'Men’s Health Package', 'text' => 'Selected health screening services for men.'],
            ['icon' => 'fa-person-cane', 'title' => 'Senior Health Package', 'text' => 'Healthcare screening options for older adults.']
        ],
        'categories_title' => 'Each Package Can Display',
        'categories' => ['Package name', 'Included tests', 'Available hospital or lab', 'Price', 'Duration', 'Preparation requirements', 'Available dates', 'Book button'],
        'steps' => ['Select Package', 'Choose Location', 'Select Date', 'Enter Details', 'Confirm Booking'],
        'step_text' => ['Choose a suitable health package.', 'Select an available hospital or lab.', 'Choose a convenient date.', 'Enter the required information.', 'Confirm your package booking.'],
        'cta' => 'Explore health packages and plan your routine screening.'
    ],
    'lab-reports' => [
        'icon' => 'fa-microscope',
        'title' => 'Lab Reports at Your Fingertips',
        'hero' => 'Access Your Laboratory Reports Online',
        'intro' => 'View your available laboratory reports through your secure patient account without needing to keep track of physical documents.',
        'button' => 'View My Lab Reports',
        'features_title' => 'Report Access Features',
        'features' => [
            ['icon' => 'fa-globe', 'title' => 'Online Report Access', 'text' => 'View available reports through your dashboard.'],
            ['icon' => 'fa-clock-rotate-left', 'title' => 'Report History', 'text' => 'Keep previous reports organized in one place.'],
            ['icon' => 'fa-lock', 'title' => 'Secure Access', 'text' => 'Only authorized users should access personal medical information.'],
            ['icon' => 'fa-share-nodes', 'title' => 'Easy Sharing', 'text' => 'Where supported, share relevant reports with your healthcare professional.'],
            ['icon' => 'fa-download', 'title' => 'Download / View', 'text' => 'View or download available reports where the service supports it.']
        ],
        'categories_title' => 'My Lab Reports',
        'categories' => ['Full Blood Count — 12 Sep 2026 — Available — View', 'Lipid Profile — 10 Sep 2026 — Available — View', 'Blood Sugar — 08 Sep 2026 — Available — View'],
        'steps' => ['Complete Test', 'Wait for Report', 'Open Your Account', 'View or Download'],
        'step_text' => ['Complete the selected laboratory test.', 'Wait for the provider to make the report available.', 'Sign in and open your report area.', 'View, download, or discuss the report with a professional where supported.'],
        'notice' => 'Laboratory results should be discussed with a qualified healthcare professional. Do not make medical decisions based only on an online report.',
        'cta' => 'Sign in to view your available lab reports.'
    ]
];

$service_key = $_GET['service'] ?? '';
$service = $services[$service_key] ?? null;
if (!$service) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $service ? htmlspecialchars($service['title']) : 'Service Not Found'; ?> - DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary:#2563eb; --primary-dark:#1d4ed8; --navy:#0f172a; --muted:#64748b; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--navy); font-family:'Inter',sans-serif; background:radial-gradient(circle at 10% 8%,rgba(37,99,235,.13),transparent 28rem),linear-gradient(135deg,#f8fbff,#eef4ff); }
        .page-wrap { width:min(1120px,calc(100% - 2rem)); margin:0 auto; padding:2.5rem 0 3.5rem; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.5rem; }
        .brand { color:var(--navy); font-size:1.35rem; font-weight:800; letter-spacing:-.04em; text-decoration:none; }
        .brand span { color:var(--primary); }
        .back-link { color:var(--primary-dark); font-size:.85rem; font-weight:700; text-decoration:none; }
        .hero { position:relative; overflow:hidden; padding:clamp(1.6rem,4vw,3rem); border-radius:30px; color:#fff; background:linear-gradient(125deg,#0b2b5c,#2563eb); box-shadow:0 20px 45px rgba(30,64,175,.2); }
        .hero::after { content:''; position:absolute; width:240px; height:240px; right:-75px; top:-105px; border:28px solid rgba(255,255,255,.1); border-radius:50%; }
        .hero-content { position:relative; z-index:1; max-width:760px; }
        .hero-icon { display:inline-flex; align-items:center; justify-content:center; width:54px; height:54px; margin-bottom:1rem; border-radius:17px; color:var(--primary); background:#fff; font-size:1.5rem; }
        .hero h1 { margin:0 0 .7rem; font-size:clamp(1.8rem,4vw,2.8rem); letter-spacing:-.04em; }
        .hero p { margin:0; color:#dbeafe; line-height:1.7; }
        .content-grid { display:grid; grid-template-columns:minmax(0,1fr) 280px; gap:1.5rem; align-items:start; margin-top:1.5rem; }
        .card { padding:clamp(1.3rem,3vw,2rem); border:1px solid rgba(226,232,240,.9); border-radius:24px; background:rgba(255,255,255,.94); box-shadow:0 12px 35px rgba(15,23,42,.06); }
        .intro { margin:0 0 1.5rem; color:#475569; line-height:1.75; }
        .section { padding:1.35rem 0; border-bottom:1px solid #edf2f7; }
        .section:last-child { border-bottom:0; padding-bottom:0; }
        .section-heading { display:flex; align-items:center; gap:.75rem; margin-bottom:.75rem; }
        .section-icon { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; flex:0 0 38px; color:var(--primary); background:#eff6ff; border-radius:12px; }
        .section h2 { margin:0; font-size:1.05rem; }
        .section p { margin:0 0 .8rem; color:var(--muted); font-size:.9rem; line-height:1.75; }
        .feature-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.8rem; }
        .feature-card { padding:1rem; border:1px solid #dbeafe; border-radius:16px; background:#f8fbff; }
        .feature-card i { color:var(--primary); font-size:1.1rem; }
        .feature-card strong { display:block; margin:.55rem 0 .3rem; font-size:.84rem; }
        .feature-card span { display:block; color:var(--muted); font-size:.78rem; line-height:1.6; }
        .step-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(145px,1fr)); gap:.7rem; padding:0; margin:0; list-style:none; counter-reset:step; }
        .step-list li { position:relative; padding:1rem .8rem .8rem 2.5rem; min-height:84px; border-radius:15px; color:#334155; background:#f8fafc; font-size:.78rem; line-height:1.5; counter-increment:step; }
        .step-list li::before { content:counter(step); position:absolute; left:.75rem; top:.85rem; display:flex; align-items:center; justify-content:center; width:25px; height:25px; color:#fff; background:var(--primary); border-radius:50%; font-weight:800; }
        .category-list { display:flex; flex-wrap:wrap; gap:.55rem; padding:0; margin:0; list-style:none; }
        .category-list li { padding:.5rem .7rem; color:#1e40af; background:#eff6ff; border:1px solid #bfdbfe; border-radius:999px; font-size:.76rem; }
        .side-card { position:sticky; top:1.25rem; }
        .side-card h3 { margin:0 0 1rem; font-size:.95rem; }
        .quick-item { display:flex; gap:.65rem; align-items:flex-start; margin-bottom:.9rem; color:var(--muted); font-size:.8rem; line-height:1.5; }
        .quick-item i { width:18px; color:var(--primary); text-align:center; }
        .notice { margin-top:1.3rem; padding:1rem; color:#1e40af; background:#eff6ff; border:1px solid #bfdbfe; border-radius:16px; font-size:.8rem; line-height:1.6; }
        .actions { display:flex; flex-wrap:wrap; gap:.7rem; margin-top:1.5rem; }
        .btn { display:inline-flex; align-items:center; gap:.55rem; padding:.78rem 1.15rem; color:#fff; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); border-radius:999px; box-shadow:0 8px 18px rgba(37,99,235,.2); font-size:.85rem; font-weight:700; text-decoration:none; transition:transform .2s ease,box-shadow .2s ease; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 12px 24px rgba(37,99,235,.28); }
        .btn.secondary { color:var(--primary-dark); background:#eff6ff; box-shadow:none; border:1px solid #bfdbfe; }
        @media (max-width:760px) { .page-wrap{padding-top:1.2rem}.content-grid{grid-template-columns:1fr}.feature-grid{grid-template-columns:1fr}.side-card{position:static} }
        @media (max-width:480px) { .page-wrap{width:min(100% - 1rem,1120px)}.hero,.card{border-radius:20px}.hero{padding:1.4rem} }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="topbar">
            <a class="brand" href="main_dashboard.php">DOC.<span>lk</span></a>
            <a class="back-link" href="main_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <?php if (!$service): ?>
            <div class="card"><h1>Service Not Found</h1><p class="intro">The requested DOC.lk service could not be found.</p><a href="main_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></div>
        <?php else: ?>
            <header class="hero">
                <div class="hero-content">
                    <div class="hero-icon"><i class="fas <?php echo htmlspecialchars($service['icon']); ?>"></i></div>
                    <h1><?php echo htmlspecialchars($service['title']); ?></h1>
                    <p><?php echo htmlspecialchars($service['hero']); ?></p>
                </div>
            </header>
            <div class="content-grid">
                <main class="card">
                    <p class="intro"><?php echo htmlspecialchars($service['intro']); ?></p>
                    <section class="section">
                        <div class="section-heading"><span class="section-icon"><i class="fas fa-star"></i></span><h2><?php echo htmlspecialchars($service['features_title']); ?></h2></div>
                        <?php if (!empty($service['feature_intro'])): ?><p><?php echo htmlspecialchars($service['feature_intro']); ?></p><?php endif; ?>
                        <div class="feature-grid">
                            <?php foreach ($service['features'] as $feature): ?>
                                <div class="feature-card"><i class="fas <?php echo htmlspecialchars($feature['icon']); ?>"></i><strong><?php echo htmlspecialchars($feature['title']); ?></strong><span><?php echo htmlspecialchars($feature['text']); ?></span></div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php if (!empty($service['categories'])): ?>
                        <section class="section">
                            <div class="section-heading"><span class="section-icon"><i class="fas fa-layer-group"></i></span><h2><?php echo htmlspecialchars($service['categories_title'] ?? 'Categories'); ?></h2></div>
                            <ul class="category-list">
                                <?php foreach ($service['categories'] as $category): ?><li><?php echo htmlspecialchars($category); ?></li><?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                    <section class="section">
                        <div class="section-heading"><span class="section-icon"><i class="fas fa-route"></i></span><h2>How It Works</h2></div>
                        <ol class="step-list">
                            <?php foreach ($service['steps'] as $index => $step): ?>
                                <li><strong><?php echo htmlspecialchars($step); ?></strong><br><?php echo htmlspecialchars($service['step_text'][$index]); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                    <?php if (!empty($service['notice'])): ?><div class="notice"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($service['notice']); ?></div><?php endif; ?>
                    <section class="section">
                        <div class="section-heading"><span class="section-icon"><i class="fas fa-heart"></i></span><h2><?php echo htmlspecialchars($service['cta']); ?></h2></div>
                        <p>Choose the next step below or contact our support team if you need help.</p>
                    </section>
                    <div class="actions">
                        <a href="<?php echo $service_key === 'health-mart' ? 'health_products.php' : 'appointments.php'; ?>" class="btn"><i class="fas <?php echo $service_key === 'health-mart' ? 'fa-cart-shopping' : 'fa-calendar-plus'; ?>"></i> <?php echo htmlspecialchars($service['button']); ?></a>
                        <a href="main_dashboard.php" class="btn secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                        <a href="contact_us.php" class="btn secondary"><i class="fas fa-headset"></i> Contact Support</a>
                    </div>
                </main>
                <aside class="card side-card">
                    <h3><i class="fas fa-compass" style="color:var(--primary);margin-right:.4rem;"></i> Explore DOC.lk</h3>
                    <div class="quick-item"><i class="fas fa-user-md"></i><span>Find doctors and available specializations.</span></div>
                    <div class="quick-item"><i class="fas fa-calendar-check"></i><span>Choose a suitable appointment slot.</span></div>
                    <div class="quick-item"><i class="fas fa-shield-alt"></i><span>Review our privacy and service policies.</span></div>
                    <div class="quick-item"><i class="fas fa-life-ring"></i><span>Contact support for service questions.</span></div>
                    <div class="notice"><strong>Need help?</strong><br>Our support team can guide you through the next step.</div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
