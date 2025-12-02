<?php
require_once 'config.php';
startSession();

// Destroy session
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

header('Location: login.php');
exit;
?>

