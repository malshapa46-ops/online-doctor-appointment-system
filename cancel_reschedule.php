<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    if ($_POST['action'] === 'cancel') {
        $stmt = $conn->prepare("UPDATE appointment SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $appointment_id, $user_id);
        $stmt->execute();
        $message = 'Appointment cancelled successfully.';
    } elseif ($_POST['action'] === 'reschedule' && !empty($_POST['new_date']) && !empty($_POST['new_time'])) {
        $new_date = trim($_POST['new_date']);
        $new_time = trim($_POST['new_time']);
        $stmt = $conn->prepare("UPDATE appointment SET date = ?, time = ?, status = 'Confirmed' WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ssii', $new_date, $new_time, $appointment_id, $user_id);
        $stmt->execute();
        $message = 'Appointment rescheduled successfully.';
    }
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
    <title>Cancel or Reschedule – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin:0; font-family:'Inter',sans-serif; background:#eef2ff; color:#0f172a; }
        .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        .header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
        .title { font-size:2rem; font-weight:700; margin:0; }
        .btn { display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; background:#2563eb; color:#fff; padding:0.85rem 1.2rem; border-radius:999px; font-weight:600; }
        .card { background:#fff; border-radius:24px; padding:1.5rem; border:1px solid #e2e8f0; box-shadow:0 12px 30px rgba(15,23,42,0.06); margin-bottom:1rem; }
        .form-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
        .form-group { margin-bottom:0.85rem; }
        .form-group label { display:block; margin-bottom:0.35rem; font-weight:600; }
        .form-group input { width:100%; padding:0.8rem; border:1px solid #cbd5e1; border-radius:12px; }
        .tbl { width:100%; border-collapse:collapse; }
        .tbl th, .tbl td { padding:0.9rem 0.75rem; border-bottom:1px solid #e2e8f0; text-align:left; }
        .tbl th { background:#f8fafc; color:#475569; }
        .badge { padding:0.35rem 0.8rem; border-radius:999px; font-size:0.85rem; font-weight:700; }
        .status-confirmed { background:#d1fae5; color:#166534; }
        .status-pending { background:#fef3c7; color:#92400e; }
        .status-cancelled { background:#fee2e2; color:#b91c1c; }
        .message { background:#e0f2fe; color:#0369a1; border-radius:18px; padding:0.95rem 1rem; margin-bottom:1rem; }
        @media (max-width:680px) { .tbl, .tbl thead, .tbl tbody, .tbl th, .tbl td, .tbl tr { display:block; } .tbl th { display:none; } .tbl td { position:relative; padding-left:50%; } .tbl td::before { position:absolute; top:0.9rem; left:0.85rem; width:calc(50% - 1.7rem); color:#475569; font-weight:700; } .tbl td:nth-of-type(1)::before { content:'Doctor'; } .tbl td:nth-of-type(2)::before { content:'Service'; } .tbl td:nth-of-type(3)::before { content:'Date'; } .tbl td:nth-of-type(4)::before { content:'Time'; } .tbl td:nth-of-type(5)::before { content:'Status'; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">Cancel or Reschedule</div>
                <p style="margin:0.5rem 0 0;color:#64748b;">Manage your appointments quickly from a single page.</p>
            </div>
            <a href="my_booking.php" class="btn"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['service_name'] ?? 'General'); ?></td>
                                <td><?php echo date('d M Y', strtotime($app['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($app['time'])); ?></td>
                                <td><span class="badge status-<?php echo strtolower($app['status']); ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="card" style="margin-top:1.5rem;">
                    <h3 style="margin-top:0;">Reschedule Appointment</h3>
                    <form method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Appointment</label>
                                <select name="appointment_id" required>
                                    <option value="">Select appointment</option>
                                    <?php foreach ($appointments as $app): ?>
                                        <option value="<?php echo (int)$app['id']; ?>"><?php echo htmlspecialchars($app['doctor_name'] . ' - ' . date('d M Y', strtotime($app['date'])) . ' @ ' . date('h:i A', strtotime($app['time']))); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>New Date</label>
                                <input type="date" name="new_date" required>
                            </div>
                            <div class="form-group">
                                <label>New Time</label>
                                <input type="time" name="new_time" required>
                            </div>
                        </div>
                        <input type="hidden" name="action" value="reschedule">
                        <button type="submit" class="btn" style="margin-top:1rem;"><i class="fas fa-sync-alt"></i> Reschedule Appointment</button>
                    </form>
                </div>

                <div class="card" style="margin-top:1.5rem;">
                    <h3 style="margin-top:0;">Cancel Appointment</h3>
                    <form method="post">
                        <div class="form-group">
                            <label>Appointment</label>
                            <select name="appointment_id" required>
                                <option value="">Select appointment</option>
                                <?php foreach ($appointments as $app): ?>
                                    <option value="<?php echo (int)$app['id']; ?>"><?php echo htmlspecialchars($app['doctor_name'] . ' - ' . date('d M Y', strtotime($app['date'])) . ' @ ' . date('h:i A', strtotime($app['time']))); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn" style="background:#ef4444;"> <i class="fas fa-trash-alt"></i> Cancel Appointment</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="muted">No active appointments available to cancel or reschedule.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
