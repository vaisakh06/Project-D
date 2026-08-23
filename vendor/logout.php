<?php
// Load project constants and helper functions. This also starts the session safely.
require_once '../includes/functions.php';

// Clear all session variables for the current visitor.
$_SESSION = [];

// Remove the session cookie from the browser when PHP is using session cookies.
if (ini_get('session.use_cookies')) {
    $cookieParams = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

// Destroy the current session data on the server.
session_destroy();

// Send the visitor back to the vendor login page with a logout success message.
redirect('vendor/login.php?logged_out=1');
