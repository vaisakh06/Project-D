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

// Fetch all bookings from the database.
$sql = "SELECT b.booking_id, b.booking_reference, u.full_name AS user_name, rt.track_name, b.booking_date, b.start_time, b.end_time, b.total_amount, b.status, b.created_at
        FROM bookings b
        INNER JOIN users u ON b.user_id = u.user_id
        INNER JOIN race_tracks rt ON b.track_id = rt.track_id
        ORDER BY b.created_at DESC";
$bookingResult = mysqli_query($connection, $sql);

// Set the page title before loading the common header.
$pageTitle = 'Manage Bookings';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="admin-bookings">
            <!-- Page header -->
            <div class="page-header">
                <h1>Manage Bookings</h1>
                <p>View all user bookings and track reservations.</p>
            </div>

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