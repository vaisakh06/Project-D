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

// Create empty variables to store form values.
$trackName = '';
$location = '';
$address = '';
$description = '';
$pricePerHour = '';
$trackType = '';
$openingTime = '';
$closingTime = '';
$contactNumber = '';
$errors = [];

// Check if the add track form was submitted using the POST method.
if (isPostRequest()) {
    // Sanitize submitted text inputs before validation and display.
    $trackName = sanitizeInput($_POST['track_name'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $pricePerHour = sanitizeInput($_POST['price_per_hour'] ?? '');
    $trackType = sanitizeInput($_POST['track_type'] ?? '');
    $openingTime = sanitizeInput($_POST['opening_time'] ?? '');
    $closingTime = sanitizeInput($_POST['closing_time'] ?? '');
    $contactNumber = sanitizeInput($_POST['contact_number'] ?? '');

    // Validate track name.
    if ($trackName === '') {
        $errors[] = 'Please enter the race track name.';
    } elseif (strlen($trackName) > 150) {
        $errors[] = 'Race track name must not be more than 150 characters.';
    }

    // Validate location.
    if ($location === '') {
        $errors[] = 'Please enter the race track location.';
    } elseif (strlen($location) > 150) {
        $errors[] = 'Location must not be more than 150 characters.';
    }

    // Validate address.
    if ($address === '') {
        $errors[] = 'Please enter the full race track address.';
    }

    // Validate price per hour.
    if ($pricePerHour === '') {
        $errors[] = 'Please enter the price per hour.';
    } elseif (!is_numeric($pricePerHour)) {
        $errors[] = 'Price per hour must be a valid number.';
    } elseif ((float) $pricePerHour <= 0) {
        $errors[] = 'Price per hour must be greater than zero.';
    }

    // Validate optional track type.
    if ($trackType !== '' && strlen($trackType) > 50) {
        $errors[] = 'Track type must not be more than 50 characters.';
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

    // Validate optional contact number.
    if ($contactNumber !== '') {
        if (!ctype_digit($contactNumber)) {
            $errors[] = 'Contact number must contain only digits.';
        } elseif (strlen($contactNumber) > 15) {
            $errors[] = 'Contact number must not be more than 15 digits.';
        }
    }

    // Add the race track only after validation passes.
    if (empty($errors)) {
        $pricePerHourValue = (float) $pricePerHour;

        // Prepare the SQL query that adds a new race track for the logged-in vendor.
        $insertSql = "INSERT INTO race_tracks
            (vendor_id, track_name, location, address, description, price_per_hour, track_type, opening_time, closing_time, contact_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $insertStatement = mysqli_prepare($connection, $insertSql);

        if ($insertStatement) {
            // Bind the vendor ID from the session and the validated form values.
            mysqli_stmt_bind_param(
                $insertStatement,
                "issssdssss",
                $vendorId,
                $trackName,
                $location,
                $address,
                $description,
                $pricePerHourValue,
                $trackType,
                $openingTime,
                $closingTime,
                $contactNumber
            );

            // Run the INSERT query and store whether it completed successfully.
            $trackAdded = mysqli_stmt_execute($insertStatement);

            // Close the INSERT statement after it has been used.
            mysqli_stmt_close($insertStatement);

            if ($trackAdded) {
                // Return to the vendor dashboard after the track is added.
                redirect('vendor/dashboard.php?track_added=1');
            }

            // Do not show internal database errors to vendors.
            $errors[] = 'Unable to add the race track. Please try again later.';
        } else {
            // Do not show internal database errors to vendors.
            $errors[] = 'Unable to add the race track. Please try again later.';
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Add Race Track';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="add-track">
            <!-- Add race track page heading -->
            <h1>Add Race Track</h1>
            <p>Create a new race track listing for administrator approval.</p>

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

            <!-- Add race track form -->
            <form method="POST" action="add_track.php" class="add-track-form">
                <!-- Track Name -->
                <div class="form-group">
                    <label for="track_name">Track Name</label>
                    <input
                        type="text"
                        id="track_name"
                        name="track_name"
                        placeholder="Enter race track name"
                        maxlength="150"
                        value="<?= htmlspecialchars($trackName, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label for="location">Location</label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        placeholder="Enter city or area"
                        maxlength="150"
                        value="<?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">Full Address</label>
                    <textarea
                        id="address"
                        name="address"
                        placeholder="Enter full race track address"
                        required
                    ><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Describe the race track"
                    ><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Price Per Hour -->
                <div class="form-group">
                    <label for="price_per_hour">Price Per Hour</label>
                    <input
                        type="number"
                        id="price_per_hour"
                        name="price_per_hour"
                        placeholder="Enter price per hour"
                        min="1"
                        step="0.01"
                        value="<?= htmlspecialchars($pricePerHour, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <!-- Track Type -->
                <div class="form-group">
                    <label for="track_type">Track Type</label>
                    <input
                        type="text"
                        id="track_type"
                        name="track_type"
                        placeholder="Example: Karting, Bike, Car"
                        maxlength="50"
                        value="<?= htmlspecialchars($trackType, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <!-- Opening Time -->
                <div class="form-group">
                    <label for="opening_time">Opening Time</label>
                    <input
                        type="time"
                        id="opening_time"
                        name="opening_time"
                        value="<?= htmlspecialchars($openingTime, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <!-- Closing Time -->
                <div class="form-group">
                    <label for="closing_time">Closing Time</label>
                    <input
                        type="time"
                        id="closing_time"
                        name="closing_time"
                        value="<?= htmlspecialchars($closingTime, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <!-- Contact Number -->
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input
                        type="tel"
                        id="contact_number"
                        name="contact_number"
                        placeholder="Optional track contact number"
                        maxlength="15"
                        value="<?= htmlspecialchars($contactNumber, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary">Add Race Track</button>
            </form>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
