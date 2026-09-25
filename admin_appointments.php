<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$appointments = [];
$result = $conn->query(
    "SELECT a.id, a.appointment_no, a.date, a.time, a.status,
            a.patient_name AS booking_patient_name, a.patient_mobile,
            a.patient_email, a.patient_address,
            d.name AS doctor_name, d.contact_no AS doctor_contact,
            s.service_name, u.full_name AS account_patient_name,
            u.email AS account_patient_email
     FROM appointment a
     LEFT JOIN doctor d ON a.doctor_id = d.doctor_id
     LEFT JOIN service s ON d.service_id = s.service_id
     LEFT JOIN user u ON a.user_id = u.user_id
     ORDER BY a.date DESC, a.time DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['patient_name'] = $row['booking_patient_name'] ?: ($row['account_patient_name'] ?: 'Unknown patient');
        $row['patient_email_display'] = $row['patient_email'] ?: ($row['account_patient_email'] ?: '');
        $appointments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin:0; font-family:'Inter',sans-serif; background:#f8fbff; color:#0f172a; }
        .container { max-width:1400px; margin:0 auto; padding:2rem 1rem 3rem; }
        .header { display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem; }
        h1 { margin:0; font-size:1.9rem; } .muted { color:#64748b; }
        .back { color:#2563eb; text-decoration:none; font-weight:600; }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:24px; padding:1.2rem; box-shadow:0 12px 30px rgba(15,23,42,.06); overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:1050px; }
        th,td { padding:.85rem .7rem; border-bottom:1px solid #e2e8f0; text-align:left; vertical-align:top; }
        th { background:#f1f5f9; color:#475569; font-size:.78rem; text-transform:uppercase; letter-spacing:.3px; }
        td { font-size:.9rem; } td small { display:block; color:#64748b; margin-top:.25rem; }
        .badge { display:inline-block; padding:.3rem .75rem; border-radius:999px; color:#fff; font-size:.78rem; font-weight:700; }
        .confirmed { background:#22c55e; } .cancelled { background:#ef4444; } .pending { background:#f59e0b; }
        .empty { text-align:center; padding:2rem; color:#64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-calendar-check" style="color:#2563eb;"></i> Manage Appointments</h1>
                <p class="muted">View complete patient, doctor and appointment details.</p>
            </div>
            <a class="back" href="../dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="card">
            <?php if ($appointments): ?>
                <table>
                    <thead><tr>
                        <th>Appointment</th><th>Patient</th><th>Contact</th><th>Doctor</th>
                        <th>Service</th><th>Date</th><th>Time</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($appointments as $app): ?>
                        <?php $status_class = strtolower((string)$app['status']); ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($app['appointment_no'] ?: ('#' . $app['id'])); ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($app['patient_name']); ?></strong>
                                <?php if (!empty($app['patient_address'])): ?><small><?php echo htmlspecialchars($app['patient_address']); ?></small><?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($app['patient_mobile'] ?: 'Not provided'); ?>
                                <?php if (!empty($app['patient_email_display'])): ?><small><?php echo htmlspecialchars($app['patient_email_display']); ?></small><?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($app['doctor_name'] ?: 'Unknown doctor'); ?>
                                <?php if (!empty($app['doctor_contact'])): ?><small><?php echo htmlspecialchars($app['doctor_contact']); ?></small><?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($app['service_name'] ?: 'General'); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($app['date']))); ?></td>
                            <td><?php echo htmlspecialchars(date('h:i A', strtotime($app['time']))); ?></td>
                            <td><span class="badge <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty"><i class="fas fa-calendar-xmark"></i><p>No appointments found.</p></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
