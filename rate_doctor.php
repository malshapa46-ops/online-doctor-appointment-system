<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['rating'], $_POST['review'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $rating = min(5, max(1, (int)$_POST['rating']));
    $review = trim($_POST['review']);

    $stmt = $conn->prepare("SELECT doctor_id FROM appointment WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $appointment_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($doctor_id);
    if ($stmt->fetch()) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO doctor_rating (doctor_id, appointment_id, user_id, rating, review, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('iiiis', $doctor_id, $appointment_id, $user_id, $rating, $review);
        if ($stmt->execute()) {
            $message = 'Thank you! Your doctor rating has been saved.';
        } else {
            $message = 'Unable to save your rating. Please try again.';
        }
        $stmt->close();
    } else {
        $stmt->close();
        $message = 'Invalid appointment selected.';
    }
}

$appointments = [];
$stmt = $conn->prepare(
    "SELECT a.id, a.date, a.time, d.name AS doctor_name, s.service_name "
    . "FROM appointment a "
    . "JOIN doctor d ON a.doctor_id = d.doctor_id "
    . "LEFT JOIN service s ON d.service_id = s.service_id "
    . "WHERE a.user_id = ? AND a.status = 'Confirmed' "
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
    <title>Rate Doctor – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin:0; font-family:'Inter',sans-serif; background:#f8fbff; color:#0f172a; }
        .container { max-width: 960px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        .header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem; }
        .title { font-size:2rem; font-weight:700; margin:0; }
        .btn { display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; background:#2563eb; color:#fff; padding:0.85rem 1.2rem; border-radius:999px; font-weight:600; }
        .card { background:#fff; border-radius:24px; padding:1.5rem; border:1px solid #e2e8f0; box-shadow:0 12px 30px rgba(15,23,42,0.06); }
        .message { background:#d1fae5; color:#134e4a; border-radius:18px; padding:1rem 1.2rem; margin-bottom:1.25rem; }
        .form-group { margin-bottom:1rem; }
        .form-group label { display:block; margin-bottom:0.5rem; font-weight:600; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:0.95rem 1rem; border:1px solid #cbd5e1; border-radius:14px; font-size:1rem; }
        .form-group textarea { min-height:140px; resize:vertical; }
        .rating-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:1rem; align-items:center; }
        .rating-option { display:flex; align-items:center; gap:0.5rem; }
        .rating-option input { accent-color:#2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">Rate Your Doctor</div>
                <p style="margin:0.5rem 0 0;color:#64748b;">Share feedback for your recent appointment so we can improve care quality.</p>
            </div>
            <a href="my_booking.php" class="btn"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="card">
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (count($appointments) > 0): ?>
                <form method="post">
                    <div class="form-group">
                        <label for="appointment_id">Select Appointment</label>
                        <select id="appointment_id" name="appointment_id" required>
                            <option value="">Choose appointment</option>
                            <?php foreach ($appointments as $app): ?>
                                <option value="<?php echo (int)$app['id']; ?>">
                                    <?php echo htmlspecialchars($app['doctor_name'] . ' / ' . ($app['service_name'] ?? 'General') . ' – ' . date('d M Y', strtotime($app['date'])) . ' @ ' . date('h:i A', strtotime($app['time']))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Rating</label>
                        <div class="rating-row">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="rating-option">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                    <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="review">Review</label>
                        <textarea id="review" name="review" placeholder="How was your appointment?" required></textarea>
                    </div>

                    <button type="submit" class="btn"><i class="fas fa-star"></i> Submit Rating</button>
                </form>
            <?php else: ?>
                <p style="margin:0;color:#64748b;">No confirmed appointments available for rating yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
