<?php
require_once 'db.php';
require_once 'send_email.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: main_dashboard.php');
    exit;
}

$appointment_no = trim($_POST['appointment_no']);
$amount         = floatval($_POST['amount']);
$card_name      = trim($_POST['card_name']);
$card_number    = trim($_POST['card_number']);
$expiry         = trim($_POST['expiry']);
$cvv            = trim($_POST['cvv']);

if (empty($appointment_no) || empty($amount) || empty($card_name) || empty($card_number) || empty($expiry) || empty($cvv)) {
    header('Location: payment.php?appointment_no=' . urlencode($appointment_no) . '&error=Please fill all fields');
    exit;
}

$stmt = $conn->prepare("SELECT id FROM appointment WHERE appointment_no = ? AND user_id = ?");
$stmt->bind_param("si", $appointment_no, $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    $stmt->close();
    header('Location: main_dashboard.php');
    exit;
}
$stmt->close();

$conn->begin_transaction();

try {
   
    $stmt = $conn->prepare("INSERT INTO payment (appointment_no, amount, status) VALUES (?, ?, 'Paid')");
    $stmt->bind_param("sd", $appointment_no, $amount);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE appointment SET status = 'Confirmed' WHERE appointment_no = ?");
    $stmt->bind_param("s", $appointment_no);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    $stmt = $conn->prepare("
        SELECT a.appointment_no, a.date, a.time,
               d.name AS doctor_name, s.service_name,
               u.full_name, u.email, u.contact_no, u.address
        FROM appointment a
        JOIN doctor d ON a.doctor_id = d.doctor_id
        JOIN user u ON a.user_id = u.user_id
        LEFT JOIN service s ON d.service_id = s.service_id
        WHERE a.appointment_no = ?
    ");
    $stmt->bind_param("s", $appointment_no);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($info) {
        sendAppointmentEmail(
            $info['email'],
            $info['full_name'],
            $info['address'] ?? 'N/A',
            $info['contact_no'] ?? 'N/A',
            $info['appointment_no'],
            $info['doctor_name'],
            $info['service_name'] ?? 'General',
            $info['date'],
            $info['time'],
            'Confirmed',
            $amount
        );
    }

    header('Location: main_dashboard.php?success=Payment successful! Your appointment is confirmed.');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: payment.php?appointment_no=' . urlencode($appointment_no) . '&error=Payment failed. Please try again.');
    exit;
}
?>