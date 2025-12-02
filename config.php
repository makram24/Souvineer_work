<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'bytebeir_work');
define('DB_PASS', 'Qnw1846abF48@');
define('DB_NAME', 'bytebeir_work_management');

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('IMAGE_UPLOAD_DIR', UPLOAD_DIR . 'images/');
define('VIDEO_UPLOAD_DIR', UPLOAD_DIR . 'videos/');
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime']);

// Session configuration
define('SESSION_NAME', 'work_management_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 hours

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(IMAGE_UPLOAD_DIR)) {
    mkdir(IMAGE_UPLOAD_DIR, 0755, true);
}
if (!file_exists(VIDEO_UPLOAD_DIR)) {
    mkdir(VIDEO_UPLOAD_DIR, 0755, true);
}

// Database connection
function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                error_log("Database connection failed: " . $conn->connect_error);
                return false;
            }
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return false;
        } catch (Error $e) {
            error_log("Database connection error: " . $e->getMessage());
            return false;
        }
    }
    return $conn;
}

// Start session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Only set session name and params if headers haven't been sent
        if (!headers_sent()) {
            session_name(SESSION_NAME);
            session_set_cookie_params(SESSION_LIFETIME);
        }
        if (!session_start()) {
            error_log("Session start failed");
        }
    }
}

// Check if user is authenticated
function isAuthenticated() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Require authentication
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Format date for display
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}
// No closing PHP tag to prevent whitespace issues

