<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Protect this page so only logged-in vendors can access it.
if (!isset($_SESSION['vendor_id'], $_SESSION['vendor_role']) || $_SESSION['vendor_role'] !== 'vendor') {
    redirect('vendor/login.php');
}

// Always use the authenticated vendor ID from the session.
$vendorId = $_SESSION['vendor_id'];
$trackId = 0;
$message = '';

// Validate the track ID from the URL before using it in database queries.
$trackIdInput = $_GET['track_id'] ?? '';

if (!ctype_digit($trackIdInput) || (int) $trackIdInput <= 0) {
    $message = 'Race track not found.';
} else {
    $trackId = (int) $trackIdInput;

    // Check that the race track belongs to the logged-in vendor.
    $ownershipSql = "SELECT track_id FROM race_tracks WHERE track_id = ? AND vendor_id = ? LIMIT 1";
    $ownershipStatement = mysqli_prepare($connection, $ownershipSql);

    if ($ownershipStatement) {
        // Bind both track ID and vendor ID to prevent deleting another vendor's track.
        mysqli_stmt_bind_param($ownershipStatement, "ii", $trackId, $vendorId);

        if (mysqli_stmt_execute($ownershipStatement)) {
            mysqli_stmt_store_result($ownershipStatement);

            if (mysqli_stmt_num_rows($ownershipStatement) === 0) {
                $message = 'Race track not found.';
            }
        } else {
            // Do not show internal database errors to vendors.
            $message = 'Unable to delete the race track. Please try again later.';
        }

        // Close the prepared statement after the ownership check.
        mysqli_stmt_close($ownershipStatement);
    } else {
        // Do not show internal database errors to vendors.
        $message = 'Unable to delete the race track. Please try again later.';
    }

    // Check for existing bookings only if the track belongs to this vendor.
    if ($message === '') {
        $bookingSql = "SELECT booking_id FROM bookings WHERE track_id = ? LIMIT 1";
        $bookingStatement = mysqli_prepare($connection, $bookingSql);

        if ($bookingStatement) {
            // Bind the track ID as an integer.
            mysqli_stmt_bind_param($bookingStatement, "i", $trackId);

            if (mysqli_stmt_execute($bookingStatement)) {
                mysqli_stmt_store_result($bookingStatement);

                if (mysqli_stmt_num_rows($bookingStatement) > 0) {
                    $message = 'This race track cannot be deleted because it has existing bookings.';
                }
            } else {
                // Do not show internal database errors to vendors.
                $message = 'Unable to delete the race track. Please try again later.';
            }

            // Close the prepared statement after the booking check.
            mysqli_stmt_close($bookingStatement);
        } else {
            // Do not show internal database errors to vendors.
            $message = 'Unable to delete the race track. Please try again later.';
        }
    }

    // Delete the race track only when ownership is verified and no bookings exist.
    if ($message === '') {
        $deleteSql = "DELETE FROM race_tracks WHERE track_id = ? AND vendor_id = ?";
        $deleteStatement = mysqli_prepare($connection, $deleteSql);

        if ($deleteStatement) {
            // Bind both track ID and vendor ID for ownership protection during deletion.
            mysqli_stmt_bind_param($deleteStatement, "ii", $trackId, $vendorId);

            $trackDeleted = mysqli_stmt_execute($deleteStatement);
            $affectedRows = mysqli_stmt_affected_rows($deleteStatement);

            // Close the prepared statement after the delete query.
            mysqli_stmt_close($deleteStatement);

            if ($trackDeleted && $affectedRows > 0) {
                redirect('vendor/tracks.php?deleted=1');
            }

            // Do not show internal database errors to vendors.
            $message = 'Unable to delete the race track. Please try again later.';
        } else {
            // Do not show internal database errors to vendors.
            $message = 'Unable to delete the race track. Please try again later.';
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Delete Race Track';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="delete-track">
            <!-- Delete race track message -->
            <h1>Delete Race Track</h1>

            <?php if ($message !== '') { ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php } ?>

            <p>
                <a href="<?= BASE_URL . 'vendor/tracks.php' ?>">Back to Race Tracks</a>
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
