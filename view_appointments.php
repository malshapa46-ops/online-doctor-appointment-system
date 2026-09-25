<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$appointments = [];
$today = date('Y-m-d');
$stmt = $conn->prepare(
    "SELECT a.id, a.date, a.time, a.status, d.name AS doctor_name, s.service_name, d.contact_no "
    . "FROM appointment a "
    . "JOIN doctor d ON a.doctor_id = d.doctor_id "
    . "LEFT JOIN service s ON d.service_id = s.service_id "
    . "WHERE a.user_id = ? AND a.date >= ? AND a.status != 'Cancelled' "
    . "ORDER BY a.date ASC, a.time ASC"
);
$stmt->bind_param('is', $user_id, $today);
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
    <title>Upcoming Appointments – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f8fbff; color: #0f172a; }
        .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        .header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
        .title { font-size: 2rem; font-weight: 700; margin: 0; }
        .btn { display:inline-flex; align-items:center; gap:0.5rem; padding:0.8rem 1.2rem; border-radius:999px; border:none; background:#2563eb; color:#fff; text-decoration:none; font-weight:600; }
        .card { background:#fff; border-radius:24px; border:1px solid #e2e8f0; box-shadow:0 12px 30px rgba(15,23,42,0.06); padding:1.5rem; }
        table { width:100%; border-collapse:collapse; margin-top:1rem; }
        th, td { padding:0.95rem 0.75rem; border-bottom:1px solid #e2e8f0; text-align:left; }
        th { font-weight:700; color:#475569; }
        .badge { display:inline-flex; align-items:center; padding:0.35rem 0.85rem; border-radius:999px; font-size:0.85rem; font-weight:700; }
        .badge-confirmed { background:#d1fae5; color:#166534; }
        .badge-pending { background:#fef3c7; color:#92400e; }
        .badge-cancelled { background:#fee2e2; color:#b91c1c; }
        .download-btn { display:inline-flex; align-items:center; gap:0.45rem; padding:0.6rem 0.95rem; border-radius:999px; background:#10b981; color:#fff; text-decoration:none; font-weight:700; font-size:0.85rem; }
        .download-btn:hover { background:#059669; }
        .muted { color:#64748b; }
        @media (max-width: 760px) { .header { flex-direction:column; align-items:flex-start; } table, thead, tbody, th, td, tr { display:block; } th { display:none; } td { padding:0.85rem; position:relative; } td::before { position:absolute; left:0.75rem; top:0.75rem; font-weight:700; color:#475569; } td:nth-of-type(1)::before { content:'Doctor'; } td:nth-of-type(2)::before { content:'Service'; } td:nth-of-type(3)::before { content:'Date'; } td:nth-of-type(4)::before { content:'Time'; } td:nth-of-type(5)::before { content:'Status'; } td:nth-of-type(6)::before { content:'Receipt'; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">Upcoming Appointments</div>
                <p class="muted" style="margin:0.6rem 0 0;">Review your confirmed and scheduled appointments, and manage them from the booking dashboard.</p>
            </div>
            <a href="my_booking.php" class="btn"><i class="fas fa-arrow-left"></i> Back to My Booking</a>
        </div>
        <div class="card">
            <?php if (count($appointments) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['service_name'] ?? 'General'); ?></td>
                                <td><?php echo date('d M Y', strtotime($app['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($app['time'])); ?></td>
                                <td>
                                    <?php $status = $app['status']; ?>
                                    <span class="badge <?php echo $status === 'Confirmed' ? 'badge-confirmed' : 'badge-pending'; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="download_receipt.php?appointment_id=<?php echo (int)$app['id']; ?>" class="download-btn" download="appointment_receipt_<?php echo (int)$app['id']; ?>.txt">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted" style="margin:0;">No upcoming appointments found. Please book an appointment from the dashboard.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
