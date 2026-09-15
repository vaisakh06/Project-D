<?php
// Thin wrapper that forwards the old user registration URL to the common registration page.
// This keeps existing links and bookmarks working.
require_once '../includes/functions.php';

redirect('register.php?type=user');
