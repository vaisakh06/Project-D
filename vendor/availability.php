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

// Create variables to store page data and messages.
$tracks = [];
$errors = [];
$successMessage = '';
$submittedTrackId = 0;
$submittedOpeningTime = '';
$submittedClosingTime = '';

// Show a simple success message after availability is updated.
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $successMessage = 'Track availability updated successfully.';
}

// Check if the availability form was submitted using the POST method.
if (isPostRequest()) {
    // Read and sanitize the submitted track ID and time values.
    $trackIdInput = sanitizeInput($_POST['track_id'] ?? '');
    $openingTime = sanitizeInput($_POST['opening_time'] ?? '');
    $closingTime = sanitizeInput($_POST['closing_time'] ?? '');
    $trackId = 0;

    // Store submitted values so they can be shown again if validation fails.
    $submittedOpeningTime = $openingTime;
    $submittedClosingTime = $closingTime;

    // Validate the hidden track ID before using it in a database query.
    if (!ctype_digit($trackIdInput) || (int) $trackIdInput <= 0) {
        $errors[] = 'Race track not found.';
    } else {
        $trackId = (int) $trackIdInput;
        $submittedTrackId = $trackId;
    }

    // Validate opening time.
    if ($openingTime === '') {
        $errors[] = 'Please enter the opening time.';
    } elseif (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $openingTime)) {
        $errors[] = 'Please enter a valid opening time.';
    }

    // Validate closing time.
    if ($closingTime === '') {
        $errors[] = 'Please enter the closing time.';
    } elseif (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $closingTime)) {
        $errors[] = 'Please enter a valid closing time.';
    }

    // Make sure the track closes after it opens.
    if (
        $openingTime !== ''
        && $closingTime !== ''
        && preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $openingTime)
        && preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $closingTime)
        && $closingTime <= $openingTime
    ) {
        $errors[] = 'Closing time must be later than opening time.';
    }

    // Update availability only after validation passes.
    if (empty($errors)) {
        // Verify that the submitted track belongs to the logged-in vendor.
        $ownershipSql = "SELECT track_id FROM race_tracks WHERE track_id = ? AND vendor_id = ? LIMIT 1";
        $ownershipStatement = mysqli_prepare($connection, $ownershipSql);

        if ($ownershipStatement) {
            // Bind both IDs so a vendor cannot update another vendor's track.
            mysqli_stmt_bind_param($ownershipStatement, "ii", $trackId, $vendorId);

            if (mysqli_stmt_execute($ownershipStatement)) {
                mysqli_stmt_store_result($ownershipStatement);

                if (mysqli_stmt_num_rows($ownershipStatement) === 0) {
                    $errors[] = 'Race track not found.';
                }
            } else {
                // Do not show internal database errors to vendors.
                $errors[] = 'Unable to update track availability. Please try again later.';
            }

            // Close the prepared statement after the ownership check.
            mysqli_stmt_close($ownershipStatement);
        } else {
            // Do not show internal database errors to vendors.
            $errors[] = 'Unable to update track availability. Please try again later.';
        }
    }

    // Update availability only after validation and ownership verification pass.
    if (empty($errors)) {
        // Update only opening and closing time, and keep status unchanged.
        $updateSql = "UPDATE race_tracks
            SET opening_time = ?,
                closing_time = ?
            WHERE track_id = ?
            AND vendor_id = ?";

        $updateStatement = mysqli_prepare($connection, $updateSql);

        if ($updateStatement) {
            // Bind the validated time values, track ID, and logged-in vendor ID.
            mysqli_stmt_bind_param($updateStatement, "ssii", $openingTime, $closingTime, $trackId, $vendorId);

            // Run the UPDATE query and check whether the database accepted it.
            $availabilityUpdated = mysqli_stmt_execute($updateStatement);

            // Close the prepared statement after the update query.
            mysqli_stmt_close($updateStatement);

            if ($availabilityUpdated) {
                redirect('vendor/availability.php?updated=1');
            }

            // Do not show internal database errors to vendors.
            $errors[] = 'Unable to update track availability. Please try again later.';
        } else {
            // Do not show internal database errors to vendors.
            $errors[] = 'Unable to update track availability. Please try again later.';
        }
    }
}

