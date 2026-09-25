<?php


function generateAppointmentNumber($conn, $appointment_id = null)
{
    $date_part = date('Ymd');

    if ($appointment_id !== null) {
        $number = 'APT-' . $date_part . '-' . str_pad($appointment_id, 4, '0', STR_PAD_LEFT);
        return $number;
    }

    // Fallback: Random number
    do {
        $number = 'APT-' . $date_part . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("SELECT id FROM appointment WHERE appointment_no = ?");
        $stmt->bind_param("s", $number);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $number;
}
?>