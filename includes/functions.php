<?php
/**
 * Common helper functions for INITIAL-D.
 *
 * This file contains small reusable functions that will be used across
 * registration, login, vendor, booking, and admin pages.
 */
require_once __DIR__ . '/../config/constants.php';

// Start the session once so authentication data is available to helper functions and shared layout files.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    // Return the cleaned input. Encoding is applied at output time via htmlspecialchars().
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

/**
 * Check whether a normal user is currently logged in.
 *
 * This function checks the session values created during user login.
 *
 * @return bool True when a valid user session exists, otherwise false.
 */
function isUserLoggedIn()
{
    // A user is logged in only when the user ID exists and the role is user.
    return isset($_SESSION['user_id'], $_SESSION['user_role'])
        && $_SESSION['user_role'] === 'user';
}

/**
 * Require a normal user to be logged in before viewing a page.
 *
 * Future protected user pages can call this function at the top of the file.
 *
 * @return void
 */
function requireUserLogin()
{
    // Send guests to the login page and stop the current page.
    if (!isUserLoggedIn()) {
        redirect('user/login.php');
    }
}

/**
 * Check whether a vendor is currently logged in.
 *
 * This function checks the session values created during vendor login.
 *
 * @return bool True when a valid vendor session exists, otherwise false.
 */
function isVendorLoggedIn()
{
    // A vendor is logged in only when the vendor ID exists and the role is vendor.
    return isset($_SESSION['vendor_id'], $_SESSION['vendor_role'])
        && $_SESSION['vendor_role'] === 'vendor';
}

/**
 * Check whether an admin is currently logged in.
 *
 * This function checks the session values created during admin login.
 *
 * @return bool True when a valid admin session exists, otherwise false.
 */
function isAdminLoggedIn()
{
    // An admin is logged in only when the admin ID exists and the role is admin.
    return isset($_SESSION['admin_id'], $_SESSION['admin_role'])
        && $_SESSION['admin_role'] === 'admin';
}

/**
 * Require a vendor to be logged in before viewing a page.
 *
 * @return void
 */
function requireVendorLogin()
{
    // Send guests to the common login page with the vendor role preselected.
    if (!isVendorLoggedIn()) {
        redirect('login.php?role=vendor');
    }
}

/**
 * Require an admin to be logged in before viewing a page.
 *
 * @return void
 */
function requireAdminLogin()
{
    // Send guests to the common login page with the admin role preselected.
    if (!isAdminLoggedIn()) {
        redirect('login.php?role=admin');
    }
}

/**
 * Validate an uploaded profile image without storing it.
 *
 * Mirrors the validation rules used for race track images: JPG, PNG, and
 * WEBP files up to 2 MB, checked by both extension and MIME type.
 *
 * @param array $file One entry from the $_FILES array.
 * @return array An array with an 'extension' key on success or an 'error' key on failure.
 */
function validateProfileImageUpload($file)
{
    $maxFileSize = 2 * 1024 * 1024;
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

    // Validate that a file was uploaded without errors.
    if (!is_array($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Image upload failed. Please try again.'];
    }

    if (!isset($file['size']) || $file['size'] > $maxFileSize) {
        return ['error' => 'Image size must not be more than 2 MB.'];
    }

    $originalFileName = $file['name'] ?? '';
    $temporaryFilePath = $file['tmp_name'] ?? '';
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

    // Validate the file extension.
    if (!in_array($fileExtension, $allowedExtensions, true)) {
        return ['error' => 'Only JPG, PNG, and WEBP images are allowed.'];
    }

    // Validate the MIME type using PHP's file information functions.
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

    if ($fileInfo === false) {
        return ['error' => 'Unable to validate the uploaded image. Please try again.'];
    }

    $mimeType = finfo_file($fileInfo, $temporaryFilePath);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return ['error' => 'Only JPG, PNG, and WEBP images are allowed.'];
    }

    return ['extension' => $fileExtension];
}

/**
 * Store a validated profile image with a unique filename.
 *
 * @param string $temporaryFilePath The temporary uploaded file path.
 * @param string $fileExtension The validated file extension.
 * @param string $prefix Filename prefix, for example 'user' or 'vendor'.
 * @param int $ownerId The user or vendor ID that owns the image.
 * @param string $uploadDirectory The absolute destination directory.
 * @param string $pathPrefix The relative folder prefix stored in the database.
 * @return array An array with a 'path' key on success or an 'error' key on failure.
 */
