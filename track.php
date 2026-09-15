<?php
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
/** @var mysqli $connection */

require_once 'includes/functions.php';

// Validate track_id from GET.
$trackId = $_GET['track_id'] ?? '';

if (!ctype_digit((string) $trackId) || (int) $trackId <= 0) {
    $trackError = 'Track not found.';
} else {
    $trackId = (int) $trackId;

    // Fetch the selected track and its vendor information.
    $sql = "SELECT rt.track_id, rt.track_name, rt.location, rt.description, rt.price_per_hour, rt.track_type, rt.opening_time, rt.closing_time, rt.status, rt.contact_number, v.business_name, v.owner_name
            FROM race_tracks rt
            INNER JOIN vendors v ON rt.vendor_id = v.vendor_id
            WHERE rt.track_id = ?
            LIMIT 1";

    $statement = mysqli_prepare($connection, $sql);

    if ($statement) {
        mysqli_stmt_bind_param($statement, "i", $trackId);
        mysqli_stmt_execute($statement);
        mysqli_stmt_bind_result(
            $statement,
            $trackId,
            $trackName,
            $location,
            $description,
            $pricePerHour,
            $trackType,
            $openingTime,
            $closingTime,
            $status,
            $contactNumber,
            $businessName,
            $ownerName
        );

        if (mysqli_stmt_fetch($statement)) {
            mysqli_stmt_close($statement);

            // Load main track image if available.
            $imageSql = "SELECT image_path FROM track_images WHERE track_id = ? AND is_main = 1 LIMIT 1";
            $imageStatement = mysqli_prepare($connection, $imageSql);
            if ($imageStatement) {
                mysqli_stmt_bind_param($imageStatement, "i", $trackId);
                mysqli_stmt_execute($imageStatement);
                mysqli_stmt_bind_result($imageStatement, $mainImagePath);
                $hasMainImage = mysqli_stmt_fetch($imageStatement);
                mysqli_stmt_close($imageStatement);
            } else {
                $hasMainImage = false;
            }
        } else {
            mysqli_stmt_close($statement);
            $trackError = 'Track not found.';
        }
    } else {
        $trackError = 'Something went wrong. Please try again later.';
    }
}

// Handle booking creation POST request.
$bookingSuccess = '';
$bookingError = '';

