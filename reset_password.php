<?php
require_once 'db.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Verify token
if (empty($token)) {
    header('Location: ../login.php?error=Invalid reset link.');
    exit;
}

// Check token validity
$stmt = $conn->prepare("SELECT user_id, full_name, reset_token_expiry FROM user WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    // Token is valid, show reset form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in both password fields.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($new_password) < 4) {
            $error = 'Password must be at least 4 characters.';
        } else {
            // Update password (plain text for demo – use password_hash() in production)
            $update = $conn->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
            $update->bind_param("si", $new_password, $user_id);
            if ($update->execute()) {
                $success = 'Password reset successfully! You can now login with your new password.';
                // Redirect after 3 seconds
                header("Refresh:3; url=../login.php");
            } else {
                $error = 'Failed to update password. Please try again.';
            }
            $update->close();
        }
    }
} else {
    // Invalid or expired token
    header('Location: ../login.php?error=Reset link is invalid or has expired.');
    exit;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – DOC.lk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="form-card">
            <div class="brand-icon">
                <i class="fas fa-lock-open"></i>
            </div>
            <h2>Reset Your Password</h2>
            <p class="subtitle">Enter your new password below</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <p style="text-align:center;margin-top:1rem;color:#64748b;">Redirecting to login...</p>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="4">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="4">
                    </div>
                    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Reset Password</button>
                </form>
            <?php endif; ?>

            <div class="extra-links">
                <a href="../login.php">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>