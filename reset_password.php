<?php
/**
 * Password Reset Utility
 * Use this to reset the admin password if you forget it
 * DELETE THIS FILE after use for security!
 */

require_once 'config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password)) {
        $message = 'Password cannot be empty';
        $messageType = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match';
        $messageType = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Password must be at least 6 characters long';
        $messageType = 'error';
    } else {
        try {
            $conn = getDBConnection();
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $stmt->bind_param("s", $password_hash);
            
            if ($stmt->execute()) {
                $message = 'Password has been reset successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error resetting password: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Reset Password - Work Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Reset Admin Password</h1>
            <h2>⚠️ Security Tool</h2>
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
            <p style="margin-top: 20px; text-align: center;">
                <a href="login.php">Back to Login</a>
            </p>
            <p style="margin-top: 10px; font-size: 12px; color: #dc3545; text-align: center;">
                ⚠️ Delete this file after use!
            </p>
        </div>
    </div>
</body>
</html>

