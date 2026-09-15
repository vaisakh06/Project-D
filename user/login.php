<?php
// Thin wrapper that forwards the old user login URL to the common login page.
// This keeps existing links, bookmarks, and role guards working.
require_once '../includes/functions.php';

// Preserve status flags such as registered and logged_out across the redirect.
$query = ['role' => 'user'];

if (isset($_GET['registered'])) {
    $query['registered'] = $_GET['registered'];
}

if (isset($_GET['logged_out'])) {
    $query['logged_out'] = $_GET['logged_out'];
}

redirect('login.php?' . http_build_query($query));
