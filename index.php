<?php
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set the page title before loading the common header.
$pageTitle = 'Home';

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <!-- Main Content -->
    <div class="container">
        <!-- Hero Section -->
        <section class="hero">
            <h1>Welcome to <?= SITE_NAME ?></h1>
            <p>
                Book premium race tracks, discover exciting driving experiences,
                and manage your bookings with ease.
            </p>
            <a href="<?= BASE_URL . 'tracks.php' ?>">Explore Tracks</a>
        </section>

        <!-- Featured Tracks Section -->
        <section class="featured-tracks">
            <h2>Featured Tracks</h2>
            <p>Featured race tracks will appear here.</p>
        </section>

        <!-- About INITIAL-D Section -->
        <section class="about">
            <h2>About INITIAL-D</h2>
            <p>
                <?= SITE_NAME ?> helps users discover race tracks, book driving sessions,
                and allows vendors to manage their race track listings in one place.
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
