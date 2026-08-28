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

// Store the admin name from the session for display on the dashboard.
$adminName = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');

// Set the page title before loading the common header.
$pageTitle = 'Admin Dashboard';

// Query database for statistics
try {
    // Get total users
    $sql = "SELECT COUNT(*) FROM users";
    $result = mysqli_query($connection, $sql);
    $stats['total_users'] = mysqli_fetch_row($result)[0] ?? 0;

    // Get total vendors
    $sql = "SELECT COUNT(*) FROM vendors";
    $result = mysqli_query($connection, $sql);
    $stats['total_vendors'] = mysqli_fetch_row($result)[0] ?? 0;

    // Get pending vendors
    $sql = "SELECT COUNT(*) FROM vendors WHERE status = 'Pending'";
    $result = mysqli_query($connection, $sql);
    $stats['pending_vendors'] = mysqli_fetch_row($result)[0] ?? 0;

    // Get total race tracks
    $sql = "SELECT COUNT(*) FROM race_tracks";
    $result = mysqli_query($connection, $sql);
    $stats['total_tracks'] = mysqli_fetch_row($result)[0] ?? 0;

    // Get pending race tracks
    $sql = "SELECT COUNT(*) FROM race_tracks WHERE status = 'Pending'";
    $result = mysqli_query($connection, $sql);
    $stats['pending_tracks'] = mysqli_fetch_row($result)[0] ?? 0;

    // Get total bookings
    $sql = "SELECT COUNT(*) FROM bookings";
    $result = mysqli_query($connection, $sql);
    $stats['total_bookings'] = mysqli_fetch_row($result)[0] ?? 0;

} catch (Exception $e) {
    $stats = [
        'total_users' => 0,
        'total_vendors' => 0,
        'pending_vendors' => 0,
        'total_tracks' => 0,
        'pending_tracks' => 0,
        'total_bookings' => 0,
    ];
}

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="admin-dashboard">
            <!-- Dashboard header -->
            <div class="dashboard-header">
                <h1>Welcome, <?= $adminName ?></h1>
                <p>Manage the INITIAL-D administration area from your admin dashboard.</p>
            </div>

            <!-- Dashboard statistics -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h2><?= $stats['total_users'] ?? '0' ?></h2>
                    <p>Total Users</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stats['total_vendors'] ?? '0' ?></h2>
                    <p>Total Vendors</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stats['pending_vendors'] ?? '0' ?></h2>
                    <p>Pending Vendors</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stats['total_tracks'] ?? '0' ?></h2>
                    <p>Total Race Tracks</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stats['pending_tracks'] ?? '0' ?></h2>
                    <p>Pending Race Tracks</p>
                </div>

                <div class="stat-card">
                    <h2><?= $stats['total_bookings'] ?? '0' ?></h2>
                    <p>Total Bookings</p>
                </div>
            </div>

            <!-- Dashboard actions -->
            <div class="dashboard-actions">
                <div class="dashboard-card">
                    <h2>Manage Vendors</h2>
                    <p>View, approve, block, or unblock vendor accounts.</p>
                    <a href="<?= BASE_URL . 'admin/manage_vendors.php' ?>">Manage Vendors</a>
                </div>

                <div class="dashboard-card">
                    <h2>Manage Race Tracks</h2>
                    <p>Review, approve, reject, or manage race track listings.</p>
                    <a href="<?= BASE_URL . 'admin/manage_race_tracks.php' ?>">Manage Race Tracks</a>
                </div>

                <div class="dashboard-card">
                    <h2>Manage Bookings</h2>
                    <p>View and manage user bookings and track reservations.</p>
                    <span>Coming Soon</span>
                </div>

                <div class="dashboard-card">
                    <h2>Manage Users</h2>
                    <p>View, block, or manage user accounts.</p>
                    <span>Coming Soon</span>
                </div>

                <div class="dashboard-card">
                    <h2>Admin Logout</h2>
                    <p>End your current admin session safely.</p>
                    <a href="<?= BASE_URL . 'admin/logout.php' ?>">Logout</a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
?>