<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$appointment_reference = trim((string)($_GET['appointment_id'] ?? ''));

if ($appointment_reference !== '') {
    $stmt = $conn->prepare(
        "SELECT a.id, a.appointment_no, a.date, a.time, a.status, a.payment_method, a.payment_number, a.payment_date, d.name AS doctor_name, d.contact_no, s.service_name, u.full_name "
        . "FROM appointment a "
        . "JOIN doctor d ON a.doctor_id = d.doctor_id "
        . "LEFT JOIN service s ON d.service_id = s.service_id "
        . "JOIN user u ON a.user_id = u.user_id "
        . "WHERE (a.appointment_no = ? OR CAST(a.id AS CHAR) = ?) AND a.user_id = ?"
    );
    $stmt->bind_param('ssi', $appointment_reference, $appointment_reference, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    $stmt->close();

    if (!$appointment) {
        header('Location: my_booking.php');
        exit;
    }

    $receipt = "DOC.lk Appointment Receipt\n";
    $receipt .= "=========================\n";
    $receipt .= "Appointment #: " . $appointment['appointment_no'] . "\n";
    $receipt .= "Payment #: " . ($appointment['payment_number'] ?? 'N/A') . "\n";
    $receipt .= "Patient: " . $appointment['full_name'] . "\n";
    $receipt .= "Doctor: " . $appointment['doctor_name'] . "\n";
    $receipt .= "Specialization: " . ($appointment['service_name'] ?? 'General') . "\n";
    $receipt .= "Payment Method: " . ($appointment['payment_method'] ? ucwords(str_replace('_', ' ', $appointment['payment_method'])) : 'N/A') . "\n";
    $receipt .= "Date: " . date('d M Y', strtotime($appointment['date'])) . "\n";
    $receipt .= "Time: " . date('h:i A', strtotime($appointment['time'])) . "\n";
    $receipt .= "Status: " . $appointment['status'] . "\n";
    $receipt .= "Contact: " . ($appointment['contact_no'] ?? 'N/A') . "\n";
    if (!empty($appointment['payment_date'])) {
        $receipt .= "Paid At: " . date('d M Y H:i:s', strtotime($appointment['payment_date'])) . "\n";
    }
    $receipt .= "Generated: " . date('d M Y H:i:s') . "\n";

    $filename = 'appointment_receipt_' . $appointment['appointment_no'] . '.txt';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    echo $receipt;
    exit;
}

$appointments = [];
$stmt = $conn->prepare(
    "SELECT a.id, a.date, a.time, a.status, d.name AS doctor_name, s.service_name "
    . "FROM appointment a "
    . "JOIN doctor d ON a.doctor_id = d.doctor_id "
    . "LEFT JOIN service s ON d.service_id = s.service_id "
    . "WHERE a.user_id = ? AND a.status != 'Cancelled' "
    . "ORDER BY a.date DESC, a.time DESC"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Receipt – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin:0; font-family:'Inter',sans-serif; background:#f8fbff; color:#0f172a; }
        .container { max-width:1100px; margin:0 auto; padding:2rem 1rem 3rem; }
        .header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
        .title { font-size:2rem; font-weight:700; margin:0; }
        .btn { display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; background:#2563eb; color:#fff; padding:0.9rem 1.25rem; border-radius:999px; font-weight:600; }
        .card { background:#fff; border-radius:24px; padding:1.5rem; border:1px solid #e2e8f0; box-shadow:0 12px 30px rgba(15,23,42,0.06); }
        .tbl { width:100%; border-collapse:collapse; margin-top:1rem; }
        .tbl th, .tbl td { padding:0.95rem 0.75rem; border-bottom:1px solid #e2e8f0; text-align:left; }
        .tbl th { background:#f8fafc; color:#475569; }
        .btn-download { display:inline-flex; align-items:center; gap:0.5rem; background:#10b981; color:#fff; border:none; padding:0.7rem 1rem; border-radius:999px; text-decoration:none; font-weight:700; }
        .btn-download:hover { background:#059669; }
        .muted { color:#64748b; }
        @media (max-width:760px) { .tbl, .tbl thead, .tbl tbody, .tbl th, .tbl td, .tbl tr { display:block; } .tbl th { display:none; } .tbl td { position:relative; padding-left:50%; } .tbl td::before { position:absolute; top:0.9rem; left:0.75rem; width:calc(50% - 1.5rem); color:#475569; font-weight:700; } .tbl td:nth-of-type(1)::before { content:'Doctor'; } .tbl td:nth-of-type(2)::before { content:'Service'; } .tbl td:nth-of-type(3)::before { content:'Date'; } .tbl td:nth-of-type(4)::before { content:'Time'; } .tbl td:nth-of-type(5)::before { content:'Status'; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">Download Receipt</div>
                <p class="muted" style="margin:0.5rem 0 0;">Choose an appointment and download a text receipt instantly.</p>
            </div>
            <a href="my_booking.php" class="btn"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="card">
            <?php if (count($appointments) > 0): ?>
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['service_name'] ?? 'General'); ?></td>
                                <td><?php echo date('d M Y', strtotime($app['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($app['time'])); ?></td>
                                <td><?php echo htmlspecialchars($app['status']); ?></td>
                                <td><a href="download_receipt.php?appointment_id=<?php echo (int)$app['id']; ?>" class="btn-download" download="appointment_receipt_<?php echo (int)$app['id']; ?>.txt"><i class="fas fa-download"></i> Download</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted" style="margin:0;">No receipts available. Book appointments first to download receipts.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
