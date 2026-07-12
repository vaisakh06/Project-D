<?php
/**
 * Common page header for INITIAL-D.
 *
 * This file starts the HTML document, sets the page title,
 * links the main stylesheet, and opens the body tag.
 */
require_once __DIR__ . '/../config/constants.php';

// Use the project name alone when a page does not provide its own title.
$fullPageTitle = SITE_NAME;

// If a page provides $pageTitle, show it before the project name.
if (isset($pageTitle) && trim($pageTitle) !== '') {
    $fullPageTitle = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | ' . SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $fullPageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
