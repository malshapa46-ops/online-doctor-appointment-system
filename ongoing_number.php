<?php
require_once 'db.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$appointments = [];

if ($user_id > 0) {
    $stmt = $conn->prepare(
        "SELECT a.id, a.date, a.time, a.status, d.name AS doctor_name, s.service_name "
        . "FROM appointment a "
        . "JOIN doctor d ON a.doctor_id = d.doctor_id "
        . "LEFT JOIN service s ON d.service_id = s.service_id "
        . "WHERE a.user_id = ? AND a.status IN ('Pending','Confirmed','Ongoing') "
        . "ORDER BY a.date ASC, a.time ASC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ongoing Number - DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%); color: #0f172a; margin: 0; padding: 0; }
        .page-wrap { max-width: 900px; margin: 0 auto; padding: 3rem 1.2rem; }
        .card { background: #fff; border-radius: 24px; padding: 2rem; box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08); border: 1px solid #e2e8f0; }
        .btn { display: inline-block; margin-top: 1.2rem; padding: 0.7rem 1.2rem; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; text-decoration: none; border-radius: 999px; font-weight: 600; }
        .queue-box { background: linear-gradient(135deg, #0b2b5c, #1a4a8a); color: white; border-radius: 22px; padding: 1.4rem; margin-bottom: 1rem; }
        .muted { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { color: #475569; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="card">
            <h1 style="margin-top:0; margin-bottom:0.5rem;"><i class="fas fa-list-ol" style="color:#2563eb; margin-right:0.6rem;"></i> Ongoing Number</h1>
            <p class="muted">Track your current appointment queue status and upcoming visits.</p>

            <div class="queue-box">
                <h3 style="margin:0 0 0.3rem;">Current Queue Status</h3>
                <p style="margin:0;">You currently have <strong><?php echo count($appointments); ?></strong> active appointment(s) in progress or pending.</p>
            </div>

            <?php if (!empty($appointments)): ?>
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
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['doctor_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appointment['service_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appointment['date'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appointment['time'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appointment['status'] ?? 'Pending'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted">No active appointments found right now.</p>
            <?php endif; ?>

            <a href="main_dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
