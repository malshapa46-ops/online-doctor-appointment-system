<?php
require_once 'db.php';
require_once 'generate_apt_no.php';
require_once 'send_email.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../appointments.php');
    exit;
}

$doctor_id = intval($_POST['doctor_id']);
$app_date  = $_POST['app_date'];
$app_time  = $_POST['app_time'];
$user_id   = $_SESSION['user_id'];

$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';

if (empty($doctor_id) || empty($app_date) || empty($app_time)) {
    header('Location: ../appointments.php?error=Please fill in all fields');
    exit;
}


$stmt = $conn->prepare("SELECT full_name, email, contact_no, address FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    header('Location: ../appointments.php?error=Patient not found');
    exit;
}

// Address/Phone update
if (!empty($address) || !empty($phone)) {
    $new_address = !empty($address) ? $address : $patient['address'];
    $new_phone   = !empty($phone) ? $phone : $patient['contact_no'];

    $update = $conn->prepare("UPDATE user SET address = ?, contact_no = ? WHERE user_id = ?");
    $update->bind_param("ssi", $new_address, $new_phone, $user_id);
    $update->execute();
    $update->close();

    $patient['address']    = $new_address;
    $patient['contact_no'] = $new_phone;
}

$stmt = $conn->prepare("INSERT INTO appointment (doctor_id, user_id, date, time, status) VALUES (?, ?, ?, ?, 'Pending')");
$stmt->bind_param("iiss", $doctor_id, $user_id, $app_date, $app_time);

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: ../appointments.php?error=Failed to book appointment');
    exit;
}

$appointment_id = $conn->insert_id;
$stmt->close();


$appointment_no = generateAppointmentNumber($conn, $appointment_id);
$stmt = $conn->prepare("UPDATE appointment SET appointment_no = ? WHERE id = ?");
$stmt->bind_param("si", $appointment_no, $appointment_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("
    SELECT d.name AS doctor_name, s.service_name
    FROM doctor d
    LEFT JOIN service s ON d.service_id = s.service_id
    WHERE d.doctor_id = ?
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$stmt->close();

$doctor_name  = $doctor['doctor_name'] ?? 'Doctor';
$service_name = $doctor['service_name'] ?? 'General';

sendAppointmentEmail(
    $patient['email'],
    $patient['full_name'],
    $patient['address'] ?? 'N/A',
    $patient['contact_no'] ?? 'N/A',
    $appointment_no,
    $doctor_name,
    $service_name,
    $app_date,
    $app_time,
    'Pending'
);

header('Location: payment.php?appointment_no=' . urlencode($appointment_no));
exit;
?>