<?php
require_once 'db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $name = trim($_POST['name']);
    $service_id = intval($_POST['service_id']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO doctor (name, service_id, contact_no, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $name, $service_id, $contact, $email);
        $stmt->execute();
        $stmt->close();
        header('Location: doctors.php?success=Doctor added successfully');
        exit;
    }
}

$doctors = [];
$res = $conn->query("SELECT d.*, s.service_name FROM doctor d LEFT JOIN service s ON d.service_id = s.service_id ORDER BY d.name");
while ($row = $res->fetch_assoc()) $doctors[] = $row;

$services = [];
$sres = $conn->query("SELECT service_id, service_name FROM service");
while ($s = $sres->fetch_assoc()) $services[] = $s;

$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors – DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ====== MODERN NAVBAR ====== */
        .navbar-modern {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            padding: 0.6rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.04);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .navbar-modern .brand {
            font-size: 1.7rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-modern .brand i { color: #2563eb; font-size: 1.8rem; }
        .navbar-modern .brand span { color: #2563eb; }
        .navbar-modern .nav-center {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            flex-wrap: wrap;
        }
        .navbar-modern .nav-center a {
            font-weight: 500;
            color: #475569;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-modern .nav-center a i { font-size: 1rem; }
        .navbar-modern .nav-center a:hover {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.06);
        }
        .navbar-modern .nav-center a.active {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.08);
            font-weight: 600;
        }
        .navbar-modern .nav-right {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .navbar-modern .profile-dropdown {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(0,0,0,0.02);
            padding: 0.3rem 0.8rem 0.3rem 0.5rem;
            border-radius: 60px;
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .navbar-modern .profile-dropdown:hover {
            background: rgba(37, 99, 235, 0.04);
            border-color: rgba(37, 99, 235, 0.15);
        }
        .navbar-modern .profile-dropdown .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .navbar-modern .profile-dropdown .user-name {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.9rem;
        }
        .navbar-modern .admin-badge-nav {
            background: rgba(37, 99, 235, 0.08);
            color: #2563eb;
            padding: 0.2rem 0.8rem;
            border-radius: 60px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid rgba(37, 99, 235, 0.12);
        }
        .navbar-modern .btn-logout {
            background: #ef4444;
            color: #fff !important;
            padding: 0.4rem 1.2rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            border: none;
            cursor: pointer;
        }
        .navbar-modern .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }
        .navbar-modern .hamburger {
            display: none;
            font-size: 1.5rem;
            color: #0f172a;
            cursor: pointer;
            background: none;
            border: none;
        }

        @media (max-width: 820px) {
            .navbar-modern .hamburger { display: block; }
            .navbar-modern .nav-center {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 0.3rem;
                padding: 0.5rem 0;
                border-top: 1px solid rgba(0,0,0,0.04);
                margin-top: 0.5rem;
            }
            .navbar-modern .nav-center.open { display: flex; }
            .navbar-modern .nav-center a { justify-content: center; padding: 0.6rem; }
            .navbar-modern .nav-right { flex-wrap: wrap; }
            .navbar-modern .admin-badge-nav { display: none; }
        }

        @media (max-width: 480px) {
            .navbar-modern { padding: 0.6rem 1rem; }
            .navbar-modern .brand { font-size: 1.3rem; }
            .navbar-modern .profile-dropdown .user-name { display: none; }
            .navbar-modern .btn-logout { padding: 0.3rem 0.8rem; font-size: 0.8rem; }
        }

        /* ====== PAGE STYLES ====== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .page-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .page-header h2 i { color: #2563eb; }

        /* Form Card */
        .form-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
        }
        .form-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .form-card h3 i { color: #2563eb; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(280px, 1fr)) 320px;
            gap: 1rem;
            align-items: end;
        }
        .form-grid .field-group label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            color: #334155;
            margin-bottom: 0.3rem;
        }
        .form-grid .field-group .form-control {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
            background: #f8fafc;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .form-grid .field-group .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.06);
            background: #ffffff;
        }
        .form-grid .btn-add {
            padding: 0.7rem 1.8rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.95rem;
            background: linear-gradient(135deg, #2563eb, #1a3a7a);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            box-shadow: 0 6px 18px rgba(37,99,235,0.2);
        }
        .form-grid .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(37,99,235,0.35);
        }

        /* Table Card */
        .table-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 1.8rem 1.8rem 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
        }
        .table-card .table-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .table-card .table-title i { color: #2563eb; }
        .table-card .table-title .count {
            background: #eff6ff;
            color: #2563eb;
            padding: 0.1rem 0.7rem;
            border-radius: 60px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 16px;
            overflow: hidden;
        }
        th {
            background: #f1f5f9;
            font-weight: 600;
            color: #0f172a;
            padding: 0.8rem 1.2rem;
            text-align: left;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        td {
            padding: 0.8rem 1.2rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            color: #1e293b;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }

        .empty-state {
            text-align: center;
            padding: 2.5rem 0;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 0.5rem;
            color: #cbd5e1;
        }

        @media (max-width: 768px) {
            .form-card { padding: 1.5rem; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .btn-add { width: 100%; justify-content: center; }
            .table-card { padding: 1.2rem; }
            .page-header h2 { font-size: 1.4rem; }
        }
        @media (max-width: 480px) {
            .form-card { padding: 1rem; }
            th, td { padding: 0.5rem 0.8rem; font-size: 0.8rem; }
        }
    </style>
</head>
<body>

    <!-- ====== MODERN NAVBAR ====== -->
    <nav class="navbar-modern">
        <div class="brand">
            <i class="fas fa-stethoscope"></i>
            DOC.<span>lk</span>
        </div>
        <button class="hamburger" onclick="toggleNav()" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-center" id="navCenter">
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="doctors.php" class="active"><i class="fas fa-user-md"></i> Doctors</a>
            <a href="patients.php"><i class="fas fa-users"></i> Patients</a>
            <a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
            <a href="users.php"><i class="fas fa-user-cog"></i> Users</a>
        </div>
        <div class="nav-right">
            <span class="admin-badge-nav"><i class="fas fa-crown"></i> Admin</span>
            <div class="profile-dropdown">
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            </div>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">

        <!-- ====== PAGE HEADER ====== -->
        <div class="page-header">
            <h2><i class="fas fa-user-md"></i> Manage Doctors</h2>
            <span style="font-size:0.9rem;color:#64748b;">
                <i class="fas fa-users" style="color:#2563eb;"></i> <?php echo count($doctors); ?> doctors registered
            </span>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <!-- ====== ADD DOCTOR FORM ====== -->
        <div class="form-card">
            <h3><i class="fas fa-plus-circle"></i> Add New Doctor</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="field-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Dr. John Doe" required>
                    </div>
                    <div class="field-group">
                        <label><i class="fas fa-tag"></i> Service</label>
                        <select name="service_id" class="form-control">
                            <option value="">— Select —</option>
                            <?php foreach ($services as $srv): ?>
                                <option value="<?php echo $srv['service_id']; ?>"><?php echo htmlspecialchars($srv['service_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="text" name="contact" class="form-control" placeholder="0771234567">
                    </div>
                    <div class="field-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" class="form-control" placeholder="doctor@example.com">
                    </div>
                    <div class="field-group search-group" style="grid-column: 5 / 6;">
                        <label><i class="fas fa-search"></i> Search Doctors</label>
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input type="search" id="doctor-search" class="form-control" placeholder="Search by name, service, phone or email">
                            <button type="button" id="search-clear" class="btn-secondary" title="Clear search" style="padding:0.5rem 0.7rem;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div style="margin-top:0.6rem;">
                            <label style="display:block;font-weight:600;font-size:0.8rem;color:#334155;margin-bottom:0.3rem;">Filter by Service</label>
                            <select id="service-filter" class="form-control">
                                <option value="">— All Services —</option>
                                <?php foreach ($services as $srv): ?>
                                    <option value="<?php echo htmlspecialchars($srv['service_name']); ?>"><?php echo htmlspecialchars($srv['service_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="field-button- style="justify-self: end; grid-column: 5 / 6;">
                        <button type="submit" name="add_doctor" class="btn-add">
                            <i class="fas fa-plus"></i> Add Doctor
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ====== DOCTORS LIST ====== -->
        <div class="table-card">
            <div class="table-title">
                <i class="fas fa-list"></i> Doctor List
                <span class="count"><?php echo count($doctors); ?></span>
            </div>

            <?php if (count($doctors) > 0): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Service</th>
                                <th>Phone</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doc): ?>
                                <tr>
                                    <td><strong>#<?php echo $doc['doctor_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($doc['name']); ?></td>
                                    <td>
                                        <span style="background:#eff6ff;color:#2563eb;padding:0.2rem 0.6rem;border-radius:40px;font-size:0.8rem;">
                                            <?php echo htmlspecialchars($doc['service_name'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc['contact_no'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($doc['email'] ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-md"></i>
                    <p>No doctors found. Add your first doctor above!</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ====== FOOTER ====== -->
    <div class="footer">
        <div class="container" style="padding: 0;">
            <div class="footer-grid">
                <div>
                    <h4>DOC.<span style="color: #2563eb;">lk</span></h4>
                    <p>Your trusted partner for hassle-free doctor channeling. Book appointments instantly with top specialists.</p>

            <script>
                // Client-side search + service filter for the doctors table (with clear button)
                (function(){
                    const search = document.getElementById('doctor-search');
                    const serviceFilter = document.getElementById('service-filter');
                    const clearBtn = document.getElementById('search-clear');
                    if (!search || !serviceFilter) return;
                    const table = document.querySelector('.table-wrap table');
                    if (!table) return;
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    function normalize(text){ return (text||'').toString().trim().toLowerCase(); }

                    function applyFilters(){
                        const q = normalize(search.value);
                        const svc = normalize(serviceFilter.value);
                        rows.forEach(r => {
                            const txt = normalize(r.textContent || '');
                            const svcTextEl = r.querySelector('td:nth-child(3)');
                            const svcText = svcTextEl ? normalize(svcTextEl.textContent) : '';
                            const matchesQuery = q === '' || txt.indexOf(q) !== -1;
                            const matchesService = svc === '' || svcText.indexOf(svc) !== -1;
                            r.style.display = (matchesQuery && matchesService) ? '' : 'none';
                        });
                    }

                    search.addEventListener('input', applyFilters);
                    serviceFilter.addEventListener('change', applyFilters);
                    if (clearBtn) clearBtn.addEventListener('click', function(){ search.value = ''; applyFilters(); search.focus(); });
                })();
            </script>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <a href="#">About Us</a><br>
                    <a href="#">Contact Us</a><br>
                    <a href="#">Terms of Service</a><br>
                    <a href="#">Privacy Policy</a>
                </div>
                <div>
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-map-marker-alt" style="color:#2563eb;"></i> No. 01, Galle Road, Colombo 07</p>
                    <p><i class="fas fa-phone" style="color:#2563eb;"></i> +94 112 345 678</p>
                    <p><i class="fas fa-envelope" style="color:#2563eb;"></i> info@doc.lk</p>
                </div>
                <div>
                    <h4>Follow Us</h4>
                    <p style="font-size: 1.8rem; letter-spacing: 0.5rem;">
                        <i class="fab fa-facebook"></i>
                        <i class="fab fa-twitter"></i>
                        <i class="fab fa-instagram"></i>
                        <i class="fab fa-youtube"></i>
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> DOC.lk. All rights reserved. | Made with <i class="fas fa-heart" style="color:#ef4444;"></i> in Sri Lanka
            </div>
        </div>
    </div>

    <script>
        function toggleNav() {
            document.getElementById('navCenter').classList.toggle('open');
        }
        document.querySelectorAll('.nav-center a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navCenter').classList.remove('open');
            });
        });
    </script>

</body>
</html>