// Retrieve only the race tracks that belong to the logged-in vendor.
$selectSql = "SELECT track_id, track_name, location, opening_time, closing_time, status
    FROM race_tracks
    WHERE vendor_id = ?
    ORDER BY track_name ASC";

$selectStatement = mysqli_prepare($connection, $selectSql);

if ($selectStatement) {
    // Bind the logged-in vendor ID as an integer.
    mysqli_stmt_bind_param($selectStatement, "i", $vendorId);

    // Run the prepared SELECT query.
    if (mysqli_stmt_execute($selectStatement)) {
        // Initialize variables that will receive each database row.
        $trackId = null;
        $trackName = '';
        $location = '';
        $openingTime = '';
        $closingTime = '';
        $status = '';

        // Store the selected columns into PHP variables.
        mysqli_stmt_bind_result(
            $selectStatement,
            $trackId,
            $trackName,
            $location,
            $openingTime,
            $closingTime,
            $status
        );

        // Build a simple array of this vendor's race track availability.
        while (mysqli_stmt_fetch($selectStatement)) {
            $tracks[] = [
                'track_id' => $trackId,
                'track_name' => $trackName,
                'location' => $location,
                'opening_time' => $openingTime,
                'closing_time' => $closingTime,
                'status' => $status,
            ];
        }
    } else {
        // Do not show internal database errors to vendors.
        $errors[] = 'Unable to load track availability. Please try again later.';
    }

    // Close the prepared statement after the track availability query.
    mysqli_stmt_close($selectStatement);
} else {
    // Do not show internal database errors to vendors.
    $errors[] = 'Unable to load track availability. Please try again later.';
}

// Set the page title before loading the common header.
$pageTitle = 'Manage Availability';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="vendor-availability">
            <!-- Availability page heading -->
            <div class="availability-header">
                <h1>Manage Availability</h1>
                <p>Update the opening and closing time for your race tracks.</p>
            </div>

            <?php if (!empty($errors)) { ?>
                <!-- Validation and database error messages -->
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error) { ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if ($successMessage !== '') { ?>
                <!-- Availability success message -->
                <div class="success-message">
                    <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php } ?>

            <?php if (empty($errors) && empty($tracks)) { ?>
                <!-- Empty availability message -->
                <p>No race tracks are available to manage yet.</p>
            <?php } ?>

            <?php if (!empty($tracks)) { ?>
                <!-- Vendor track availability listing -->
                <div class="availability-list">
                    <?php foreach ($tracks as $track) { ?>
                        <?php
                        // Preserve submitted values for the track form that failed validation.
                        $formOpeningTime = $track['opening_time'];
                        $formClosingTime = $track['closing_time'];

                        if (
                            isPostRequest()
                            && !empty($errors)
                            && $submittedTrackId === (int) $track['track_id']
                        ) {
                            $formOpeningTime = $submittedOpeningTime;
                            $formClosingTime = $submittedClosingTime;
                        }
                        ?>
                        <article class="availability-item">
                            <h2><?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?></h2>

                            <p>
                                <strong>Location:</strong>
                                <?= htmlspecialchars($track['location'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Current Opening Time:</strong>
                                <?= htmlspecialchars($track['opening_time'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Current Closing Time:</strong>
                                <?= htmlspecialchars($track['closing_time'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <strong>Status:</strong>
                                <?= htmlspecialchars($track['status'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <!-- Edit availability form -->
                            <form method="POST" action="availability.php" class="availability-form">
                                <input
                                    type="hidden"
                                    name="track_id"
                                    value="<?= htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>"
                                >

                                <!-- Opening Time -->
                                <div class="form-group">
                                    <label for="opening_time_<?= htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>">Opening Time</label>
                                    <input
                                        type="time"
                                        id="opening_time_<?= htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>"
                                        name="opening_time"
                                        value="<?= htmlspecialchars($formOpeningTime, ENT_QUOTES, 'UTF-8') ?>"
                                        required
                                    >
                                </div>

                                <!-- Closing Time -->
                                <div class="form-group">
                                    <label for="closing_time_<?= htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>">Closing Time</label>
                                    <input
                                        type="time"
                                        id="closing_time_<?= htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>"
                                        name="closing_time"
                                        value="<?= htmlspecialchars($formClosingTime, ENT_QUOTES, 'UTF-8') ?>"
                                        required
                                    >
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn-primary">Update Availability</button>
                            </form>
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
