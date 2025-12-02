<?php
// Simple test file to check if login.php is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent header issues
ob_start();

// Test config file first (before any output)
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    die("✗ config.php not found");
}

// Now output HTML
ob_end_clean();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Login Test</h1>
    
    <?php
    echo "<h2>1. Testing config.php</h2>";
    echo "<span class='success'>✓ config.php exists</span><br>";
    echo "<span class='success'>✓ config.php loaded</span><br>";
    ?>

// Test database connection
echo "<h2>2. Testing database connection</h2>";
try {
    $conn = getDBConnection();
    if ($conn) {
        echo "✓ Database connection successful<br>";
        echo "Database: " . DB_NAME . "<br>";
    } else {
        echo "✗ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test session
echo "<h2>3. Testing session</h2>";
try {
    startSession();
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<span class='success'>✓ Session is active</span><br>";
        echo "Session ID: " . session_id() . "<br>";
    } else {
        echo "<span class='error'>✗ Session is not active</span><br>";
        echo "Session status: " . session_status() . " (0=None, 1=Disabled, 2=Active)<br>";
        if (headers_sent()) {
            echo "<span class='error'>⚠️ Headers already sent - this is the problem!</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span class='error'>✗ Session error: " . $e->getMessage() . "</span><br>";
}

// Test users table
echo "<h2>4. Testing users table</h2>";
try {
    $conn = getDBConnection();
    if ($conn) {
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "✓ Users table exists<br>";
            echo "Number of users: " . $row['count'] . "<br>";
            
            // List users
            $result = $conn->query("SELECT id, username FROM users");
            echo "<h3>Users in database:</h3>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>ID: " . $row['id'] . ", Username: " . htmlspecialchars($row['username']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "✗ Could not query users table: " . $conn->error . "<br>";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test file permissions
echo "<h2>5. Testing file permissions</h2>";
if (is_readable('login.php')) {
    echo "✓ login.php is readable<br>";
} else {
    echo "✗ login.php is not readable<br>";
}

if (is_readable('index.php')) {
    echo "✓ index.php is readable<br>";
} else {
    echo "✗ index.php is not readable<br>";
}

echo "<h2>6. PHP Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Support: " . (function_exists('session_start') ? 'Yes' : 'No') . "<br>";
echo "MySQLi Support: " . (extension_loaded('mysqli') ? 'Yes' : 'No') . "<br>";

echo "<hr>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>

