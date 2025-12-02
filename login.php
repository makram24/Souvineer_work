<?php
require_once 'config.php';
startSession();

// Redirect if already logged in
if (isAuthenticated()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid username or password. If this is your first time, please run setup.php to configure the admin account.';
                }
            } else {
                $error = 'Invalid username or password. If this is your first time, please run setup.php to configure the admin account.';
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Database error. Please check your database connection.';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login - Work Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Work Management System</h1>
            <h2>Login</h2>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!file_exists('setup.php') || (file_exists('setup.php') && filesize('setup.php') > 0)): ?>
                <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 12px;">
                    <strong>First time setup?</strong> Make sure you've imported database.sql and run <a href="setup.php" style="color: #856404; text-decoration: underline;">setup.php</a> to configure the admin account.
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>

