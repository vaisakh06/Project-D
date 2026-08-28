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

// Handle POST requests for vendor status actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate POST data.
    $vendorId = $_POST['vendor_id'] ?? '';
    $action = $_POST['action'] ?? '';

    // Validate vendor_id as integer.
    if (!is_numeric($vendorId) || (int)$vendorId <= 0) {
        $statusUpdateMessage = 'Invalid vendor ID.';
    } else {
        // Validate action against explicit allowlist.
        $allowedActions = ['approve', 'block', 'unblock'];
        if (!in_array($action, $allowedActions, true)) {
            $statusUpdateMessage = 'Invalid action.';
        } else {
            // Map actions to status values.
            $statusMap = [
                'approve' => 'Approved',
                'block' => 'Blocked',
                'unblock' => 'Approved',
            ];
            $newStatus = $statusMap[$action];

            // Verify vendor exists before updating.
            $checkSql = "SELECT vendor_id, status FROM vendors WHERE vendor_id = ?";
            $checkStatement = mysqli_prepare($connection, $checkSql);

            if ($checkStatement) {
                mysqli_stmt_bind_param($checkStatement, "i", $vendorId);
                mysqli_stmt_execute($checkStatement);
                mysqli_stmt_bind_result($checkStatement, $existingVendorId, $existingStatus);
                $vendorExists = mysqli_stmt_fetch($checkStatement);
                mysqli_stmt_close($checkStatement);

                if ($vendorExists) {
                    // Skip update if status change would be redundant.
                    if ($existingStatus === $newStatus) {
                        $statusUpdateMessage = 'This vendor already has this status.';
                    } else {
                        // Prepare and execute the status update.
                        $updateSql = "UPDATE vendors SET status = ? WHERE vendor_id = ?";
                        $updateStatement = mysqli_prepare($connection, $updateSql);

                        if ($updateStatement) {
                            mysqli_stmt_bind_param($updateStatement, "si", $newStatus, $vendorId);
                            $updateSuccessful = mysqli_stmt_execute($updateStatement);
                            mysqli_stmt_close($updateStatement);

                            if ($updateSuccessful) {
                                // Redirect after successful status update.
                                redirect('admin/manage_vendors.php?updated=1');
                                exit;
                            } else {
                                $statusUpdateMessage = 'Failed to update vendor status.';
                            }
                        } else {
                            $statusUpdateMessage = 'Something went wrong. Please try again later.';
                        }
                    }
                } else {
                    $statusUpdateMessage = 'Vendor not found.';
                }
            } else {
                $statusUpdateMessage = 'Something went wrong. Please try again later.';
            }
        }
    }
}

// Fetch all vendors from the database.
$sql = "SELECT vendor_id, business_name, owner_name, email, phone, address, status, created_at FROM vendors ORDER BY created_at DESC";
$vendorResult = mysqli_query($connection, $sql);

// Set the page title before loading the common header.
$pageTitle = 'Manage Vendors';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="admin-vendors">
            <!-- Page header -->
            <div class="page-header">
                <h1>Manage Vendors</h1>
                <p>View and manage all vendor accounts in the system.</p>
            </div>

            <!-- Status update message -->
            <?php if ($statusUpdateMessage !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($statusUpdateMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Success message for status update -->
            <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
                <div class="success-message">
                    <p>Vendor status updated successfully.</p>
                </div>
            <?php endif; ?>

            <!-- Vendors table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Business Name</th>
                            <th>Owner Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($vendorResult && mysqli_num_rows($vendorResult) > 0): ?>
                            <?php while ($vendor = mysqli_fetch_assoc($vendorResult)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($vendor['business_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($vendor['owner_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($vendor['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($vendor['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($vendor['address'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($vendor['status']) ?>">
                                            <?= htmlspecialchars($vendor['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($vendor['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="actions">
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="vendor_id" value="<?= $vendor['vendor_id'] ?>">

                                            <?php if ($vendor['status'] === 'Pending'): ?>
                                                <button type="submit" name="action" value="approve" class="btn-small btn-approve">
                                                    Approve
                                                </button>
                                                <button type="submit" name="action" value="block" class="btn-small btn-block">
                                                    Block
                                                </button>
                                            <?php elseif ($vendor['status'] === 'Approved'): ?>
                                                <button type="submit" name="action" value="block" class="btn-small btn-block">
                                                    Block
                                                </button>
                                            <?php elseif ($vendor['status'] === 'Blocked'): ?>
                                                <button type="submit" name="action" value="unblock" class="btn-small btn-unblock">
                                                    Unblock
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-data">No vendors found.</td>
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