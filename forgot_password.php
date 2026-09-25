<?php
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_value      = trim($_POST['login_value']);
    $password         = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // ====== Validation ======
    if (empty($login_value) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 4) {
        $error = 'Password must be at least 4 characters.';
    } else {
        // ====== Username හෝ Email එකට අදාළ පරිශීලකයා සොයන්න ======
        $stmt = $conn->prepare("SELECT user_id, full_name FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login_value, $login_value);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // ====== මුරපදය යාවත්කාලීන කරන්න ======
            $update = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $update->bind_param("si", $password, $row['user_id']);

            if ($update->execute()) {
                $message = 'Password reset successfully! You can now login with your new password.';
                // Redirect after 3 seconds
                header("Refresh:3; url=../login.php");
            } else {
                $error = 'Failed to update password. Please try again.';
            }
            $update->close();
        } else {
            $error = 'No account found with that username or email.';
        }
        $stmt->close();
    }
}
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
                <i class="fas fa-key"></i>
            </div>
            <h2>Forgot Password?</h2>
            <p class="subtitle">Enter your username or email to reset your password</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <p style="text-align:center;margin-top:0.5rem;color:#64748b;font-size:0.9rem;">
                    Redirecting to login...
                </p>
            <?php else: ?>

            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username or Email</label>
                    <input type="text" name="login_value" class="form-control" placeholder="Enter your username or email" required autofocus>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password (min 4 characters)" required minlength="4">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-redo"></i> Reset Password
                </button>
            </form>

            <?php endif; ?>

            <div class="extra-links">
                <a href="../login.php">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>