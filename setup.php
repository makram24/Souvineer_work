<?php
/**
 * Setup script to create admin user with correct password hash
 * Run this once after importing database.sql
 * DELETE THIS FILE after setup for security!
 */

require_once 'config.php';

// Generate correct password hash for 'admin123'
$password = 'admin123';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<meta name='robots' content='noindex, nofollow'>";
echo "<title>Setup - Work Management</title>";
echo "<link rel='preconnect' href='https://fonts.googleapis.com'>";
echo "<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>";
echo "<link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>";
echo "<link rel='stylesheet' href='styles.css'>";
echo "</head>";
echo "<body class='login-page'>";
echo "<div class='login-container'>";
echo "<div class='login-box'>";
echo "<h1>⚙️ Work Management System</h1>";
echo "<h2>Setup & Configuration</h2>";
echo "<p style='margin-bottom: 24px; color: var(--gray);'>This script will create/update the admin user in the database.</p>";

try {
    $conn = getDBConnection();
    
    // Check if admin user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing admin user
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->bind_param("s", $password_hash);
            if ($stmt->execute()) {
                echo "<div class='message success'>";
                echo "✓ Admin user password has been updated successfully!<br>";
                echo "<strong>Username:</strong> admin<br>";
                echo "<strong>Password:</strong> admin123";
                echo "</div>";
            } else {
                echo "<div class='message error'>✗ Error updating admin user: " . $stmt->error . "</div>";
            }
        $stmt->close();
    } else {
        // Create new admin user
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES ('admin', ?)");
        $stmt->bind_param("s", $password_hash);
        if ($stmt->execute()) {
            echo "<div class='message success'>";
            echo "✓ Admin user has been created successfully!<br>";
            echo "<strong>Username:</strong> admin<br>";
            echo "<strong>Password:</strong> admin123";
            echo "</div>";
        } else {
            echo "<div class='message error'>✗ Error creating admin user: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    
    echo "<div style='margin-top: 24px; padding: 16px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border-left: 4px solid var(--danger);'>";
    echo "<p style='color: var(--danger); margin-bottom: 16px;'><strong>⚠️ IMPORTANT:</strong> Delete this setup.php file after setup for security!</p>";
    echo "<a href='login.php' class='btn btn-primary' style='width: 100%; text-align: center; display: block;'>Go to Login Page</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='message error'>Error: " . $e->getMessage() . "<br>Make sure you have imported database.sql first!</div>";
}
echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";
?>

