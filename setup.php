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

echo "<h1>Work Management System - Setup</h1>";
echo "<p>This script will create/update the admin user in the database.</p>";

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
            echo "<p style='color: green;'>✓ Admin user password has been updated successfully!</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
        } else {
            echo "<p style='color: red;'>✗ Error updating admin user: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        // Create new admin user
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES ('admin', ?)");
        $stmt->bind_param("s", $password_hash);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Admin user has been created successfully!</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating admin user: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
    
    echo "<hr>";
    echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this setup.php file after setup for security!</p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure you have imported database.sql first!</p>";
}
?>

