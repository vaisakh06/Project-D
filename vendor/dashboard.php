<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Protect this page so only logged-in vendors can access it.
if (!isset($_SESSION['vendor_id'], $_SESSION['vendor_role']) || $_SESSION['vendor_role'] !== 'vendor') {
    redirect('vendor/login.php');
}

// Store the vendor name from the session for display on the dashboard.
$vendorName = $_SESSION['vendor_name'] ?? 'Vendor';

// Set the page title before loading the common header.
$pageTitle = 'Vendor Dashboard';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="vendor-dashboard">
            <!-- Dashboard header -->
            <div class="dashboard-header">
                <h1>Welcome, <?= htmlspecialchars($vendorName, ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Manage your race tracks and booking activities from your vendor dashboard.</p>
            </div>

            <!-- Dashboard actions -->
            <div class="dashboard-actions">
                <div class="dashboard-card">
                    <h2>Manage Race Tracks</h2>
                    <p>Add and manage your race track listings.</p>
                    <a href="<?= BASE_URL . 'vendor/tracks.php' ?>">Manage Race Tracks</a>
                </div>

                <div class="dashboard-card">
                    <h2>Manage Availability</h2>
                    <p>Update the opening and closing time for your race tracks.</p>
                    <a href="<?= BASE_URL . 'vendor/availability.php' ?>">Manage Availability</a>
                </div>

                <div class="dashboard-card">
                    <h2>Booking Requests</h2>
                    <p>View and accept or reject booking requests for your race tracks.</p>
                    <a href="<?= BASE_URL . 'vendor/manage_bookings.php' ?>">Manage Bookings</a>
                </div>

                <div class="dashboard-card">
                    <h2>Vendor Profile</h2>
                    <p>View and update your vendor account information.</p>
                    <a href="<?= BASE_URL . 'vendor/profile.php' ?>">My Profile</a>
                </div>

                <div class="dashboard-card">
                    <h2>Logout</h2>
                    <p>End your current vendor session safely.</p>
                    <a href="<?= BASE_URL . 'vendor/logout.php' ?>">Logout</a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
