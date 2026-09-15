<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Protect this page so only logged-in users can access it.
requireUserLogin();

// Set the page title before loading the common header.
$pageTitle = 'Dashboard';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="user-dashboard">
            <!-- Dashboard header -->
            <div class="dashboard-header">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p>
                    You can manage your race track activity from this dashboard.
                </p>
            </div>

            <!-- Dashboard actions -->
            <div class="dashboard-actions">
                <div class="dashboard-card">
                    <h2>Browse Tracks</h2>
                    <p>Explore available race tracks and driving experiences.</p>
                    <a href="<?= BASE_URL . 'tracks.php' ?>">Browse Tracks</a>
                </div>

                <div class="dashboard-card">
                    <h2>My Bookings</h2>
                    <p>View your booking history and manage pending reservations.</p>
                    <a href="<?= BASE_URL . 'user/bookings.php' ?>">Booking History</a>
                </div>

                <div class="dashboard-card">
                    <h2>Profile</h2>
                    <p>View and update your account information.</p>
                    <a href="<?= BASE_URL . 'user/profile.php' ?>">My Profile</a>
                </div>

                <div class="dashboard-card">
                    <h2>Logout</h2>
                    <p>End your current login session safely.</p>
                    <a href="<?= BASE_URL . 'user/logout.php' ?>">Logout</a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