if (isPostRequest() && isset($_POST['create_booking'])) {
    // Only logged-in users can create bookings.
    if (!isUserLoggedIn()) {
        $bookingError = 'Please log in to book this track.';
    } elseif (!isset($trackError)) {
        // Reject bookings on non-approved tracks.
        if ($status !== 'Approved') {
            $bookingError = 'This track is not available for booking.';
        }

        // Collect and sanitize form inputs.
        $bookingDate = trim($_POST['booking_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');

        if ($bookingError === '') {
            // Validate booking date is not empty.
            if ($bookingDate === '') {
                $bookingError = 'Please select a booking date.';
            }
            // Validate booking date is not in the past.
            elseif ($bookingDate < date('Y-m-d')) {
                $bookingError = 'Booking date cannot be in the past.';
            }
            // Validate start time is not empty.
            elseif ($startTime === '') {
                $bookingError = 'Please select a start time.';
            }
            // Validate end time is not empty.
            elseif ($endTime === '') {
                $bookingError = 'Please select an end time.';
            }
            // Validate start time is before end time.
            elseif ($startTime >= $endTime) {
                $bookingError = 'Start time must be before end time.';
            }
            // Validate the booking falls within track operating hours.
            elseif ($startTime < $openingTime || $endTime > $closingTime) {
                $bookingError = 'Booking must be between track hours: ' . $openingTime . ' - ' . $closingTime;
            }
        }

        if ($bookingError === '') {
            // Calculate duration in hours using PHP DateTime for accurate difference.
            $startDt = new DateTime($bookingDate . ' ' . $startTime);
            $endDt = new DateTime($bookingDate . ' ' . $endTime);
            $durationMinutes = ($endDt->getTimestamp() - $startDt->getTimestamp()) / 60;

            // Prevent zero or negative duration.
            if ($durationMinutes <= 0) {
                $bookingError = 'Invalid booking duration.';
            }
        }

        if ($bookingError === '') {
            // Calculate total amount from price_per_hour and duration.
            $durationHours = $durationMinutes / 60;
            $totalAmount = round($pricePerHour * $durationHours, 2);

            // Prevent zero or negative amount.
            if ($totalAmount <= 0) {
                $bookingError = 'Invalid booking amount.';
            }
        }

        if ($bookingError === '') {
            // Check for overlapping bookings on the same track and date.
            $overlapSql = "SELECT booking_id FROM bookings
                           WHERE track_id = ? AND booking_date = ? AND status IN ('Pending', 'Accepted')
                           AND start_time < ? AND end_time > ?
                           LIMIT 1";
            $overlapStmt = mysqli_prepare($connection, $overlapSql);

            if ($overlapStmt) {
                mysqli_stmt_bind_param($overlapStmt, "isss", $trackId, $bookingDate, $endTime, $startTime);
                mysqli_stmt_execute($overlapStmt);
                mysqli_stmt_store_result($overlapStmt);
                $hasOverlap = mysqli_stmt_num_rows($overlapStmt) > 0;
                mysqli_stmt_close($overlapStmt);

                if ($hasOverlap) {
                    $bookingError = 'This track is already booked for the selected date and time.';
                }
            } else {
                $bookingError = 'Something went wrong. Please try again later.';
            }
        }

        if ($bookingError === '') {
            // Generate a unique booking reference: BK-YYYYMMDD-XXXX.
            $datePart = date('Ymd');
            $maxAttempts = 10;
            $bookingRef = '';

            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $randomPart = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $candidate = 'BK-' . $datePart . '-' . $randomPart;

                // Check uniqueness of the generated reference.
                $refCheckSql = "SELECT booking_id FROM bookings WHERE booking_reference = ? LIMIT 1";
                $refCheckStmt = mysqli_prepare($connection, $refCheckSql);

                if ($refCheckStmt) {
                    mysqli_stmt_bind_param($refCheckStmt, "s", $candidate);
                    mysqli_stmt_execute($refCheckStmt);
                    mysqli_stmt_store_result($refCheckStmt);
                    $refExists = mysqli_stmt_num_rows($refCheckStmt) > 0;
                    mysqli_stmt_close($refCheckStmt);

                    if (!$refExists) {
                        $bookingRef = $candidate;
                        break;
                    }
                } else {
                    $bookingError = 'Something went wrong. Please try again later.';
                    break;
                }
            }

            // Proceed only if a unique reference was generated.
            if ($bookingRef === '' && $bookingError === '') {
                $bookingError = 'Unable to generate a booking reference. Please try again.';
            }
        }

        if ($bookingError === '') {
            // Get authenticated user ID from session.
            $userId = $_SESSION['user_id'];

            // Insert the booking with Pending status.
            $insertSql = "INSERT INTO bookings
                (booking_reference, user_id, track_id, booking_date, start_time, end_time, total_amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
            $insertStmt = mysqli_prepare($connection, $insertSql);

            if ($insertStmt) {
                mysqli_stmt_bind_param(
                    $insertStmt,
                    "siisssd",
                    $bookingRef,
                    $userId,
                    $trackId,
                    $bookingDate,
                    $startTime,
                    $endTime,
                    $totalAmount
                );

                $insertSuccess = mysqli_stmt_execute($insertStmt);
                $newBookingId = mysqli_stmt_insert_id($insertStmt);
                mysqli_stmt_close($insertStmt);

                if ($insertSuccess) {
                    redirect('payment.php?booking_id=' . (int) $newBookingId);
                } else {
                    $bookingError = 'Unable to create your booking. Please try again later.';
                }
            } else {
                $bookingError = 'Something went wrong. Please try again later.';
            }
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Track Details';

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <div class="container">
        <?php if (isset($trackError)): ?>
            <!-- Error message -->
            <div class="error-messages">
                <p><?= htmlspecialchars($trackError, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <p>
                <a href="<?= BASE_URL . 'tracks.php' ?>">Back to Tracks</a>
            </p>
        <?php else: ?>
            <!-- Track detail heading -->
            <div class="page-header">
                <h1><?= htmlspecialchars($trackName, ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <?php if ($hasMainImage && !empty($mainImagePath)): ?>
                <img
                    src="<?= BASE_URL . htmlspecialchars($mainImagePath, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($trackName, ENT_QUOTES, 'UTF-8') ?>"
                    class="track-detail-image"
                >
            <?php endif; ?>

            <div class="track-detail-info">
                <p>
                    <strong>Description:</strong>
                    <?= htmlspecialchars($description ?? 'No description provided.', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Price Per Hour:</strong>
                    <?= htmlspecialchars($pricePerHour, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Track Type:</strong>
                    <?= htmlspecialchars($trackType !== null && $trackType !== '' ? $trackType : 'Not provided', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Hours:</strong>
                    <?= htmlspecialchars($openingTime, ENT_QUOTES, 'UTF-8') ?> -
                    <?= htmlspecialchars($closingTime, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Venue:</strong>
                    <?= htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <strong>Contact:</strong>
                    <?= htmlspecialchars($contactNumber ?? 'Not provided', ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>

            <div class="track-detail-actions">
                <a href="<?= BASE_URL . 'tracks.php' ?>" class="btn-primary">Back to Tracks</a>
            </div>

            <!-- Booking success message -->
            <?php if ($bookingSuccess !== ''): ?>
                <div class="success-message">
                    <p><?= htmlspecialchars($bookingSuccess, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Booking error message -->
            <?php if ($bookingError !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($bookingError, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Booking form (only for logged-in users) -->
            <?php if (isUserLoggedIn()): ?>
                <div class="track-detail-booking">
                    <h2>Book This Track</h2>
                    <form method="POST" action="track.php?track_id=<?= htmlspecialchars((string) $trackId, ENT_QUOTES, 'UTF-8') ?>" class="booking-form">
                        <input type="hidden" name="create_booking" value="1">

                        <!-- Booking Date -->
                        <div class="form-group">
                            <label for="booking_date">Booking Date</label>
                            <input
                                type="date"
                                id="booking_date"
                                name="booking_date"
                                min="<?= date('Y-m-d') ?>"
                                value="<?= htmlspecialchars($_POST['booking_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>

                        <!-- Start Time -->
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input
                                type="time"
                                id="start_time"
                                name="start_time"
                                min="<?= htmlspecialchars($openingTime, ENT_QUOTES, 'UTF-8') ?>"
                                max="<?= htmlspecialchars($closingTime, ENT_QUOTES, 'UTF-8') ?>"
                                value="<?= htmlspecialchars($_POST['start_time'] ?? $openingTime, ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>

                        <!-- End Time -->
                        <div class="form-group">
                            <label for="end_time">End Time</label>
                            <input
                                type="time"
                                id="end_time"
                                name="end_time"
                                min="<?= htmlspecialchars($openingTime, ENT_QUOTES, 'UTF-8') ?>"
                                max="<?= htmlspecialchars($closingTime, ENT_QUOTES, 'UTF-8') ?>"
                                value="<?= htmlspecialchars($_POST['end_time'] ?? $closingTime, ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>

                        <!-- Price info -->
                        <p class="booking-price-info">
                            Price: <?= htmlspecialchars($pricePerHour, ENT_QUOTES, 'UTF-8') ?> per hour
                        </p>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-primary">Book This Track</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="track-detail-booking">
                    <p>
                        <a href="<?= BASE_URL . 'user/login.php' ?>">Log in</a> to book this track.
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
?>