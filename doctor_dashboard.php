<?php
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$doctors = [];
$res = $conn->query("
    SELECT d.doctor_id, d.name, d.contact_no, d.email, s.service_name,
           (SELECT COUNT(*) FROM appointment WHERE doctor_id = d.doctor_id) AS app_count
    FROM doctor d
    LEFT JOIN service s ON d.service_id = s.service_id
");
while ($row = $res->fetch_assoc()) {
    $doctors[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Management – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="brand">DOC.<span>lk</span></div>
        <div class="nav-links">
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2>Doctor Information</h2>
        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Service</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Appointments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['name']); ?></td>
                                <td><?php echo htmlspecialchars($doc['service_name'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($doc['contact_no'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($doc['email'] ?? '—'); ?></td>
                                <td><?php echo $doc['app_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>