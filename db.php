<?php
// db.php – Database Connection
$host = 'localhost';
$user = 'root';        // Your MySQL username
$pass = '';            // Your MySQL password
$dbname = 'doctor_booking';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
date_default_timezone_set('Asia/Colombo');
session_start();

// Ensure the user table has a role column for role-based login.
$role_check = $conn->query("SHOW COLUMNS FROM user LIKE 'role'");
if ($role_check && $role_check->num_rows === 0) {
    $conn->query("ALTER TABLE user ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'patient' AFTER email");
}

// Ensure the appointment table has patient detail columns.
$appointment_fields = [
    'patient_name'    => "VARCHAR(255) NOT NULL DEFAULT ''",
    'patient_nic'     => "VARCHAR(50) NOT NULL DEFAULT ''",
    'patient_gender'  => "VARCHAR(30) NOT NULL DEFAULT ''",
    'patient_mobile'  => "VARCHAR(50) NOT NULL DEFAULT ''",
    'patient_email'   => "VARCHAR(255) NOT NULL DEFAULT ''",
    'patient_address' => "VARCHAR(500) NOT NULL DEFAULT ''",
    'payment_method'  => "VARCHAR(50) NOT NULL DEFAULT ''",
    'payment_number'  => "VARCHAR(100) NOT NULL DEFAULT ''",
    'payment_date'    => "DATETIME NULL DEFAULT NULL"
];

foreach ($appointment_fields as $field => $definition) {
    $field_check = $conn->query("SHOW COLUMNS FROM appointment LIKE '$field'");
    if ($field_check && $field_check->num_rows === 0) {
        $conn->query("ALTER TABLE appointment ADD COLUMN $field $definition");
    }
}

// Older pages use a numeric appointment id, while newer records use appointment_no.
// Keep both identifiers available so all booking and management pages remain compatible.
$appointment_id_check = $conn->query("SHOW COLUMNS FROM appointment LIKE 'id'");
if ($appointment_id_check && $appointment_id_check->num_rows === 0) {
    $conn->query("ALTER TABLE appointment ADD COLUMN id INT NOT NULL AUTO_INCREMENT UNIQUE FIRST");
}

// Ensure a doctor_rating table exists for storing patient feedback.
$rating_check = $conn->query("SHOW TABLES LIKE 'doctor_rating'");
if ($rating_check && $rating_check->num_rows === 0) {
    $conn->query("CREATE TABLE doctor_rating (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT NOT NULL,
        appointment_no VARCHAR(38) NOT NULL,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL,
        review TEXT,
        created_at DATETIME NOT NULL,
        INDEX (doctor_id),
        INDEX (appointment_no),
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
?>