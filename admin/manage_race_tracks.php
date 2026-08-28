<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Protect this page so only logged-in admins can access it.
if (!isset($_SESSION['admin_id'], $_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    redirect('admin/login.php');
}

// Initialize status update message
$statusUpdateMessage = '';
$successMessage = '';
$errorMessage = '';

// Handle POST requests for track status actions and deletion.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate POST data.
    $trackId = $_POST['track_id'] ?? '';
    $action = $_POST['action'] ?? '';

    // Validate track_id as integer.
    if (!is_numeric($trackId) || (int)$trackId <= 0) {
        $statusUpdateMessage = 'Invalid track ID.';
    } else {
        $trackId = (int)$trackId;

        // Validate action against explicit allowlist.
        $allowedActions = ['approve', 'reject', 'approve_if_rejected', 'reject_if_approved', 'return_to_pending', 'delete'];
        if (!in_array($action, $allowedActions, true)) {
            $statusUpdateMessage = 'Invalid action.';
        } else {
            // Prepare and execute the requested action.
            $actionResult = processTrackAction($trackId, $action);

            if ($actionResult['success']) {
                $successMessage = $actionResult['message'];
            } else {
                $statusUpdateMessage = $actionResult['message'];
            }
        }
    }
}

// Fetch all tracks from the database.
$sql = "SELECT rt.track_id, rt.track_name, rt.location, rt.address, rt.description, rt.price_per_hour, rt.track_type, rt.opening_time, rt.closing_time, rt.contact_number, rt.status, rt.created_at, v.business_name, v.owner_name
        FROM race_tracks rt
        INNER JOIN vendors v ON rt.vendor_id = v.vendor_id
        ORDER BY rt.created_at DESC";
$tracksResult = mysqli_query($connection, $sql);

// Set the page title before loading the common header.
$pageTitle = 'Manage Race Tracks';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';

function processTrackAction($trackId, $action)
{
    global $connection;
    global $statusUpdateMessage;

    switch ($action) {
        case 'approve':
            $statusSql = "UPDATE race_tracks SET status = 'Approved' WHERE track_id = ? AND status = 'Pending'";
            $statusMessage = 'Track approved successfully.';
            $errorMessage = 'Unable to approve the track. It may already be approved or rejected.';
            break;

        case 'reject':
            $statusSql = "UPDATE race_tracks SET status = 'Rejected' WHERE track_id = ? AND status = 'Pending'";
            $statusMessage = 'Track rejected successfully.';
            $errorMessage = 'Unable to reject the track. It may already be approved or rejected.';
            break;

        case 'approve_if_rejected':
            $statusSql = "UPDATE race_tracks SET status = 'Approved' WHERE track_id = ? AND status = 'Rejected'";
            $statusMessage = 'Track status updated to Approved.';
            $errorMessage = 'Unable to approve the track. It is not in Rejected status.';
            break;

        case 'reject_if_approved':
            $statusSql = "UPDATE race_tracks SET status = 'Rejected' WHERE track_id = ? AND status = 'Approved'";
            $statusMessage = 'Track status updated to Rejected.';
            $errorMessage = 'Unable to reject the track. It is not in Approved status.';
            break;

        case 'return_to_pending':
            $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
            $statusSql = "UPDATE race_tracks SET status = 'Pending' WHERE track_id = ? AND status IN (?, ?, ?)";
            $statusMessage = 'Track status returned to Pending.';
            $errorMessage = 'Unable to return the track to Pending status.';
            break;

        case 'delete':
            return handleTrackDeletion($trackId);

        default:
            return ['success' => false, 'message' => 'Invalid action.'];
    }

    if ($action !== 'delete') {
        $statement = mysqli_prepare($connection, $statusSql);

            if ($statement) {
                if ($action === 'return_to_pending') {
                    $allowedStatus1 = 'Pending';
                    $allowedStatus2 = 'Approved';
                    $allowedStatus3 = 'Rejected';
                    mysqli_stmt_bind_param($statement, "isss", $trackId, $allowedStatus1, $allowedStatus2, $allowedStatus3);
                } else {
                    mysqli_stmt_bind_param($statement, "i", $trackId);
                }

            $updateSuccessful = mysqli_stmt_execute($statement);
            $affectedRows = mysqli_stmt_affected_rows($statement);
            mysqli_stmt_close($statement);

            if ($updateSuccessful) {
                if ($affectedRows > 0) {
                    return ['success' => true, 'message' => $statusMessage];
                } else {
                    return ['success' => false, 'message' => $errorMessage];
                }
            } else {
                return ['success' => false, 'message' => $errorMessage];
            }
        } else {
            return ['success' => false, 'message' => 'Something went wrong. Please try again later.'];
        }
    }

    return ['success' => false, 'message' => 'Something went wrong. Please try again later.'];
}

