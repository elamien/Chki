<?php
session_start();

// Clear the session variables
$_SESSION = array();

// Clear the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Clear the remember_user cookie if it exists
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 42000, '/');
}

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit();
?> 