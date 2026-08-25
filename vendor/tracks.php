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
$tracks = [];
$errors = [];
$successMessage = '';

// Show a simple success message after a race track is deleted.
if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $successMessage = 'Race track deleted successfully.';
}

// Retrieve only the race tracks that belong to the logged-in vendor.
$sql = "SELECT track_id, track_name, location, track_type, price_per_hour, opening_time, closing_time, contact_number, status
        FROM race_tracks
        WHERE vendor_id = ?
        ORDER BY created_at DESC";

$statement = mysqli_prepare($connection, $sql);

if ($statement) {
    // Bind the logged-in vendor ID as an integer.
    mysqli_stmt_bind_param($statement, "i", $vendorId);

    // Run the prepared SELECT query.
    if (mysqli_stmt_execute($statement)) {
        // Initialize variables that will receive each database row.
        $trackId = null;
        $trackName = '';
        $location = '';
        $trackType = '';
        $pricePerHour = '';
        $openingTime = '';
        $closingTime = '';
        $contactNumber = '';
        $status = '';

        // Store the selected columns into PHP variables.
        mysqli_stmt_bind_result(
            $statement,
            $trackId,
            $trackName,
            $location,
            $trackType,
            $pricePerHour,
            $openingTime,
            $closingTime,
            $contactNumber,
            $status
        );

        // Build a simple array of this vendor's race tracks.
        while (mysqli_stmt_fetch($statement)) {
            $tracks[] = [
                'track_id' => $trackId,
                'track_name' => $trackName,
                'location' => $location,
                'track_type' => $trackType,
                'price_per_hour' => $pricePerHour,
                'opening_time' => $openingTime,
                'closing_time' => $closingTime,
                'contact_number' => $contactNumber,
                'status' => $status,
            ];
        }
    } else {
        // Do not show internal database errors to vendors.
        $errors[] = 'Unable to load race tracks. Please try again later.';
    }

    // Close the prepared statement after the track listing query.
    mysqli_stmt_close($statement);
} else {
    // Do not show internal database errors to vendors.
    $errors[] = 'Unable to load race tracks. Please try again later.';
}

// Set the page title before loading the common header.
$pageTitle = 'Manage Race Tracks';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="vendor-tracks">
            <!-- Track listing page heading -->
            <div class="tracks-header">
                <h1>Manage Race Tracks</h1>
                <p>View the race tracks added by your vendor account.</p>
                <a href="<?= BASE_URL . 'vendor/add_track.php' ?>" class="btn-primary">Add New Track</a>
            </div>

            <?php if (!empty($errors)) { ?>
                <!-- Database error messages -->
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error) { ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if ($successMessage !== '') { ?>
                <!-- Delete success message -->
                <div class="success-message">
                    <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php } ?>

            <?php if (empty($errors) && empty($tracks)) { ?>
                <!-- Empty track listing message -->
                <p>No race tracks have been added yet.</p>
            <?php } ?>

            <?php if (!empty($tracks)) { ?>
                <!-- Vendor race track listing -->
                <div class="track-list">
                    <?php foreach ($tracks as $track) { ?>
                        <article class="track-item">
                            <h2><?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?></h2>

                            <p>
                                <strong>Location:</strong>
                                <?= htmlspecialchars($track['location'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Track Type:</strong>
                                <?= htmlspecialchars($track['track_type'] !== null && $track['track_type'] !== '' ? $track['track_type'] : 'Not provided', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Price Per Hour:</strong>
                                <?= htmlspecialchars($track['price_per_hour'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Opening Time:</strong>
                                <?= htmlspecialchars($track['opening_time'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Closing Time:</strong>
                                <?= htmlspecialchars($track['closing_time'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Contact Number:</strong>
                                <?= htmlspecialchars($track['contact_number'] !== null && $track['contact_number'] !== '' ? $track['contact_number'] : 'Not provided', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Status:</strong>
                                <?= htmlspecialchars($track['status'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <a href="<?= BASE_URL . 'vendor/edit_track.php?track_id=' . htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>">Edit</a>
                                |
                                <a href="<?= BASE_URL . 'vendor/track_images.php?track_id=' . htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>">Manage Images</a>
                                |
                                <a
                                    href="<?= BASE_URL . 'vendor/delete_track.php?track_id=' . htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>"
                                    onclick="return confirm('Are you sure you want to delete this race track?');"
                                >Delete</a>
                            </p>
                        </article>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
