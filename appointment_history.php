<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$history = [];
$stmt = $conn->prepare(
    "SELECT a.id, a.date, a.time, a.status, d.name AS doctor_name, s.service_name "
    . "FROM appointment a "
    . "JOIN doctor d ON a.doctor_id = d.doctor_id "
    . "LEFT JOIN service s ON d.service_id = s.service_id "
    . "WHERE a.user_id = ? "
    . "ORDER BY a.date DESC, a.time DESC"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment History – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin:0; font-family:'Inter',sans-serif; background:#f8fbff; color:#0f172a; }
        .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        .header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
        .title { font-size:2rem; font-weight:700; margin:0; }
        .btn { display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; background:#2563eb; color:#fff; padding:0.85rem 1.2rem; border-radius:999px; font-weight:600; }
        .card { background:#fff; border-radius:24px; padding:1.5rem; border:1px solid #e2e8f0; box-shadow:0 12px 30px rgba(15,23,42,0.06); }
        .tbl { width:100%; border-collapse:collapse; margin-top:1rem; }
        .tbl th, .tbl td { padding:0.95rem 0.75rem; border-bottom:1px solid #e2e8f0; text-align:left; }
        .tbl th { background:#f8fafc; color:#475569; }
        .badge { display:inline-flex; align-items:center; padding:0.35rem 0.8rem; border-radius:999px; font-size:0.85rem; font-weight:700; }
        .status-confirmed { background:#d1fae5; color:#166534; }
        .status-cancelled { background:#fee2e2; color:#b91c1c; }
        .details { color:#475569; font-size:0.95rem; margin-top:0.25rem; }
        .download-btn { display:inline-flex; align-items:center; gap:0.45rem; padding:0.6rem 0.9rem; border-radius:999px; background:#10b981; color:#fff; text-decoration:none; font-weight:700; font-size:0.85rem; }
        .download-btn:hover { background:#059669; }
        @media (max-width:760px) { .tbl, .tbl thead, .tbl tbody, .tbl tr, .tbl th, .tbl td { display:block; } .tbl th { display:none; } .tbl td { position:relative; padding-left:50%; } .tbl td::before { position:absolute; top:0.9rem; left:0.85rem; width:calc(50% - 1.7rem); font-weight:700; color:#475569; } .tbl td:nth-of-type(1)::before { content:'Doctor'; } .tbl td:nth-of-type(2)::before { content:'Service'; } .tbl td:nth-of-type(3)::before { content:'Date'; } .tbl td:nth-of-type(4)::before { content:'Time'; } .tbl td:nth-of-type(5)::before { content:'Status'; } .tbl td:nth-of-type(6)::before { content:'Receipt'; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">Appointment History</div>
                <p style="margin:0.5rem 0 0;color:#64748b;">All your past and cancelled appointments are shown here for reference.</p>
            </div>
            <a href="my_booking.php" class="btn"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="card">
            <?php if (count($history) > 0): ?>
                <table class="tbl">
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
                        <?php foreach ($history as $app): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($app['doctor_name']); ?>
                                    <?php if (!empty($app['details'])): ?>
                                        <div class="details"><?php echo htmlspecialchars($app['details']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($app['service_name'] ?? 'General'); ?></td>
                                <td><?php echo date('d M Y', strtotime($app['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($app['time'])); ?></td>
                                <td><span class="badge status-<?php echo strtolower($app['status']); ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
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
                <p style="margin:0; color:#64748b;">No appointment history is available yet. Your past visits will appear here once completed or cancelled.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
