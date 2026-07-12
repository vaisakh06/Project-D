<?php
/**
 * Common helper functions for INITIAL-D.
 *
 * This file contains small reusable functions that will be used across
 * registration, login, vendor, booking, and admin pages.
 */
require_once __DIR__ . '/../config/constants.php';

/**
 * Clean user input before using it in the project.
 *
 * This function removes extra spaces, removes backslashes,
 * and converts special HTML characters to safer HTML entities.
 *
 * @param string $data The input value received from a form or request.
 * @return string The cleaned input value.
 */
function sanitizeInput($data)
{
    // Remove unnecessary spaces from the beginning and end of the input.
    $data = trim($data);

    // Remove backslashes from the input.
    $data = stripslashes($data);

    // Convert special HTML characters to prevent unwanted HTML output.
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    // Return the cleaned input.
    return $data;
}

/**
 * Redirect the browser to another page in the project.
 *
 * This function uses BASE_URL from config/constants.php so redirects
 * stay consistent throughout the project.
 *
 * @param string $location The page path after BASE_URL.
 * @return void
 */
function redirect($location)
{
    // Remove a leading slash so BASE_URL and location join correctly.
    $location = ltrim($location, '/');

    // Send the browser to the requested project page.
    header('Location: ' . BASE_URL . $location);

    // Stop the script so no extra code runs after the redirect.
    exit();
}

/**
 * Check whether the current request method is POST.
 *
 * This is useful before processing forms because most forms will
 * submit data using the POST method.
 *
 * @return bool True if the request method is POST, otherwise false.
 */
function isPostRequest()
{
    // Return true only when the page request was made using POST.
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Display a simple styled message.
 *
 * Supported message types are success, error, warning, and info.
 *
 * @param string $message The message text to display.
 * @param string $type The message type. Default value is success.
 * @return void
 */
function displayMessage($message, $type = 'success')
{
    // Only allow the four supported message types.
    $allowedTypes = ['success', 'error', 'warning', 'info'];

    // Use info if an unsupported message type is provided.
    if (!in_array($type, $allowedTypes, true)) {
        $type = 'info';
    }

    // Clean the message before displaying it on the page.
    $message = sanitizeInput($message);

    // Display a simple HTML message box. CSS styling can be added later.
    echo '<div class="message message-' . $type . '">' . $message . '</div>';
}
