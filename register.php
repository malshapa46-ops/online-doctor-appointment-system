<?php
require_once 'db.php';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $contact   = trim($_POST['contact']);
    $address   = trim($_POST['reg_address']);

    if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } else {
        $check = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = 'This username or email is already registered.';
        } else {
            $stmt = $conn->prepare("INSERT INTO user (username, password, full_name, email, contact_no, address, role) VALUES (?, ?, ?, ?, ?, ?, 'patient')");
            $stmt->bind_param("ssssss", $username, $password, $full_name, $email, $contact, $address);
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now log in.';
                header("Refresh:2; url=../login.php");
            } else {
                $error = 'Failed to insert data. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – DOC.lk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="form-card">
            <div class="brand-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Create Account</h2>
            <p class="subtitle">Join DOC.lk and start booking appointments</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="contact" class="form-control">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="reg_address" class="form-control" placeholder="Enter your address">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-primary"><i class="fas fa-user-check"></i> Register</button>
            </form>

            <div class="extra-links">
                Already have an account? <a href="/DOC/login.php" style="font-weight:600; color:#2563eb;">Login</a>
            </div>
        </div>
    </div>
</body>
</html>