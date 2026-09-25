<?php
require_once 'db.php';

// Check if user is already logged in
$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['role'] === 'patient';

// ====== LOGIN HANDLING ======
$login_error = '';
$login_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $login_error = 'Please enter your username and password.';
    } else {
        $login_value = $username;
        $stmt = $conn->prepare("SELECT user_id, username, password, full_name, role FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login_value, $login_value);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($password === $row['password']) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role'];
                header('Location: main_dashboard.php');
                exit;
            } else {
                $login_error = 'Incorrect password. Please try again.';
            }
        } else {
            $login_error = 'This username does not exist.';
        }
        $stmt->close();
    }
}

// ====== REGISTER HANDLING ======
$register_error = '';
$register_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_action'])) {
    $username = trim($_POST['reg_username']);
    $password = trim($_POST['reg_password']);
    $confirm_password = trim($_POST['reg_confirm_password']);
    $full_name = trim($_POST['reg_full_name']);
    $email = trim($_POST['reg_email']);
    $contact = trim($_POST['reg_contact']);

    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name) || empty($email)) {
        $register_error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $register_error = 'Passwords do not match.';
    } elseif (strlen($password) < 4) {
        $register_error = 'Password must be at least 4 characters.';
    } else {
        $check = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $register_error = 'This username or email is already registered.';
        } else {
            $stmt = $conn->prepare("INSERT INTO user (username, password, full_name, email, contact_no, role) VALUES (?, ?, ?, ?, ?, 'patient')");
            $stmt->bind_param("sssss", $username, $password, $full_name, $email, $contact);
            if ($stmt->execute()) {
                $register_success = 'Registration successful! You can now login.';
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['role'] = 'patient';
                header('Location: main_dashboard.php');
                exit;
            } else {
                $register_error = 'Failed to register. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}

// ====== LOGOUT HANDLING ======
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// ====== SEARCH LOGIC (only if logged in) ======
$search_results = [];
$search_triggered = false;
$selected_doctor_id = '';
$hospital = '';
$specialization = '';
$search_date = '';

if ($is_logged_in) {
    $all_doctors = [];
    $doc_res = $conn->query("SELECT d.doctor_id, d.name, s.service_name FROM doctor d LEFT JOIN service s ON d.service_id = s.service_id ORDER BY d.name");
    while ($row = $doc_res->fetch_assoc()) {
        $all_doctors[] = $row;
    }

    $all_specializations = [];
    $spec_res = $conn->query("SELECT DISTINCT service_name FROM service ORDER BY service_name");
    while ($row = $spec_res->fetch_assoc()) {
        $all_specializations[] = $row['service_name'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_doctor'])) {
        $search_triggered = true;
        $selected_doctor_id = $_GET['doctor_id'] ?? '';
        $hospital = trim($_GET['hospital'] ?? '');
        $specialization = trim($_GET['specialization'] ?? '');
        $search_date = $_GET['search_date'] ?? '';

        $sql = "SELECT d.*, s.service_name 
                FROM doctor d 
                LEFT JOIN service s ON d.service_id = s.service_id 
                WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($selected_doctor_id)) {
            $sql .= " AND d.doctor_id = ?";
            $params[] = $selected_doctor_id;
            $types .= "i";
        }
        if (!empty($specialization)) {
            $sql .= " AND s.service_name LIKE ?";
            $params[] = "%$specialization%";
            $types .= "s";
        }
        if (!empty($hospital)) {
            $sql .= " AND (d.name LIKE ? OR d.contact_no LIKE ?)";
            $params[] = "%$hospital%";
            $params[] = "%$hospital%";
            $types .= "ss";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
        $stmt->close();
    }
}

// ====== APPOINTMENTS (only if logged in) ======
$appointments = [];
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT a.id AS appointment_id, a.date AS app_date, a.time AS app_time, a.status,
               d.name AS doctor_name, d.contact_no AS doctor_contact,
               s.service_name
        FROM appointment a
        JOIN doctor d ON a.doctor_id = d.doctor_id
        LEFT JOIN service s ON d.service_id = s.service_id
        WHERE a.user_id = ?
        ORDER BY a.date DESC, a.time DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();
}

// ====== FETCH NAWODYA HOSPITAL DOCTORS FOR ABOUT US ======
$nawodya_doctors = [];
$stmt = $conn->prepare("
    SELECT d.*, s.service_name 
    FROM doctor d 
    LEFT JOIN service s ON d.service_id = s.service_id 
    WHERE d.hospital = 'Nawodya Hospital' 
    ORDER BY d.name
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $nawodya_doctors[] = $row;
}
$stmt->close();

// Build custom HTML for Nawodya Hospital section
$nawodya_html = '';
$nawodya_html .= '<div class="nawodya-hospital-section" style="margin-top: 2rem; border-top: 2px solid #e2e8f0; padding-top: 2rem;">';
$nawodya_html .= '<div class="hospital-header" style="background: linear-gradient(135deg, #0b2b5c, #1a4a8a); color: white; padding: 2rem; border-radius: 28px; margin-bottom: 2rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;">';
$nawodya_html .= '<div><h3 style="font-size: 1.8rem; font-weight: 700; margin: 0;"><i class="fas fa-hospital" style="margin-right: 0.8rem;"></i> Nawodya Hospital</h3>';
$nawodya_html .= '<p style="opacity: 0.85; margin: 0.2rem 0 0;"><i class="fas fa-map-marker-alt"></i> No. 01, Galle Road, Colombo 07, Sri Lanka</p>';
$nawodya_html .= '<p style="opacity: 0.85;"><i class="fas fa-phone"></i> +94 112 345 678 | <i class="fas fa-envelope"></i> info@nawodyahospital.lk</p>';
$nawodya_html .= '</div>';
$nawodya_html .= '<div class="badge" style="background: rgba(255,255,255,0.15); padding: 0.5rem 1.5rem; border-radius: 60px; border: 1px solid rgba(255,255,255,0.2); font-weight: 600; backdrop-filter: blur(4px);">';
$nawodya_html .= '<i class="fas fa-user-md"></i> ' . count($nawodya_doctors) . ' Doctors Available</div>';
$nawodya_html .= '</div>';

if (count($nawodya_doctors) > 0) {
    $nawodya_html .= '<h4 style="margin-bottom: 0.5rem; color: #0f172a;"><i class="fas fa-user-md" style="color:#2563eb;"></i> Our Specialist Doctors</h4>';
    $nawodya_html .= '<p style="color:#64748b; margin-bottom: 1.5rem;">Book an appointment with our experienced doctors</p>';
    $nawodya_html .= '<div class="doctor-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">';
    foreach ($nawodya_doctors as $doc) {
        $nawodya_html .= '<div class="doctor-card" style="background: #ffffff; border-radius: 24px; padding: 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.02); transition: 0.25s;">';
        $nawodya_html .= '<div class="doc-name" style="font-size: 1.2rem; font-weight: 700; color: #0f172a;"><i class="fas fa-user-md" style="color:#2563eb; margin-right:6px;"></i>' . htmlspecialchars($doc['name']) . '</div>';
        $nawodya_html .= '<div class="doc-specialty" style="color: #2563eb; font-size: 0.9rem; font-weight: 600; margin: 0.2rem 0 0.5rem;">' . htmlspecialchars($doc['service_name'] ?? 'General Medicine') . '</div>';
        $nawodya_html .= '<div class="doc-details" style="color: #64748b; font-size: 0.85rem; margin: 0.2rem 0;"><i class="fas fa-phone" style="color: #2563eb; width:20px;"></i> ' . htmlspecialchars($doc['contact_no'] ?? 'N/A') . '</div>';
        $nawodya_html .= '<div class="doc-details" style="color: #64748b; font-size: 0.85rem; margin: 0.2rem 0;"><i class="fas fa-envelope" style="color: #2563eb; width:20px;"></i> ' . htmlspecialchars($doc['email'] ?? 'N/A') . '</div>';
        $nawodya_html .= '<a href="../appointments.php?doctor_id=' . $doc['doctor_id'] . '" class="btn-book" style="display: inline-block; margin-top: 1rem; padding: 0.5rem 1.5rem; background: linear-gradient(135deg, #2563eb, #1a3a7a); color: #fff; border-radius: 60px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.2s; border: none; cursor: pointer;">';
        $nawodya_html .= '<i class="fas fa-calendar-plus"></i> Book Appointment</a>';
        $nawodya_html .= '</div>';
    }
    $nawodya_html .= '</div>';
} else {
    $nawodya_html .= '<div style="text-align:center; padding: 2rem 0; color: #94a3b8;"><i class="fas fa-user-md" style="font-size: 2rem; display:block; margin-bottom:0.5rem; color:#cbd5e1;"></i><p>No doctors available at Nawodya Hospital at the moment.</p></div>';
}
$nawodya_html .= '</div>';

$about_html = '<div style="margin-top: 1rem; display: grid; gap: 1rem;">';
$about_html .= '<div style="background: linear-gradient(135deg, #eff6ff, #f8fbff); border: 1px solid #dbeafe; border-radius: 20px; padding: 1.2rem;">';
$about_html .= '<h4 style="margin: 0 0 0.6rem; color: #0f172a;"><i class="fas fa-hospital" style="color:#2563eb; margin-right:0.5rem;"></i> About DOC.lk</h4>';
$about_html .= '<p style="margin: 0; color: #475569; line-height: 1.7;">DOC.lk is a trusted digital healthcare platform that helps patients book appointments with specialist doctors quickly, safely, and conveniently from anywhere in Sri Lanka.</p>';
$about_html .= '</div>';
$about_html .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.8rem;">';
$about_html .= '<div style="padding: 0.9rem; border-radius: 16px; background: #ffffff; border: 1px solid #e2e8f0;"><strong>24/7</strong><div style="color:#64748b; font-size:0.9rem;">Online support</div></div>';
$about_html .= '<div style="padding: 0.9rem; border-radius: 16px; background: #ffffff; border: 1px solid #e2e8f0;"><strong>500+</strong><div style="color:#64748b; font-size:0.9rem;">Specialist doctors</div></div>';
$about_html .= '<div style="padding: 0.9rem; border-radius: 16px; background: #ffffff; border: 1px solid #e2e8f0;"><strong>100+</strong><div style="color:#64748b; font-size:0.9rem;">Partner hospitals</div></div>';
$about_html .= '</div></div>';

$terms_html = '<div style="margin-top: 1rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.1rem;">';
$terms_html .= '<h4 style="margin: 0 0 0.6rem; color: #0f172a;"><i class="fas fa-file-contract" style="color:#2563eb; margin-right:0.5rem;"></i> Terms & Conditions</h4>';
$terms_html .= '<ul style="margin: 0; padding-left: 1rem; color: #475569; line-height: 1.8;">';
$terms_html .= '<li>Users must provide accurate personal and contact information.</li>';
$terms_html .= '<li>Appointments should be cancelled or rescheduled at least 24 hours in advance when possible.</li>';
$terms_html .= '<li>DOC.lk is not responsible for missed appointments caused by user delays or network issues.</li>';
$terms_html .= '</ul></div>';

$privacy_html = '<div style="margin-top: 1rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.1rem;">';
$privacy_html .= '<h4 style="margin: 0 0 0.6rem; color: #0f172a;"><i class="fas fa-shield-alt" style="color:#2563eb; margin-right:0.5rem;"></i> Privacy Notice</h4>';
$privacy_html .= '<p style="margin: 0; color: #475569; line-height: 1.7;">We use your personal information only to manage appointments, improve service quality, and communicate important updates. Your details are protected and never shared without consent.</p>';
$privacy_html .= '</div>';

$contact_html = '<div style="margin-top: 1rem; display: grid; gap: 0.8rem;">';
$contact_html .= '<div style="background: #f8fbff; border: 1px solid #dbeafe; border-radius: 18px; padding: 1rem;">';
$contact_html .= '<div style="font-weight: 700; color: #0f172a; margin-bottom: 0.3rem;"><i class="fas fa-envelope" style="color:#2563eb; margin-right:0.4rem;"></i> Email Support</div>';
$contact_html .= '<div style="color:#475569;">support@doc.lk</div></div>';
$contact_html .= '<div style="background: #f8fbff; border: 1px solid #dbeafe; border-radius: 18px; padding: 1rem;">';
$contact_html .= '<div style="font-weight: 700; color: #0f172a; margin-bottom: 0.3rem;"><i class="fas fa-phone" style="color:#2563eb; margin-right:0.4rem;"></i> Call Us</div>';
$contact_html .= '<div style="color:#475569;">+94 112 345 678</div></div>';
$contact_html .= '<div style="background: #f8fbff; border: 1px solid #dbeafe; border-radius: 18px; padding: 1rem;">';
$contact_html .= '<div style="font-weight: 700; color: #0f172a; margin-bottom: 0.3rem;"><i class="fas fa-map-marker-alt" style="color:#2563eb; margin-right:0.4rem;"></i> Visit Us</div>';
$contact_html .= '<div style="color:#475569;">No. 01, Galle Road, Colombo 07, Sri Lanka</div></div></div>';

$faq_html = '<div style="margin-top: 1rem; display: grid; gap: 0.8rem;">';
$faq_html .= '<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 0.9rem;"><strong>How do I book an appointment?</strong><div style="color:#64748b; margin-top:0.3rem;">Sign in, choose a doctor, and select your preferred date and time.</div></div>';
$faq_html .= '<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 0.9rem;"><strong>Can I cancel or reschedule?</strong><div style="color:#64748b; margin-top:0.3rem;">Yes. You can manage this from the My Booking section.</div></div>';
$faq_html .= '<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 0.9rem;"><strong>How do I get a refund?</strong><div style="color:#64748b; margin-top:0.3rem;">Use the Refund Request option after a cancelled appointment.</div></div>';
$faq_html .= '</div>';

// ====== ONGOING NUMBER CONTENT ======
$upcoming_queue = [];
if ($is_logged_in && !empty($appointments)) {
    foreach ($appointments as $appointment) {
        if ($appointment['status'] !== 'Cancelled' && $appointment['status'] !== 'Completed') {
            $upcoming_queue[] = $appointment;
        }
    }
}

$ongoing_html = '';
if (!$is_logged_in) {
    $ongoing_html .= '<div style="background: linear-gradient(135deg, #eff6ff, #f8fbff); border: 1px solid #dbeafe; border-radius: 20px; padding: 1.2rem; margin-top: 0.5rem;">';
    $ongoing_html .= '<div style="font-size: 1rem; font-weight: 700; color: #0f172a;"><i class="fas fa-lock"></i> Sign in to view your queue info</div>';
    $ongoing_html .= '<p style="color: #64748b; margin: 0.4rem 0 0; font-size: 0.9rem;">Login to see your live ongoing number and appointment updates.</p>';
    $ongoing_html .= '</div>';
} elseif (!empty($upcoming_queue)) {
    $next_app = $upcoming_queue[0];
    $queue_number = count($upcoming_queue);
    $estimated_wait = min(45, max(15, count($upcoming_queue) * 10));

    $ongoing_html .= '<div style="background: linear-gradient(135deg, #0b2b5c, #1a4a8a); color: white; border-radius: 24px; padding: 1.2rem; margin-top: 0.7rem;">';
    $ongoing_html .= '<div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">';
    $ongoing_html .= '<div><div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.8;">Your current queue</div><div style="font-size: 2rem; font-weight: 800;">#' . $queue_number . '</div></div>';
    $ongoing_html .= '<div style="background: rgba(255,255,255,0.16); padding: 0.4rem 0.8rem; border-radius: 999px; font-size: 0.8rem; font-weight: 700;">' . $estimated_wait . ' min wait</div>';
    $ongoing_html .= '</div>';
    $ongoing_html .= '<div style="margin-top: 0.8rem; font-size: 0.95rem; line-height: 1.6; opacity: 0.95;">';
    $ongoing_html .= '<i class="fas fa-user-md"></i> Next appointment: <strong>' . htmlspecialchars($next_app['doctor_name']) . '</strong><br>';
    $ongoing_html .= '<i class="fas fa-calendar-day"></i> ' . date('d M Y', strtotime($next_app['app_date'])) . ' at ' . date('h:i A', strtotime($next_app['app_time'])) . '<br>';
    $ongoing_html .= '<i class="fas fa-check-circle"></i> Status: <strong>' . htmlspecialchars($next_app['status']) . '</strong>';
    $ongoing_html .= '</div>';
    $ongoing_html .= '</div>';
} else {
    $ongoing_html .= '<div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 20px; padding: 1rem; margin-top: 0.5rem;">';
    $ongoing_html .= '<div style="font-size: 1rem; font-weight: 700; color: #0f172a;"><i class="fas fa-info-circle"></i> No active queue number</div>';
    $ongoing_html .= '<p style="color: #64748b; margin: 0.4rem 0 0; font-size: 0.9rem;">Book an appointment to see your queue number and live updates here.</p>';
    $ongoing_html .= '</div>';
}

// ====== SIDEBAR CONTENT DATA ======
$sidebar_content = [
    'home' => [
        'icon' => 'fa-home',
        'title' => 'Home',
        'description' => 'Welcome to your DOC.lk Dashboard. Manage your appointments and health services here.',
        'details' => [],
        'type' => 'content'
    ],
    'ongoing' => [
        'icon' => 'fa-clock',
        'title' => 'Ongoing Number',
        'description' => 'Check your current queue number in real-time.',
        'details' => [
            'Real-time queue updates',
            'Estimated waiting time',
            'Appointment status tracking',
            'Queue progress updates',
            'Live status tracking'
        ],
        'type' => 'link',
        'url' => 'ongoing_number.php'
    ],
    'mybooking' => [
        'icon' => 'fa-calendar-check',
        'title' => 'My Booking',
        'description' => 'View and manage all your appointments.',
        'details' => [
            'View upcoming appointments',
            'Cancel or reschedule bookings',
            'View appointment history',
            'Download appointment receipts',
            'Rate your doctor experience'
        ],
        'type' => 'link',
        'url' => 'my_booking.php'  // නිවැරදිව සකසා ඇත
    ],
    'refund' => [
        'icon' => 'fa-undo-alt',
        'title' => 'Refund Request',
        'description' => 'Submit a refund request for cancelled appointments.',
        'details' => [
            'Submit refund requests online',
            'Track refund status',
            '24-48 hour processing time',
            'Money back guarantee',
            'Customer support assistance'
        ],
        'type' => 'link',
        'url' => 'refund_request.php'  // නිවැරදිව සකසා ඇත
    ],
    'about' => [
        'icon' => 'fa-info-circle',
        'title' => 'About Us',
        'description' => 'Learn more about DOC.lk and our mission.',
        'details' => [
            'Founded in 2024',
            '100+ partner hospitals',
            '500+ specialist doctors',
            '10,000+ happy patients',
            'Committed to quality healthcare'
        ],
        'type' => 'link',
        'url' => '/DOC/DOCTOR_BOOKING/about_us.php'
    ],
    'terms' => [
        'icon' => 'fa-file-contract',
        'title' => 'Terms & Condition',
        'description' => 'Our terms and conditions for using DOC.lk services.',
        'details' => [
            'User agreement and obligations',
            'Privacy and data protection',
            'Booking and cancellation policy',
            'Payment terms and conditions',
            'Liability and disclaimers'
        ],
        'type' => 'link',
        'url' => '/DOC/DOCTOR_BOOKING/terms_conditions.php'
    ],
    'privacy' => [
        'icon' => 'fa-shield-alt',
        'title' => 'Privacy Notice',
        'description' => 'How we protect your personal information.',
        'details' => [
            'Data collection and usage',
            'Cookie policy',
            'Third-party sharing',
            'Data security measures',
            'Your rights and choices'
        ],
        'type' => 'link',
        'url' => '/DOC/DOCTOR_BOOKING/privacy_notice.php'
    ],
    'contact' => [
        'icon' => 'fa-envelope',
        'title' => 'Contact Us',
        'description' => 'Get in touch with our support team.',
        'details' => [
            'Email: support@doc.lk',
            'Phone: +94 112 345 678',
            'WhatsApp: +94 77 123 4567',
            'Visit us: No. 01, Galle Road, Colombo 07',
            '24/7 customer support'
        ],
        'type' => 'link',
        'url' => '/DOC/DOCTOR_BOOKING/contact_us.php'
    ],
    'faq' => [
        'icon' => 'fa-question-circle',
        'title' => 'FAQ',
        'description' => 'Frequently asked questions about DOC.lk.',
        'details' => [
            'How to book an appointment?',
            'How to cancel a booking?',
            'What are the payment methods?',
            'How to get a refund?',
            'How to contact a doctor?',
            'What if I miss my appointment?'
        ],
        'type' => 'link',
        'url' => '/DOC/DOCTOR_BOOKING/faq.php'
    ],
    
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ====== MODERN NAVBAR ====== */
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
            transition: all 0.3s ease;
        }
        .navbar-modern .brand {
            font-size: 1.7rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
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
        .navbar-modern .nav-center a i { font-size: 1rem; }
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
            transition: all 0.2s ease;
        }
        .navbar-modern .profile-dropdown:hover {
            background: rgba(37, 99, 235, 0.04);
            border-color: rgba(37, 99, 235, 0.15);
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
        .navbar-modern .btn-login-nav {
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
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
        .navbar-modern .btn-login-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
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
            .navbar-modern .btn-login-nav { width: 100%; justify-content: center; }
        }

        @media (max-width: 480px) {
            .navbar-modern { padding: 0.6rem 1rem; }
            .navbar-modern .brand { font-size: 1.3rem; }
            .navbar-modern .profile-dropdown .user-name { display: none; }
            .navbar-modern .btn-logout { padding: 0.3rem 0.8rem; font-size: 0.8rem; }
            .navbar-modern .btn-login-nav { padding: 0.3rem 0.8rem; font-size: 0.8rem; }
        }

        /* ====== DASHBOARD LAYOUT WITH SIDEBAR ====== */
        .dashboard-layout {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        /* ====== SIDEBAR ====== */
        .sidebar {
            flex: 0 0 240px;
            min-width: 200px;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border-radius: 28px;
            padding: 1.5rem 0.8rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(255,255,255,0.6);
            position: sticky;
            top: 90px;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .sidebar .sidebar-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 0 0.8rem 0.8rem;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        .sidebar .sidebar-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.6rem 0.8rem;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #1e293b;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.2rem;
            text-decoration: none;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        .sidebar .sidebar-item i {
            font-size: 1rem;
            color: #2563eb;
            width: 20px;
            text-align: center;
        }
        .sidebar .sidebar-item:hover {
            background: rgba(37, 99, 235, 0.06);
            color: #2563eb;
        }
        .sidebar .sidebar-item.active {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            font-weight: 600;
        }
        .sidebar .sidebar-divider {
            height: 1px;
            background: rgba(0,0,0,0.04);
            margin: 0.3rem 0.8rem;
        }

        /* ====== MAIN CONTENT ====== */
        .main-content {
            flex: 1;
            min-width: 0;
        }

        /* ====== BANNER ====== */
       .banner {
    background: linear-gradient(135deg, rgba(11, 43, 92, 0.85), rgba(26, 74, 138, 0.85)), 
                url('image project/images.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: white;
    padding: 2.5rem 2rem;
    border-radius: 28px;
    margin-bottom: 2rem;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
    min-height: 180px;
}
        

        /* ====== CONTENT PANEL ====== */
        .content-panel {
            background: #ffffff;
            border-radius: 28px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
            animation: panelSlideDown 0.3s ease;
            display: none;
        }
        .content-panel.active {
            display: block;
        }
        @keyframes panelSlideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .content-panel .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .content-panel .panel-header .panel-title-group {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .content-panel .panel-header .panel-title-group .panel-icon {
            font-size: 2.5rem;
            color: #2563eb;
            background: #eff6ff;
            padding: 0.5rem;
            border-radius: 60px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .content-panel .panel-header .panel-title-group h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .content-panel .panel-header .panel-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #94a3b8;
            cursor: pointer;
            transition: 0.2s;
            padding: 0.3rem 0.6rem;
            border-radius: 40px;
        }
        .content-panel .panel-header .panel-close:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .content-panel .panel-description {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 18px;
            background: linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
            border: 1px solid #dbeafe;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
            color: #334155;
            font-size: 1rem;
            line-height: 1.7;
        }
        .content-panel .panel-description .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18);
        }
        .content-panel .panel-description .description-text {
            color: #0f172a;
            font-weight: 500;
        }
        .content-panel .panel-details {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 0.5rem 1.5rem;
        }
        .content-panel .panel-details li {
            padding: 0.6rem 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #1e293b;
            font-size: 0.95rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .content-panel .panel-details li:last-child { border-bottom: none; }
        .content-panel .panel-details li i {
            color: #2563eb;
            font-size: 0.9rem;
            width: 20px;
        }

        /* ====== AUTH FORMS ====== */
        .auth-form {
            max-width: 450px;
            margin: 0 auto;
        }
        .auth-form .form-group {
            margin-bottom: 1.2rem;
        }
        .auth-form .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
            margin-bottom: 0.3rem;
        }
        .auth-form .form-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
            background: #f8fafc;
            transition: 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .auth-form .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
            background: #ffffff;
        }
        .auth-form .btn-submit {
            width: 100%;
            padding: 0.8rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: 0.25s;
            font-family: 'Inter', sans-serif;
        }
        .auth-form .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        .auth-form .alert {
            padding: 0.7rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .auth-form .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border-left: 4px solid #dc2626;
        }
        .auth-form .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
        .auth-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }
        @media (max-width: 480px) {
            .auth-form .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* ====== GLASS SEARCH ====== */
        .glass-search {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(14px) saturate(180%);
            -webkit-backdrop-filter: blur(14px) saturate(180%);
            border-radius: 32px;
            padding: 2rem 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 8px 40px rgba(0,0,0,0.04), 0 2px 8px rgba(0,0,0,0.02);
            border: 1px solid rgba(255,255,255,0.7);
            position: relative;
            overflow: hidden;
        }
        .glass-search::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(37,99,235,0.03) 0%, transparent 70%);
            pointer-events: none;
        }
        .glass-search .search-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .glass-search .search-title i { color: #2563eb; font-size: 1.4rem; }
        .glass-search .search-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 1rem 1.2rem;
        }
        .glass-search .search-row .field-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }
        .glass-search .search-row .field-group input,
        .glass-search .search-row .field-group select {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1.5px solid rgba(0,0,0,0.06);
            border-radius: 40px;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(2px);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
            color: #0f172a;
            appearance: auto;
        }
        .glass-search .search-row .field-group input:focus,
        .glass-search .search-row .field-group select:focus {
            outline: none;
            border-color: #2563eb;
            background: rgba(255,255,255,0.9);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.08);
        }
        .glass-search .search-row .field-group input::placeholder {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .glass-search .search-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.2rem;
            gap: 0.8rem;
        }
        .glass-search .btn-search-glass {
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            color: #fff;
            border: none;
            padding: 0.6rem 2rem;
            border-radius: 60px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 6px 20px rgba(37,99,235,0.25);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .glass-search .btn-search-glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(37,99,235,0.35);
        }
        .glass-search .btn-reset {
            background: rgba(0,0,0,0.05);
            color: #475569;
            border: 1px solid rgba(0,0,0,0.06);
            padding: 0.6rem 1.5rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .glass-search .btn-reset:hover { background: rgba(0,0,0,0.08); }

        /* ====== SEARCH RESULTS ====== */
        .search-results-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0,0,0,0.06);
        }
        .search-results-section .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .search-results-section .result-header .result-count {
            font-weight: 600;
            color: #0f172a;
            font-size: 1rem;
        }
        .search-results-section .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        .search-results-section .result-card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(4px);
            padding: 1rem 1.2rem;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.8);
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: 0.25s;
        }
        .search-results-section .result-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(37,99,235,0.06);
            border-color: #2563eb;
        }
        .search-results-section .result-card .doc-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.05rem;
        }
        .search-results-section .result-card .doc-name i { color: #2563eb; margin-right: 6px; }
        .search-results-section .result-card .doc-specialty { font-size: 0.85rem; color: #475569; margin-top: 2px; }
        .search-results-section .result-card .doc-contact {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.4rem;
        }
        .search-results-section .result-card .doc-contact i { color: #2563eb; margin-right: 4px; width: 16px; }
        .search-results-section .no-results {
            color: #64748b;
            padding: 1.5rem 0;
            text-align: center;
        }
        .search-results-section .no-results i {
            font-size: 2rem;
            color: #94a3b8;
            display: block;
            margin-bottom: 0.5rem;
        }

        /* ====== DETAILS BAR ====== */
        .details-bar {
            background: #f1f5f9;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 1rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            color: #334155;
        }
        .details-bar i { color: #2563eb; margin-right: 0.5rem; }

        /* ====== CHANNEL YOUR DOCTOR (Home Page Only) ====== */
        .channel-doctor-wrapper {
            display: block;
        }
        .channel-doctor-wrapper.hidden {
            display: none;
        }

        /* ====== NAWODYA HOSPITAL SECTION STYLES (for About Us) ====== */
        .nawodya-hospital-section .hospital-header {
            background: linear-gradient(135deg, #0b2b5c, #1a4a8a);
            color: white;
            padding: 2rem;
            border-radius: 28px;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }
        .nawodya-hospital-section .hospital-header .badge {
            background: rgba(255,255,255,0.15);
            padding: 0.5rem 1.5rem;
            border-radius: 60px;
            border: 1px solid rgba(255,255,255,0.2);
            font-weight: 600;
            backdrop-filter: blur(4px);
        }
        .nawodya-hospital-section .doctor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .nawodya-hospital-section .doctor-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 1.5rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: 0.25s;
        }
        .nawodya-hospital-section .doctor-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(37,99,235,0.08);
            border-color: #2563eb;
        }
        .nawodya-hospital-section .doctor-card .doc-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
        }
        .nawodya-hospital-section .doctor-card .doc-specialty {
            color: #2563eb;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0.2rem 0 0.5rem;
        }
        .nawodya-hospital-section .doctor-card .doc-details {
            color: #64748b;
            font-size: 0.85rem;
            margin: 0.2rem 0;
        }
        .nawodya-hospital-section .doctor-card .doc-details i {
            color: #2563eb;
            width: 20px;
        }
        .nawodya-hospital-section .doctor-card .btn-book {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            color: #fff;
            border-radius: 60px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }
        .nawodya-hospital-section .doctor-card .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37,99,235,0.3);
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 1024px) {
            .sidebar { flex: 0 0 200px; min-width: 160px; padding: 1rem 0.5rem; }
            .sidebar .sidebar-item { font-size: 0.78rem; padding: 0.5rem 0.6rem; }
        }

        @media (max-width: 768px) {
            .dashboard-layout { flex-direction: column; }
            .sidebar {
                flex: 1;
                width: 100%;
                min-width: unset;
                position: relative;
                top: 0;
                max-height: unset;
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 0.3rem;
                padding: 1rem;
                border-radius: 20px;
            }
            .sidebar .sidebar-title { display: none; }
            .sidebar .sidebar-item {
                font-size: 0.75rem;
                padding: 0.5rem 0.6rem;
                margin: 0;
                justify-content: center;
                flex-direction: column;
                gap: 0.2rem;
                text-align: center;
                border-radius: 12px;
            }
            .sidebar .sidebar-item i { font-size: 1.2rem; margin-bottom: 0.1rem; }
            .sidebar .sidebar-divider { display: none; }
            .banner { flex-direction: column; text-align: center; }
            .banner h2 { font-size: 1.5rem; }
            .glass-search { padding: 1.5rem; }
            .glass-search .search-row { grid-template-columns: 1fr; }
            .glass-search .search-actions { flex-direction: column; }
            .glass-search .search-actions .btn-search-glass,
            .glass-search .search-actions .btn-reset { width: 100%; justify-content: center; }
            .search-results-section .result-grid { grid-template-columns: 1fr; }
            .details-bar { flex-direction: column; align-items: center; }
            .content-panel { padding: 1.5rem; }
            .content-panel .panel-header .panel-title-group h3 { font-size: 1.2rem; }
            .content-panel .panel-header .panel-title-group .panel-icon { width: 45px; height: 45px; font-size: 1.8rem; }
            .content-panel .panel-details { grid-template-columns: 1fr; }
            .nawodya-hospital-section .hospital-header { flex-direction: column; text-align: center; }
            .nawodya-hospital-section .doctor-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .sidebar { grid-template-columns: repeat(2, 1fr); padding: 0.8rem; gap: 0.3rem; }
            .sidebar .sidebar-item { font-size: 0.65rem; padding: 0.4rem 0.3rem; }
            .sidebar .sidebar-item i { font-size: 1rem; }
            .banner { padding: 1.5rem; }
            .banner-left h2 { font-size: 1.2rem; }
            .banner-right .offer { font-size: 1.2rem; }
            .glass-search { padding: 1rem; }
            .glass-search .search-title { font-size: 1rem; }
            .glass-search .search-row .field-group input,
            .glass-search .search-row .field-group select { font-size: 0.85rem; padding: 0.5rem 0.6rem; }
            .content-panel { padding: 1rem; }
            .content-panel .panel-header .panel-title-group h3 { font-size: 1rem; }
            .content-panel .panel-header .panel-title-group .panel-icon { width: 35px; height: 35px; font-size: 1.4rem; }
            .nawodya-hospital-section .hospital-header { padding: 1.5rem; }
            .nawodya-hospital-section .hospital-header h3 { font-size: 1.3rem; }
        }

        /* ====== APPOINTMENTS SECTION ====== */
        .appointments-card {
            margin-top: 0.5rem;
            background: #ffffff;
            border-radius: 24px;
            padding: 1.4rem 1.5rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
        }
        .appointments-card .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }
        .appointments-card .card-title i { color: #2563eb; }
        .appointments-card .table-wrap { overflow-x: auto; }
        .appointments-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .appointments-card th {
            text-align: left;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            padding: 0.7rem 0.6rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .appointments-card td {
            padding: 0.8rem 0.6rem;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
            font-size: 0.95rem;
        }
        .appointments-card tr:last-child td { border-bottom: none; }

        /* ====== FOOTER ====== */
        .footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 2.5rem 2rem 1rem;
            border-radius: 28px 28px 0 0;
            margin-top: 2.5rem;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .footer-grid h4 { color: #fff; margin-bottom: 0.8rem; font-size: 1.1rem; }
        .footer-grid p, .footer-grid a { color: #94a3b8; font-size: 0.9rem; line-height: 1.8; }
        .footer-grid a:hover { color: #fff; }
        .footer-services a {
            display: block;
            width: fit-content;
            padding: 0.18rem 0;
            text-decoration: none;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .footer-services a::before {
            content: '\f054';
            margin-right: 0.45rem;
            color: #2563eb;
            font: 700 0.65rem/1 'Font Awesome 6 Free';
        }
        .footer-services a:hover { transform: translateX(3px); }
        .footer-bottom {
            border-top: 1px solid #1e293b;
            padding-top: 1.2rem;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
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
            <?php if ($is_logged_in): ?>
                <a href="main_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../appointments.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
                <a href="find_doctor.php" class="find-doctor-nav"><i class="fas fa-user-md"></i> Find a Doctor</a>
            <?php else: ?>
                <a href="main_dashboard.php" class="active"><i class="fas fa-home"></i> Home</a>
            <?php endif; ?>
        </div>
        <div class="nav-right">
            <?php if ($is_logged_in): ?>
                <div class="profile-dropdown">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
                <a href="?logout=1" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="../login.php" class="btn-login-nav">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">

        <!-- ====== DASHBOARD LAYOUT WITH SIDEBAR ====== -->
        <div class="dashboard-layout">

            <!-- ====== SIDEBAR ====== -->
            <aside class="sidebar">
                <div class="sidebar-title"><i class="fas fa-th-list"></i> Quick Menu</div>
                
                <?php foreach ($sidebar_content as $key => $item): ?>
                    <?php if (isset($item['type']) && $item['type'] === 'link'): ?>
                        <!-- ====== Link Type (Opens separate page) ====== -->
                        <button class="sidebar-item" onclick="window.location.href='<?php echo $item['url']; ?>'">
                            <i class="fas <?php echo $item['icon']; ?>"></i>
                            <?php echo htmlspecialchars($item['title']); ?>
                        </button>
                    <?php else: ?>
                        <!-- ====== Content Type (Opens in panel) ====== -->
                        <button class="sidebar-item" onclick="showContent('<?php echo $key; ?>')">
                            <i class="fas <?php echo $item['icon']; ?>"></i>
                            <?php echo htmlspecialchars($item['title']); ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <div class="sidebar-divider"></div>
                <?php if ($is_logged_in): ?>
                    <button class="sidebar-item" onclick="window.location.href='../appointments.php'" style="color:#2563eb;font-weight:600;">
                        <i class="fas fa-calendar-plus"></i>
                        Book Now
                    </button>
                <?php else: ?>
                   
                <?php endif; ?>
            </aside>

            <!-- ====== MAIN CONTENT ====== -->
            <main class="main-content">

                <!-- ====== BANNER ====== -->
                <div class="banner">
                    <div class="banner-left">
                        <h2>DOCTOR CHANNELING <br>IS NOW <span>CASHLESS</span></h2>
                        <p><i class="fas fa-shield-alt"></i> Exclusively for Softlogic Life Corporate Customers</p>
                    </div>
                    <div class="banner-right">
                        <div class="offer">35% OFF</div>
                        <div class="sub">ON BOOKING CHARGES</div>
                    </div>
                </div>

                <!-- ====== CONTENT PANEL ====== -->
                <div class="content-panel" id="contentPanel">
                    <div class="panel-header">
                        <div class="panel-title-group">
                            <div class="panel-icon" id="panelIcon"><i class="fas fa-home"></i></div>
                            <h3 id="panelTitle">Home</h3>
                        </div>
                        <button class="panel-close" onclick="closePanel()"><i class="fas fa-times"></i></button>
                    </div>
                    <div id="panelContent">
                        <p class="panel-description" id="panelDescription">
                            <span class="welcome-badge"><i class="fas fa-user-check"></i> Welcome</span>
                            <span class="description-text">Welcome to your DOC.lk Dashboard. Manage your appointments and health services here.</span>
                        </p>
                        <ul class="panel-details" id="panelDetails"></ul>
                    </div>
                </div>

                <!-- ====== GLASS SEARCH (Channel Your Doctor - Home Page Only) ====== -->
                <?php if ($is_logged_in): ?>
                <div class="channel-doctor-wrapper" id="channelDoctorWrapper">
                    <div class="glass-search">
                        <div class="search-title">
                            <i class="fas fa-search"></i> Channel Your Doctor
                        </div>
                        <form method="GET" action="">
                            <div class="search-row">
                                <div class="field-group">
                                    <label>Doctor</label>
                                    <select name="doctor_id">
                                        <option value="">— Select Doctor —</option>
                                        <?php foreach ($all_doctors as $doc): ?>
                                            <option value="<?php echo $doc['doctor_id']; ?>" <?php echo ($selected_doctor_id == $doc['doctor_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($doc['name'] . ' (' . ($doc['service_name'] ?? 'General') . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- Hospital field removed -->
                                <div class="field-group">
                                    <label>Specialization</label>
                                    <select name="specialization">
                                        <option value="">— Any —</option>
                                        <?php foreach ($all_specializations as $spec): ?>
                                            <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo ($specialization == $spec) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($spec); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field-group">
                                    <label>Date</label>
                                    <input type="date" name="search_date" value="<?php echo htmlspecialchars($search_date); ?>">
                                </div>
                            </div>
                            <div class="search-actions">
                                <a href="main_dashboard.php" class="btn-reset"><i class="fas fa-undo"></i> Reset</a>
                                <button type="submit" name="search_doctor" class="btn-search-glass">
                                    <i class="fas fa-arrow-right"></i> Search
                                </button>
                            </div>
                        </form>

                        <!-- ====== SEARCH RESULTS ====== -->
                        <?php if ($search_triggered): ?>
                            <div class="search-results-section">
                                <div class="result-header">
                                    <div class="result-count">
                                        <i class="fas fa-list"></i> <?php echo count($search_results); ?> doctor(s) found
                                    </div>
                                </div>
                                <?php if (count($search_results) > 0): ?>
                                    <div class="result-grid">
                                        <?php foreach ($search_results as $doc): ?>
                                            <div class="result-card">
                                                <div class="doc-name"><i class="fas fa-user-md"></i><?php echo htmlspecialchars($doc['name']); ?></div>
                                                <div class="doc-specialty"><?php echo htmlspecialchars($doc['service_name'] ?? 'General'); ?></div>
                                                <div class="doc-contact"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doc['contact_no'] ?? 'N/A'); ?></div>
                                                <div class="doc-contact"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($doc['email'] ?? 'N/A'); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-results">
                                        <i class="fas fa-info-circle"></i>
                                        No doctors found matching your criteria. Please try different search terms.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ====== DETAILS BAR ====== -->
                <div class="details-bar">
                    <span><i class="fas fa-phone-alt"></i> Hotline: 0112 345 678</span>
                    <span><i class="fas fa-envelope"></i> support@doc.lk</span>
                    <span><i class="fas fa-clock"></i> Mon-Sat: 8:00 AM - 8:00 PM</span>
                    <span><i class="fas fa-map-marker-alt"></i> Colombo 07, Sri Lanka</span>
                </div>

                <!-- ====== APPOINTMENTS TABLE ====== -->
                <div class="appointments-card">
                    <div class="card-title"><i class="fas fa-list"></i> Your Appointments</div>
                    <?php if ($is_logged_in): ?>
                        <?php if (count($appointments) > 0): ?>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Doctor</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $app): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($app['doctor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($app['service_name'] ?? '—'); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($app['app_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($app['app_time'])); ?></td>
                                                <td>
                                                    <span class="badge" style="background:<?php echo ($app['status']=='Confirmed')?'#22c55e':($app['status']=='Cancelled'?'#ef4444':'#f59e0b'); ?>;color:#fff;padding:0.2rem 0.8rem;border-radius:40px;font-size:0.8rem;">
                                                        <?php echo $app['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Doctor</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Dr. Kamala Silva</td>
                                            <td>Pediatrics</td>
                                            <td>2026-08-08</td>
                                            <td>03:00 PM</td>
                                            <td><span class="badge" style="background:#ef4444;color:#fff;padding:0.2rem 0.8rem;border-radius:40px;font-size:0.8rem;">Cancelled</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p style="color:#64748b;margin-top:1rem;">You have no appointments yet. <a href="../appointments.php" style="color:#2563eb;">Book a new appointment</a></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color:#64748b;margin-top:1rem;">Please <a href="../login.php" style="color:#2563eb;">login</a> to view your appointments.</p>
                    <?php endif; ?>
                </div>

            </main>

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
                    <a href="about_us.php">About Us</a><br>
                    <a href="contact_us.php">Contact Us</a><br>
                    <a href="terms_conditions.php">Terms of Service</a><br>
                    <a href="privacy_notice.php">Privacy Policy</a><br>
                    <a href="faq.php">FAQ</a>
                </div>
                <div class="footer-services">
                    <h4>Health Services</h4>
                    <a href="service_details.php?service=health-mart">Health Mart</a>
                    <a href="service_details.php?service=medicine">Medicine to Your Doorstep</a>
                    <a href="service_details.php?service=audio-video">Audio/Video Consultation</a>
                    <a href="service_details.php?service=ayurvedic">Ayurvedic Consultations</a>
                    <a href="service_details.php?service=sexual-wellness">Sexual Wellness</a>
                    <a href="service_details.php?service=visa-medical">Visa Medical Test</a>
                    <a href="service_details.php?service=marketplace">Marketplace Health Packages</a>
                    <a href="service_details.php?service=lab-reports">Lab Reports at Your Fingertips</a>
                    <a href="ongoing_number.php">Check Ongoing Number</a>
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
        // ====== PASS PHP VARIABLE TO JS ======
        const isLoggedIn = <?php echo json_encode($is_logged_in); ?>;

        // ====== NAVBAR TOGGLE ======
        function toggleNav() {
            document.getElementById('navCenter').classList.toggle('open');
        }
        document.querySelectorAll('.nav-center a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navCenter').classList.remove('open');
            });
        });

        // ====== SIDEBAR CONTENT DATA ======
        const sidebarData = <?php echo json_encode($sidebar_content); ?>;

        // ====== SHOW CONTENT IN PANEL ======
        function showContent(key) {
            const data = sidebarData[key];
            if (!data) return;

            const panel = document.getElementById('contentPanel');
            const panelContent = document.getElementById('panelContent');
            const panelIcon = document.getElementById('panelIcon');
            const panelTitle = document.getElementById('panelTitle');
            const panelDescription = document.getElementById('panelDescription');
            const panelDetails = document.getElementById('panelDetails');

            // Show panel
            panel.classList.add('active');

            // Highlight active sidebar item
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('active');
            });
            const items = document.querySelectorAll('.sidebar-item');
            items.forEach(item => {
                if (item.textContent.trim() === data.title) {
                    item.classList.add('active');
                }
            });

            // ====== SHOW/HIDE CHANNEL YOUR DOCTOR ======
            const channelWrapper = document.getElementById('channelDoctorWrapper');
            if (channelWrapper) {
                if (key === 'home' && <?php echo $is_logged_in ? 'true' : 'false'; ?>) {
                    channelWrapper.classList.remove('hidden');
                } else {
                    channelWrapper.classList.add('hidden');
                }
            }

            // ====== HANDLE LOGIN FORM ======
            if (data.type === 'login') {
                panelIcon.innerHTML = `<i class="fas fa-sign-in-alt"></i>`;
                panelTitle.textContent = 'Sign In';
                panelContent.innerHTML = `
                    <p class="panel-description">Login to your DOC.lk account to access all features.</p>
                    <div class="auth-form">
                        <?php if ($login_error): ?>
                            <div class="alert alert-danger"><?php echo $login_error; ?></div>
                        <?php endif; ?>
                        <?php if ($login_success): ?>
                            <div class="alert alert-success"><?php echo $login_success; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <input type="hidden" name="login_action" value="1">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Username</label>
                                <input type="text" name="username" placeholder="Enter your username" required autofocus>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Password</label>
                                <input type="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn-submit"><i class="fas fa-sign-in-alt"></i> Sign In</button>
                        </form>
                        <div style="text-align:center;margin-top:1rem;font-size:0.9rem;color:#64748b;">
                            Don't have an account? <a href="#" onclick="showContent('signup');return false;" style="color:#2563eb;font-weight:600;">Sign Up</a>
                        </div>
                    </div>
                `;
            }
            // ====== HANDLE REGISTER FORM ======
            else if (data.type === 'register') {
                panelIcon.innerHTML = `<i class="fas fa-user-plus"></i>`;
                panelTitle.textContent = 'Sign Up';
                panelContent.innerHTML = `
                    <p class="panel-description">Create your free DOC.lk account in minutes.</p>
                    <div class="auth-form">
                        <?php if ($register_error): ?>
                            <div class="alert alert-danger"><?php echo $register_error; ?></div>
                        <?php endif; ?>
                        <?php if ($register_success): ?>
                            <div class="alert alert-success"><?php echo $register_success; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <input type="hidden" name="register_action" value="1">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Username</label>
                                <input type="text" name="reg_username" placeholder="Choose a username" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-user-circle"></i> Full Name</label>
                                <input type="text" name="reg_full_name" placeholder="Enter your full name" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="reg_email" placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="text" name="reg_contact" placeholder="Enter your phone number">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-lock"></i> Password</label>
                                    <input type="password" name="reg_password" placeholder="Min 4 characters" required minlength="4">
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-check-circle"></i> Confirm Password</label>
                                    <input type="password" name="reg_confirm_password" placeholder="Confirm password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn-submit"><i class="fas fa-user-check"></i> Create Account</button>
                        </form>
                        <div style="text-align:center;margin-top:1rem;font-size:0.9rem;color:#64748b;">
                            Already have an account? <a href="#" onclick="showContent('signin');return false;" style="color:#2563eb;font-weight:600;">Sign In</a>
                        </div>
                    </div>
                `;
            }
            // ====== HANDLE NORMAL CONTENT ======
            else {
                panelIcon.innerHTML = `<i class="fas ${data.icon}"></i>`;
                panelTitle.textContent = data.title;

                // Build content
                let detailsHTML = '';
                if (data.details && data.details.length > 0) {
                    detailsHTML = '<ul class="panel-details">';
                    data.details.forEach(detail => {
                        detailsHTML += `<li><i class="fas fa-check-circle"></i> ${detail}</li>`;
                    });
                    detailsHTML += '</ul>';
                }

                // Append custom HTML if present (e.g., Nawodya Hospital section)
                let customHTML = data.custom_html ? data.custom_html : '';

                panelContent.innerHTML = `
                    <p class="panel-description">${data.description}</p>
                    ${detailsHTML}
                    ${customHTML}
                `;
            }
        }

        // ====== CLOSE PANEL ======
        function closePanel() {
            document.getElementById('contentPanel').classList.remove('active');
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('active');
            });
        }

        // ====== AUTO SHOW: LOGIN FORM IF NOT LOGGED IN, ELSE HOME ======
        document.addEventListener('DOMContentLoaded', function() {
            if (isLoggedIn) {
                showContent('home');
            } else {
                showContent('signin');
            }
        });
    </script>

</body>
</html>