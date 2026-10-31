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
            <!-- Cinematic Track Hero -->
            <section class="track-page-hero">
                <div class="track-page-hero-image">
                    <?php if ($hasMainImage && !empty($mainImagePath)): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($mainImagePath, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($trackName, ENT_QUOTES, 'UTF-8') ?>"
                             class="track-page-hero-img">
                    <?php else: ?>
                        <div class="track-page-hero-fallback">
                            <div class="fallback-grid"></div>
                            <div class="fallback-label">TRACK</div>
                        </div>
                    <?php endif; ?>
                    <div class="track-page-hero-overlay"></div>
                    <div class="track-page-hero-overlay-line overlay-horizontal-top"></div>
                    <div class="track-page-hero-overlay-line overlay-horizontal-bottom"></div>
                    <div class="track-page-hero-overlay-line overlay-vertical-left"></div>
                    <div class="track-page-hero-overlay-line overlay-vertical-right"></div>
                    <div class="track-page-hero-crosshair"></div>
                    <div class="track-page-hero-corner overlay-tl"></div>
                    <div class="track-page-hero-corner overlay-tr"></div>
                    <div class="track-page-hero-corner overlay-bl"></div>
                    <div class="track-page-hero-corner overlay-br"></div>
                </div>
                <div class="track-page-hero-content">
                    <div class="track-page-hero-number">
                        <span class="section-num">01</span>
                        <div class="section-num-line"></div>
                    </div>
                    <h1 class="track-page-hero-title"><?= htmlspecialchars($trackName, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="track-page-hero-location">
                        <?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <div class="track-page-hero-specs">
                        <?php if (!empty($trackType)): ?>
                            <span class="track-page-hero-spec"><?= htmlspecialchars($trackType, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <span class="track-page-hero-spec">₹<?= number_format((float)$pricePerHour, 2) ?>/HR</span>
                    </div>
                </div>
            </section>

            <!-- Track Details Section -->
            <section class="track-page-details">
                <div class="section-header">
                    <div class="section-number-block">
                        <span class="section-num">02</span>
                        <div class="section-num-line"></div>
                    </div>
                    <h2 class="section-title">TRACK INFORMATION</h2>
                </div>

                <div class="track-page-detail-list">
                    <div class="track-page-details-text">
                        <?php if (!empty($description)): ?>
                            <p class="track-page-description"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>

                        <div class="track-page-detail-rows">
                            <div class="track-page-detail-item">
                                <span class="detail-label">PRICE</span>
                                <span class="detail-value">₹<?= number_format((float)$pricePerHour, 2) ?></span>
                            </div>
                            <div class="track-page-detail-item">
                                <span class="detail-label">TYPE</span>
                                <span class="detail-value"><?= htmlspecialchars($trackType !== null && $trackType !== '' ? $trackType : 'Not provided', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="track-page-detail-item">
                                <span class="detail-label">HOURS</span>
                                <span class="detail-value"><?= htmlspecialchars($openingTime, ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($closingTime, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="track-page-detail-item">
                                <span class="detail-label">VENUE</span>
                                <span class="detail-value"><?= htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="track-page-detail-item">
                                <span class="detail-label">STATUS</span>
                                <span class="detail-value"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="track-page-detail-item">
                                <span class="detail-label">CONTACT</span>
                                <span class="detail-value"><?= htmlspecialchars($contactNumber ?? 'Not provided', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Booking Section -->
            <section class="track-page-booking">
                <div class="section-header">
                    <div class="section-number-block">
                        <span class="section-num">03</span>
                        <div class="section-num-line"></div>
                    </div>
                    <h2 class="section-title">BOOK THIS TRACK</h2>
                </div>

                <?php if ($bookingSuccess !== ''): ?>
                    <div class="success-message">
                        <p><?= htmlspecialchars($bookingSuccess, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($bookingError !== ''): ?>
                    <div class="error-messages">
                        <p><?= htmlspecialchars($bookingError, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isUserLoggedIn()): ?>
                    <form method="POST" action="track.php?track_id=<?= htmlspecialchars((string) $trackId, ENT_QUOTES, 'UTF-8') ?>" class="track-page-booking-form">
                        <input type="hidden" name="create_booking" value="1">

                        <div class="booking-form-grid">
                            <div class="booking-form-group">
                                <label for="booking_date" class="booking-form-label">BOOKING DATE</label>
                                <input
                                    type="date"
                                    id="booking_date"
                                    name="booking_date"
                                    min="<?= date('Y-m-d') ?>"
                                    value="<?= htmlspecialchars($_POST['booking_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>
                            <div class="booking-form-group">
                                <label for="start_time" class="booking-form-label">START TIME</label>
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
                            <div class="booking-form-group">
                                <label for="end_time" class="booking-form-label">END TIME</label>
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
                        </div>

                        <p class="booking-price-info">
                            Price: ₹<?= htmlspecialchars($pricePerHour, ENT_QUOTES, 'UTF-8') ?> per hour
                        </p>

                        <button type="submit" class="cta-button-primary">BOOK THIS TRACK</button>
                    </form>
                <?php else: ?>
                    <div class="track-page-login-notice">
                        <p>Already have an account? <a href="<?= BASE_URL . 'user/login.php' ?>">Log in</a> to book this track.</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Back to Tracks -->
            <div class="track-page-back">
                <a href="<?= BASE_URL . 'tracks.php' ?>" class="track-action">← BACK TO TRACKS</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faders = document.querySelectorAll('.track-page-hero-content, .track-page-details, .track-page-booking, .track-page-back');

    const appearOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const appearOnScroll = new IntersectionObserver(function(entries, appearOnScroll) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) {
                return;
            } else {
                entry.target.classList.add('appear');
                appearOnScroll.unobserve(entry.target);
            }
        });
    }, appearOptions);

    faders.forEach(fader => {
        appearOnScroll.observe(fader);
    });

    faders.forEach(fader => {
        if (fader.getBoundingClientRect().top < window.innerHeight &&
            fader.getBoundingClientRect().bottom >= 0) {
            fader.classList.add('appear');
        }
    });
});
</script>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
?>