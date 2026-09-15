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

// Initialize message variables.
$statusMessage = '';
$successMessage = '';

// Handle POST requests for booking status actions.
if (isPostRequest()) {
    $bookingIdInput = $_POST['booking_id'] ?? '';
    $action = $_POST['action'] ?? '';

    // Validate booking_id as integer.
    if (!ctype_digit((string) $bookingIdInput) || (int) $bookingIdInput <= 0) {
        $statusMessage = 'Invalid booking ID.';
    } else {
        $bookingId = (int) $bookingIdInput;

        // Validate action against explicit allowlist.
        $allowedActions = ['accept', 'reject'];
        if (!in_array($action, $allowedActions, true)) {
            $statusMessage = 'Invalid action.';
        } else {
            // Map actions to status values.
            $statusMap = [
                'accept' => 'Accepted',
                'reject' => 'Rejected',
            ];
            $newStatus = $statusMap[$action];

            // Verify booking exists and belongs to a track owned by this vendor.
            $checkSql = "SELECT b.booking_id, b.status
                         FROM bookings b
                         INNER JOIN race_tracks rt ON b.track_id = rt.track_id
                         WHERE b.booking_id = ? AND rt.vendor_id = ?
                         LIMIT 1";
            $checkStatement = mysqli_prepare($connection, $checkSql);

            if ($checkStatement) {
                mysqli_stmt_bind_param($checkStatement, "ii", $bookingId, $vendorId);
                mysqli_stmt_execute($checkStatement);
                mysqli_stmt_bind_result($checkStatement, $existingBookingId, $currentStatus);
                $bookingExists = mysqli_stmt_fetch($checkStatement);
                mysqli_stmt_close($checkStatement);

                if ($bookingExists) {
                    // Only allow status changes on Pending bookings.
                    if ($currentStatus !== 'Pending') {
                        $statusMessage = 'Only pending bookings can be accepted or rejected.';
                    } else {
                        // Conditional UPDATE that verifies the current status.
                        $updateSql = "UPDATE bookings SET status = ? WHERE booking_id = ? AND status = ?";
                        $updateStatement = mysqli_prepare($connection, $updateSql);

                        if ($updateStatement) {
                            mysqli_stmt_bind_param($updateStatement, "sis", $newStatus, $bookingId, $currentStatus);
                            $updateSuccessful = mysqli_stmt_execute($updateStatement);
                            $affectedRows = mysqli_stmt_affected_rows($updateStatement);
                            mysqli_stmt_close($updateStatement);

                            if ($updateSuccessful && $affectedRows > 0) {
                                redirect('vendor/manage_bookings.php?updated=1');
                                exit;
                            } else {
                                $statusMessage = 'Unable to update booking status.';
                            }
                        } else {
                            $statusMessage = 'Something went wrong. Please try again later.';
                        }
                    }
                } else {
                    $statusMessage = 'Booking not found.';
                }
            } else {
                $statusMessage = 'Something went wrong. Please try again later.';
            }
        }
    }
}

// Fetch bookings for tracks owned by the logged-in vendor.
$sql = "SELECT b.booking_id, b.booking_reference, u.full_name AS user_name, rt.track_name,
               b.booking_date, b.start_time, b.end_time, b.total_amount, b.status, b.created_at
        FROM bookings b
        INNER JOIN race_tracks rt ON b.track_id = rt.track_id
        INNER JOIN users u ON b.user_id = u.user_id
        WHERE rt.vendor_id = ?
        ORDER BY b.created_at DESC";
$bookingStatement = mysqli_prepare($connection, $sql);

if ($bookingStatement) {
    mysqli_stmt_bind_param($bookingStatement, "i", $vendorId);
    mysqli_stmt_execute($bookingStatement);
    $bookingResult = mysqli_stmt_get_result($bookingStatement);
    mysqli_stmt_close($bookingStatement);
} else {
    $bookingResult = false;
}

// Set the page title before loading the common header.
$pageTitle = 'Manage Bookings';

// Get success message from URL after redirect.
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $successMessage = 'Booking status updated successfully.';
}

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="vendor-bookings">
            <!-- Page header -->
            <div class="page-header">
                <h1>Manage Bookings</h1>
                <p>View and manage booking requests for your race tracks.</p>
            </div>

            <!-- Status update message -->
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
                            <th>User Name</th>
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
                                    <td><?= htmlspecialchars($booking['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
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
                                                <button type="submit" name="action" value="accept" class="btn-small btn-approve">
                                                    Accept
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn-small btn-block">
                                                    Reject
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
                                <td colspan="10" class="no-data">No bookings found.</td>
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
