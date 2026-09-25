<?php
require_once 'db.php';

$search = trim($_GET['search'] ?? '');
$specialization = trim($_GET['specialization'] ?? '');
$doctors = [];
$specializations = [];

$service_result = $conn->query("SELECT DISTINCT service_name FROM service WHERE service_name IS NOT NULL AND service_name <> '' ORDER BY service_name");
while ($row = $service_result->fetch_assoc()) {
    $specializations[] = $row['service_name'];
}

$sql = "SELECT d.doctor_id, d.name, d.contact_no, d.email, d.hospital, s.service_name
        FROM doctor d
        LEFT JOIN service s ON d.service_id = s.service_id
        WHERE 1=1";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (d.name LIKE ? OR d.hospital LIKE ? OR s.service_name LIKE ?)";
    $search_value = '%' . $search . '%';
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $types .= 'sss';
}
if ($specialization !== '') {
    $sql .= " AND s.service_name = ?";
    $params[] = $specialization;
    $types .= 's';
}
$sql .= " ORDER BY d.name";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Doctor - DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary:#2563eb; --primary-dark:#1d4ed8; --navy:#0f172a; --muted:#64748b; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--navy); font-family:'Inter',sans-serif; background:radial-gradient(circle at 10% 8%,rgba(37,99,235,.13),transparent 28rem),linear-gradient(135deg,#f8fbff,#eef4ff); }
        .page-wrap { width:min(1160px,calc(100% - 2rem)); margin:0 auto; padding:2.5rem 0 3.5rem; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.5rem; }
        .brand { color:var(--navy); font-size:1.35rem; font-weight:800; letter-spacing:-.04em; text-decoration:none; }
        .brand span { color:var(--primary); }
        .back-link { color:var(--primary-dark); font-size:.85rem; font-weight:700; text-decoration:none; }
        .hero { position:relative; overflow:hidden; padding:clamp(1.6rem,4vw,3rem); border-radius:30px; color:#fff; background:linear-gradient(125deg,#0b2b5c,#2563eb); box-shadow:0 20px 45px rgba(30,64,175,.2); }
        .hero::after { content:''; position:absolute; width:240px; height:240px; right:-70px; top:-105px; border:28px solid rgba(255,255,255,.1); border-radius:50%; }
        .hero-content { position:relative; z-index:1; max-width:760px; }
        .hero-icon { display:inline-flex; align-items:center; justify-content:center; width:54px; height:54px; margin-bottom:1rem; border-radius:17px; color:var(--primary); background:#fff; font-size:1.5rem; }
        .hero h1 { margin:0 0 .7rem; font-size:clamp(1.8rem,4vw,2.8rem); letter-spacing:-.04em; }
        .hero p { margin:0; color:#dbeafe; line-height:1.7; }
        .card { margin-top:1.5rem; padding:clamp(1.3rem,3vw,2rem); border:1px solid rgba(226,232,240,.9); border-radius:24px; background:rgba(255,255,255,.94); box-shadow:0 12px 35px rgba(15,23,42,.06); }
        .search-form { display:grid; grid-template-columns:minmax(0,1fr) minmax(180px,.55fr) auto; gap:.8rem; }
        .search-form input, .search-form select { width:100%; padding:.8rem 1rem; border:1px solid #dbeafe; border-radius:14px; color:#334155; background:#f8fbff; font:inherit; }
        .search-form input:focus, .search-form select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.78rem 1.1rem; color:#fff; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); border:0; border-radius:999px; box-shadow:0 8px 18px rgba(37,99,235,.2); font-size:.85rem; font-weight:700; text-decoration:none; cursor:pointer; }
        .btn.secondary { color:var(--primary-dark); background:#eff6ff; box-shadow:none; border:1px solid #bfdbfe; }
        .results-heading { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin:1.8rem 0 1rem; }
        .results-heading h2 { margin:0; font-size:1.15rem; }
        .count { padding:.35rem .7rem; color:#1e40af; background:#eff6ff; border:1px solid #bfdbfe; border-radius:999px; font-size:.75rem; font-weight:700; }
        .doctor-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:1rem; }
        .doctor-card { display:flex; flex-direction:column; padding:1.2rem; border:1px solid #e2e8f0; border-radius:20px; background:#fff; transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }
        .doctor-card:hover { transform:translateY(-4px); border-color:#bfdbfe; box-shadow:0 12px 28px rgba(37,99,235,.1); }
        .doctor-avatar { display:flex; align-items:center; justify-content:center; width:50px; height:50px; margin-bottom:.8rem; color:#fff; background:linear-gradient(135deg,var(--primary),#1e3a8a); border-radius:16px; font-size:1.25rem; }
        .doctor-card h3 { margin:0 0 .3rem; font-size:1rem; }
        .specialty { display:inline-block; width:fit-content; padding:.3rem .6rem; color:#1d4ed8; background:#eff6ff; border-radius:999px; font-size:.72rem; font-weight:700; }
        .doctor-meta { display:grid; gap:.35rem; margin:1rem 0; color:var(--muted); font-size:.78rem; line-height:1.5; }
        .doctor-meta i { width:16px; color:var(--primary); text-align:center; }
        .doctor-card .btn { margin-top:auto; }
        .empty { padding:2rem 1rem; text-align:center; color:var(--muted); border:1px dashed #bfdbfe; border-radius:18px; background:#f8fbff; }
        @media (max-width:700px) { .page-wrap{padding-top:1.2rem}.search-form{grid-template-columns:1fr}.results-heading{align-items:flex-start;flex-direction:column} }
        @media (max-width:480px) { .page-wrap{width:min(100% - 1rem,1160px)}.hero,.card{border-radius:20px}.hero{padding:1.4rem} }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="topbar">
            <a class="brand" href="main_dashboard.php">DOC.<span>lk</span></a>
            <a class="back-link" href="main_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <header class="hero">
            <div class="hero-content">
                <div class="hero-icon"><i class="fas fa-user-md"></i></div>
                <h1>Find a Doctor</h1>
                <p>Explore the doctors listed in DOC.lk and choose a healthcare professional based on name, specialization, or hospital.</p>
            </div>
        </header>
        <section class="card">
            <form class="search-form" method="GET" action="find_doctor.php">
                <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search doctor, hospital, or service">
                <select name="specialization">
                    <option value="">All Specializations</option>
                    <?php foreach ($specializations as $item): ?>
                        <option value="<?php echo htmlspecialchars($item); ?>" <?php echo $specialization === $item ? 'selected' : ''; ?>><?php echo htmlspecialchars($item); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn"><i class="fas fa-search"></i> Search</button>
            </form>
            <div class="results-heading">
                <h2><i class="fas fa-stethoscope" style="color:var(--primary);margin-right:.4rem;"></i> Available Doctors</h2>
                <span class="count"><?php echo count($doctors); ?> doctor<?php echo count($doctors) === 1 ? '' : 's'; ?> found</span>
            </div>
            <?php if ($doctors): ?>
                <div class="doctor-grid">
                    <?php foreach ($doctors as $doctor): ?>
                        <article class="doctor-card">
                            <div class="doctor-avatar"><i class="fas fa-user-doctor"></i></div>
                            <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                            <span class="specialty"><?php echo htmlspecialchars($doctor['service_name'] ?? 'General Medicine'); ?></span>
                            <div class="doctor-meta">
                                <span><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($doctor['hospital'] ?? 'Available Hospital'); ?></span>
                                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor['contact_no'] ?? 'Contact through support'); ?></span>
                                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($doctor['email'] ?? 'Email unavailable'); ?></span>
                            </div>
                            <a href="../appointments.php?doctor_id=<?php echo (int) $doctor['doctor_id']; ?>" class="btn"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty"><i class="fas fa-user-md" style="font-size:2rem;color:var(--primary);"></i><p>No doctors matched your search. Try another name, hospital, or specialization.</p><a href="find_doctor.php" class="btn secondary">View All Doctors</a></div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
