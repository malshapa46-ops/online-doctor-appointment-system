<?php
require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



function sendAppointmentEmail($to_email, $patient_name, $patient_address, $patient_phone, $appointment_no, $doctor_name, $service_name, $app_date, $app_time, $status, $amount = null)
{
    $subject = "Appointment Confirmation - " . $appointment_no;

    // HTML Email Body
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f0f6fc; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
            .header { background: linear-gradient(135deg, #0b2b5c, #1a4a8a); color: #fff; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
            .body { padding: 30px; }
            .apt-number { background: #eff6ff; border: 2px dashed #2563eb; border-radius: 12px; padding: 15px; text-align: center; margin-bottom: 25px; }
            .apt-number .label { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
            .apt-number .value { color: #2563eb; font-size: 26px; font-weight: bold; margin-top: 5px; }
            .section { margin-bottom: 20px; }
            .section h3 { color: #0f172a; font-size: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px; }
            .info-row { display: flex; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
            .info-row .label { color: #64748b; font-size: 13px; width: 40%; }
            .info-row .value { color: #1e293b; font-size: 13px; font-weight: 600; width: 60%; }
            .status-badge { display: inline-block; padding: 4px 14px; border-radius: 40px; font-size: 12px; font-weight: 600; color: #fff; }
            .footer { background: #0f172a; color: #94a3b8; padding: 20px; text-align: center; font-size: 12px; }
            .footer a { color: #2563eb; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>DOC.lk</h1>
                <p>Appointment Confirmation</p>
            </div>
            <div class="body">
                <p>Dear <strong>' . htmlspecialchars($patient_name) . '</strong>,</p>
                <p>Your appointment has been successfully booked. Below are the details:</p>

                <div class="apt-number">
                    <div class="label">Appointment Number</div>
                    <div class="value">' . htmlspecialchars($appointment_no) . '</div>
                </div>

                <div class="section">
                    <h3>👤 Patient Details</h3>
                    <div class="info-row"><span class="label">Name:</span><span class="value">' . htmlspecialchars($patient_name) . '</span></div>
                    <div class="info-row"><span class="label">Address:</span><span class="value">' . htmlspecialchars($patient_address) . '</span></div>
                    <div class="info-row"><span class="label">Phone:</span><span class="value">' . htmlspecialchars($patient_phone) . '</span></div>
                    <div class="info-row"><span class="label">Email:</span><span class="value">' . htmlspecialchars($to_email) . '</span></div>
                </div>

                <div class="section">
                    <h3>🩺 Appointment Details</h3>
                    <div class="info-row"><span class="label">Doctor:</span><span class="value">' . htmlspecialchars($doctor_name) . '</span></div>
                    <div class="info-row"><span class="label">Service:</span><span class="value">' . htmlspecialchars($service_name) . '</span></div>
                    <div class="info-row"><span class="label">Date:</span><span class="value">' . htmlspecialchars($app_date) . '</span></div>
                    <div class="info-row"><span class="label">Time:</span><span class="value">' . htmlspecialchars($app_time) . '</span></div>
                    <div class="info-row"><span class="label">Status:</span><span class="value">
                        <span class="status-badge" style="background:' . ($status == 'Confirmed' ? '#22c55e' : ($status == 'Cancelled' ? '#ef4444' : '#f59e0b')) . ';">' . htmlspecialchars($status) . '</span>
                    </span></div>';

    if ($amount !== null) {
        $body .= '<div class="info-row"><span class="label">Amount Paid:</span><span class="value">LKR ' . number_format($amount, 2) . '</span></div>';
    }

    $body .= '
                </div>

                <p style="margin-top: 25px; color: #475569; font-size: 14px;">
                    Please arrive 15 minutes before your appointment time. If you need to cancel or reschedule, please log in to your account.
                </p>
            </div>
            <div class="footer">
                <p><strong>DOC.lk</strong> - Your trusted healthcare partner</p>
                <p>Hotline: +94 112 345 678 | Email: support@doc.lk</p>
                <p>&copy; ' . date('Y') . ' DOC.lk. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';

    if (MAIL_DEMO_MODE) {
        $log_dir = __DIR__ . '/email_logs/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        $log_file = $log_dir . 'email_' . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $to_email) . '.html';
        file_put_contents($log_file, $body);
        return true;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to_email, $patient_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace('<br>', "\n", $body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error
        $log_dir = __DIR__ . '/email_logs/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        file_put_contents($log_dir . 'error.log', date('Y-m-d H:i:s') . ' | ' . $to_email . ' | ' . $mail->ErrorInfo . "\n", FILE_APPEND);
        return false;
    }
}
?>