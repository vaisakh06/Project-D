<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Protect this page so only logged-in users can access it.
requireUserLogin();

// Always use the authenticated user ID from the session.
$userId = $_SESSION['user_id'];

// Initialize message variables.
$statusMessage = '';
$successMessage = '';

// Handle POST requests for booking cancellation.
if (isPostRequest()) {
    $bookingIdInput = $_POST['booking_id'] ?? '';
    $action = $_POST['action'] ?? '';

    // Validate booking_id as integer.
    if (!ctype_digit((string) $bookingIdInput) || (int) $bookingIdInput <= 0) {
        $statusMessage = 'Invalid booking ID.';
    } else {
        $bookingId = (int) $bookingIdInput;

        // Validate action against explicit allowlist.
        if ($action !== 'cancel') {
            $statusMessage = 'Invalid action.';
        } else {
            // Conditional UPDATE that verifies ownership and current status.
            $updateSql = "UPDATE bookings SET status = 'Cancelled'
                          WHERE booking_id = ? AND user_id = ? AND status = 'Pending'";
            $updateStatement = mysqli_prepare($connection, $updateSql);

            if ($updateStatement) {
                mysqli_stmt_bind_param($updateStatement, "ii", $bookingId, $userId);
                $updateSuccessful = mysqli_stmt_execute($updateStatement);
                $affectedRows = mysqli_stmt_affected_rows($updateStatement);
                mysqli_stmt_close($updateStatement);

                if ($updateSuccessful && $affectedRows > 0) {
                    redirect('user/bookings.php?cancelled=1');
                    exit;
                } else {
                    $statusMessage = 'Unable to cancel booking. Only pending bookings can be cancelled.';
                }
            } else {
                $statusMessage = 'Something went wrong. Please try again later.';
            }
        }
    }
}

// Fetch bookings belonging to the logged-in user.
$sql = "SELECT b.booking_id, b.booking_reference, rt.track_name,
               b.booking_date, b.start_time, b.end_time, b.total_amount, b.status, b.created_at
        FROM bookings b
        INNER JOIN race_tracks rt ON b.track_id = rt.track_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC";
$bookingStatement = mysqli_prepare($connection, $sql);

if ($bookingStatement) {
    mysqli_stmt_bind_param($bookingStatement, "i", $userId);
    mysqli_stmt_execute($bookingStatement);
    $bookingResult = mysqli_stmt_get_result($bookingStatement);
    mysqli_stmt_close($bookingStatement);
} else {
    $bookingResult = false;
}

// Set the page title before loading the common header.
$pageTitle = 'My Bookings';

// Get success message from URL after redirect.
if (isset($_GET['cancelled']) && $_GET['cancelled'] === '1') {
    $successMessage = 'Booking cancelled successfully.';
}

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="user-bookings">
            <!-- Page header -->
            <div class="page-header">
                <h1>My Bookings</h1>
                <p>View your booking history and manage pending reservations.</p>
            </div>

            <!-- Status message -->
            <?php if ($statusMessage !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Success message -->
            <?php if ($successMessage !== ''): ?>
                <div class="success-message">
                    <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Bookings table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking Reference</th>
                            <th>Track Name</th>
                            <th>Booking Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookingResult && mysqli_num_rows($bookingResult) > 0): ?>
                            <?php while ($booking = mysqli_fetch_assoc($bookingResult)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($booking['track_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($booking['booking_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($booking['start_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($booking['end_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($booking['total_amount'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                            <?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($booking['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="actions">
                                        <?php if ($booking['status'] === 'Pending'): ?>
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                                <button type="submit" name="action" value="cancel" class="btn-small btn-cancel">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="no-action">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="no-data">No bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
?>
