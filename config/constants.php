<?php
/**
 * Project constants for INITIAL-D.
 *
 * This file stores fixed project settings that can be reused across
 * different pages, such as the site name, base URL, timezone, and upload paths.
 */

// -----------------------------
// Basic Website Settings
// -----------------------------

// The official name of the website.
define('SITE_NAME', 'INITIAL-D');

// The base path of the project inside the local WAMP server.
define('BASE_URL', '/Project-D/');

// The timezone used for dates and times in the project.
define('TIMEZONE', 'Asia/Kolkata');

// -----------------------------
// Upload Folder Settings
// -----------------------------

// The main folder where uploaded files will be stored.
define('UPLOAD_FOLDER', 'uploads/');

// The folder where race track images uploaded by vendors will be stored.
define('TRACK_IMAGE_FOLDER', 'uploads/tracks/');

// Apply the project timezone immediately so all date and time functions use it.
date_default_timezone_set(TIMEZONE);
