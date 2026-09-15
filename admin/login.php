<?php
// Thin wrapper that forwards the old admin login URL to the common login page.
// This keeps existing links, bookmarks, and role guards working.
require_once '../includes/functions.php';

// Preserve the logout flag across the redirect.
$query = ['role' => 'admin'];

if (isset($_GET['logged_out'])) {
    $query['logged_out'] = $_GET['logged_out'];
}

redirect('login.php?' . http_build_query($query));
