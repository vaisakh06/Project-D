<?php
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set the page title before loading the common header.
$pageTitle = 'About';

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="about">
            <!-- Page header -->
            <div class="page-header">
                <h1>About INITIAL-D</h1>
            </div>

            <p>
                INITIAL-D is a Race Track Booking Website designed to connect users
                with vendors who provide race tracks for rent. The system allows
                users to search for race tracks, view details, and make bookings online.
            </p>

            <h2>How It Works</h2>
            <p>
                Users can register an account, browse approved race tracks, view
                track details including price, location, operating hours, and images,
                then create a booking for their preferred date and time. Vendors manage
                their track listings, set availability, and review booking requests
                from users. Administrators monitor the entire platform, approve vendors
                and tracks, and manage the system.
            </p>

            <h2>Our Goal</h2>
            <p>
                INITIAL-D aims to simplify the process of finding and booking race tracks
                by providing a centralized online platform. Instead of contacting
                vendors individually, users can compare tracks based on location, price,
                type, and availability, then book directly through the website.
            </p>

            <h2>Technology</h2>
            <p>
                The project is built using HTML5, CSS3, JavaScript, and PHP in procedural
                style with MySQLi for database operations. The backend runs on WAMP
                Server locally. All database queries use prepared statements to prevent
                SQL injection, and all output is escaped with htmlspecialchars to prevent
                XSS attacks.
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
