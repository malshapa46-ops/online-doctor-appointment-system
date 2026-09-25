<?php
require_once 'db.php';

// Only allow admin (optional) — keep simple for local use
if (!isset($_SESSION['user_id']) /*|| $_SESSION['role'] !== 'admin'*/) {
    // allow running from CLI where session isn't set
}

$sample = [
    ['name'=>'Dr. Amali Perera','service_name'=>'General Medicine','contact_no'=>'0712345671','email'=>'amali.perera@example.com'],
    ['name'=>'Dr. Nimal Fernando','service_name'=>'Cardiology','contact_no'=>'0712345672','email'=>'nimal.fernando@example.com'],
    ['name'=>'Dr. Sunitha Jayasuriya','service_name'=>'Dermatology','contact_no'=>'0712345673','email'=>'sunitha.j@example.com'],
    ['name'=>'Dr. Kasun Silva','service_name'=>'Orthopedics','contact_no'=>'0712345674','email'=>'kasun.silva@example.com'],
    ['name'=>'Dr. Malini Rajapaksa','service_name'=>'Pediatrics','contact_no'=>'0712345675','email'=>'malini.raj@example.com'],
    ['name'=>'Dr. Roshan Kumara','service_name'=>'ENT','contact_no'=>'0712345676','email'=>'roshan.k@example.com'],
    ['name'=>'Dr. Priyanka Wickramasinghe','service_name'=>'Gynecology','contact_no'=>'0712345677','email'=>'priyanka.w@example.com'],
    ['name'=>'Dr. Kithsiri Perera','service_name'=>'Neurology','contact_no'=>'0712345678','email'=>'kithsiri.p@example.com'],
    ['name'=>'Dr. Anusha Wickramasinghe','service_name'=>'Endocrinology','contact_no'=>'0712345679','email'=>'anusha.w@example.com'],
    ['name'=>'Dr. Chanaka Silva','service_name'=>'Urology','contact_no'=>'0712345680','email'=>'chanaka.s@example.com'],
    ['name'=>'Dr. Ruwani Dias','service_name'=>'Ophthalmology','contact_no'=>'0712345681','email'=>'ruwani.d@example.com'],
    ['name'=>'Dr. Mahesh Goonetilleke','service_name'=>'Psychiatry','contact_no'=>'0712345682','email'=>'mahesh.g@example.com'],
    ['name'=>'Dr. Shehan Perera','service_name'=>'Radiology','contact_no'=>'0712345683','email'=>'shehan.p@example.com'],
    ['name'=>'Dr. Dilani Senanayake','service_name'=>'Rheumatology','contact_no'=>'0712345684','email'=>'dilani.s@example.com'],
    ['name'=>'Dr. Lasantha Kumara','service_name'=>'Gastroenterology','contact_no'=>'0712345685','email'=>'lasantha.k@example.com'],
    ['name'=>'Dr. Nadeeka Silva','service_name'=>'Oncology','contact_no'=>'0712345686','email'=>'nadeeka.s@example.com'],
    ['name'=>'Dr. Chathurika Perera','service_name'=>'Nephrology','contact_no'=>'0712345687','email'=>'chathurika.p@example.com'],
    ['name'=>'Dr. Rohan Wijesuriya','service_name'=>'Pulmonology','contact_no'=>'0712345688','email'=>'rohan.w@example.com'],
];

// ensure service names map to IDs
$service_map = [];
$res = $conn->query("SELECT service_id, service_name FROM service");
while ($r = $res->fetch_assoc()) {
    $service_map[strtolower($r['service_name'])] = $r['service_id'];
}

$inserted = 0;
foreach ($sample as $s) {
    $sname = strtolower($s['service_name']);
    $service_id = $service_map[$sname] ?? null;
    if (!$service_id) {
        // try to create service if missing
        $stmt = $conn->prepare("INSERT INTO service (service_name) VALUES (?)");
        $stmt->bind_param('s', $s['service_name']);
        $stmt->execute();
        $service_id = $conn->insert_id;
        $stmt->close();
        $service_map[$sname] = $service_id;
    }

    // check duplicate by email
    $stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $s['email']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO doctor (name, service_id, contact_no, email, hospital) VALUES (?, ?, ?, ?, 'Nawodya Hospital')");
        $stmt->bind_param('siss', $s['name'], $service_id, $s['contact_no'], $s['email']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) $inserted++;
        $stmt->close();
    } else {
        $stmt->close();
    }
}

echo "Seed complete. Inserted: $inserted\n";