function handleTrackDeletion($trackId)
{
    global $connection;

    // First check if the track has existing bookings.
    $bookingSql = "SELECT booking_id FROM bookings WHERE track_id = ? LIMIT 1";
    $bookingStatement = mysqli_prepare($connection, $bookingSql);

    if ($bookingStatement) {
        mysqli_stmt_bind_param($bookingStatement, "i", $trackId);
        mysqli_stmt_execute($bookingStatement);
        mysqli_stmt_store_result($bookingStatement);

        if (mysqli_stmt_num_rows($bookingStatement) > 0) {
            mysqli_stmt_close($bookingStatement);
            return ['success' => false, 'message' => 'This track cannot be deleted because it has existing bookings.'];
        }

        mysqli_stmt_close($bookingStatement);
    }

    // Verify the track exists before deleting.
    $checkSql = "SELECT track_id FROM race_tracks WHERE track_id = ? LIMIT 1";
    $checkStatement = mysqli_prepare($connection, $checkSql);

    if ($checkStatement) {
        mysqli_stmt_bind_param($checkStatement, "i", $trackId);
        mysqli_stmt_execute($checkStatement);
        mysqli_stmt_store_result($checkStatement);

        if (mysqli_stmt_num_rows($checkStatement) === 0) {
            mysqli_stmt_close($checkStatement);
            return ['success' => false, 'message' => 'Track not found.'];
        }

        mysqli_stmt_close($checkStatement);
    }

    // Delete the track.
    $deleteSql = "DELETE FROM race_tracks WHERE track_id = ?";
    $deleteStatement = mysqli_prepare($connection, $deleteSql);

    if ($deleteStatement) {
        mysqli_stmt_bind_param($deleteStatement, "i", $trackId);

        $trackDeleted = mysqli_stmt_execute($deleteStatement);
        $affectedRows = mysqli_stmt_affected_rows($deleteStatement);

        mysqli_stmt_close($deleteStatement);

        if ($trackDeleted && $affectedRows > 0) {
            return ['success' => true, 'message' => 'Track deleted successfully.'];
        } else {
            return ['success' => false, 'message' => 'Unable to delete the track. Please try again later.'];
        }
    } else {
        return ['success' => false, 'message' => 'Unable to delete the track. Please try again later.'];
    }
}

// Get success message from URL after redirect.
if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $successMessage = 'Track deleted successfully.';
} elseif (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $successMessage = 'Track status updated successfully.';
}
?>

<main>
    <div class="container">
        <section class="admin-tracks">
            <!-- Page header -->
            <div class="page-header">
                <h1>Manage Race Tracks</h1>
                <p>Review, approve, reject, or manage all race track listings in the system.</p>
            </div>

            <!-- Status update message -->
            <?php if ($statusUpdateMessage !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($statusUpdateMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Success message for actions -->
            <?php if ($successMessage !== ''): ?>
                <div class="success-message">
                    <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Tracks table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Track Name</th>
                            <th>Location</th>
                            <th>Vendor</th>
                            <th>Owner</th>
                            <th>Price/Hour</th>
                            <th>Type</th>
                            <th>Opening Time</th>
                            <th>Closing Time</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tracksResult && mysqli_num_rows($tracksResult) > 0): ?>
                            <?php while ($track = mysqli_fetch_assoc($tracksResult)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($track['location'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($track['business_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($track['owner_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($track['price_per_hour'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($track['track_type'] !== null && $track['track_type'] !== '' ? $track['track_type'] : 'Not provided', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($track['opening_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($track['closing_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($track['status']) ?>">
                                            <?= htmlspecialchars($track['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($track['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="actions">
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="track_id" value="<?= $track['track_id'] ?>">

                                            <?php if ($track['status'] === 'Pending'): ?>
                                                <button type="submit" name="action" value="approve" class="btn-small btn-approve">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn-small btn-block">Reject</button>
                                            <?php elseif ($track['status'] === 'Approved'): ?>
                                                <button type="submit" name="action" value="reject_if_approved" class="btn-small btn-block">Reject</button>
                                                <button type="submit" name="action" value="return_to_pending" class="btn-small btn-info">Return to Pending</button>
                                            <?php elseif ($track['status'] === 'Rejected'): ?>
                                                <button type="submit" name="action" value="approve_if_rejected" class="btn-small btn-approve">Approve</button>
                                                <button type="submit" name="action" value="return_to_pending" class="btn-small btn-info">Return to Pending</button>
                                            <?php endif; ?>

                                            <button type="submit" name="action" value="delete" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this track?');">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="no-data">No race tracks found.</td>
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