function storeProfileImage($temporaryFilePath, $fileExtension, $prefix, $ownerId, $uploadDirectory, $pathPrefix)
{
    if (!is_dir($uploadDirectory)) {
        return ['error' => 'Profile image upload folder is not available.'];
    }

    // Build a unique filename so uploads never overwrite each other.
    $safeFileName = $prefix . '_' . (int) $ownerId . '_' . str_replace('.', '_', uniqid('', true)) . '.' . $fileExtension;
    $storedImagePath = $pathPrefix . $safeFileName;
    $destinationPath = $uploadDirectory . $safeFileName;

    if (!move_uploaded_file($temporaryFilePath, $destinationPath)) {
        return ['error' => 'Unable to upload the profile image. Please try again.'];
    }

    return ['path' => $storedImagePath];
}

/**
 * Insert a new contact message into the database.
 *
 * @param mysqli $connection The database connection.
 * @param string $name The sender's name.
 * @param string $email The sender's email.
 * @param string $subject The message subject.
 * @param string $message The message body.
 * @return array An array with 'success' key on success or 'error' key on failure.
 */
function insertContactMessage($connection, $name, $email, $subject, $message)
{
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
        $inserted = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($inserted) {
            return ['success' => true];
        }
        return ['error' => 'Unable to save your message. Please try again later.'];
    }
    return ['error' => 'Something went wrong. Please try again later.'];
}

/**
 * Fetch all contact messages, ordered by most recent first.
 *
 * @param mysqli $connection The database connection.
 * @return mixed The result set or false on failure.
 */
function getContactMessages($connection)
{
    $sql = "SELECT message_id, name, email, subject, message, status, created_at, updated_at
            FROM contact_messages
            ORDER BY created_at DESC";
    return mysqli_query($connection, $sql);
}

/**
 * Update the status of a contact message.
 *
 * @param mysqli $connection The database connection.
 * @param int $messageId The message ID.
 * @param string $newStatus The new status value.
 * @return array An array with 'success' or 'error' key.
 */
function updateContactMessageStatus($connection, $messageId, $newStatus)
{
    $sql = "UPDATE contact_messages SET status = ? WHERE message_id = ?";
    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $messageId);
        $updated = mysqli_stmt_execute($stmt);
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($updated) {
            return ['success' => true];
        }
        return ['error' => 'Unable to update message status.'];
    }
    return ['error' => 'Something went wrong. Please try again later.'];
}

/**
 * Delete a contact message from the database.
 *
 * @param mysqli $connection The database connection.
 * @param int $messageId The message ID.
 * @return array An array with 'success' or 'error' key.
 */
function deleteContactMessage($connection, $messageId)
{
    $sql = "DELETE FROM contact_messages WHERE message_id = ?";
    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        $deleted = mysqli_stmt_execute($stmt);
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($deleted && $affectedRows > 0) {
            return ['success' => true];
        }
        return ['error' => 'Unable to delete the message.'];
    }
    return ['error' => 'Something went wrong. Please try again later.'];
}

/**
 * Count unread contact messages.
 *
 * @param mysqli $connection The database connection.
 * @return int The number of unread messages.
 */
function getUnreadContactMessageCount($connection)
{
    $sql = "SELECT COUNT(*) FROM contact_messages WHERE status = 'Unread'";
    $result = mysqli_query($connection, $sql);
    if ($result) {
        return (int) mysqli_fetch_row($result)[0];
    }
    return 0;
}

/**
 * Save an admin reply to a contact message and mark it as Replied.
 *
 * @param mysqli $connection The database connection.
 * @param int $messageId The message ID.
 * @param string $reply The admin reply text.
 * @return array An array with 'success' or 'error' key.
 */
function saveContactReply($connection, $messageId, $reply)
{
    $sql = "UPDATE contact_messages SET admin_reply = ?, replied_at = NOW(), status = 'Replied' WHERE message_id = ?";
    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $reply, $messageId);
        $updated = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($updated) {
            return ['success' => true];
        }
        return ['error' => 'Unable to save the reply. Please try again.'];
    }
    return ['error' => 'Something went wrong. Please try again later.'];
}

/**
 * Fetch a single contact message by ID.
 *
 * @param mysqli $connection The database connection.
 * @param int $messageId The message ID.
 * @return array|false The message array or false if not found.
 */
function getContactMessageById($connection, $messageId)
{
    $sql = "SELECT message_id, name, email, subject, message, admin_reply, replied_at, status, created_at, updated_at
            FROM contact_messages
            WHERE message_id = ?
            LIMIT 1";
    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $message = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $message;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}
