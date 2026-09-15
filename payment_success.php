<?php
require_once 'config/constants.php';
require_once 'config/database.php';
/** @var mysqli $connection */
require_once 'includes/functions.php';

requireUserLogin();

$bookingId = 0;
$booking = null;
$paymentError = '';
$transactionRef = '';
$method = 'card';

$bookingIdInput = $_GET['booking_id'] ?? '';
$method = isset($_GET['method']) && $_GET['method'] === 'upi' ? 'upi' : 'card';

if (!ctype_digit((string) $bookingIdInput) || (int) $bookingIdInput <= 0) {
    $paymentError = 'Invalid booking ID.';
} else {
    $bookingId = (int) $bookingIdInput;

    $sql = "SELECT b.booking_id, b.booking_reference, b.user_id, b.track_id,
                   rt.track_name, b.booking_date, b.start_time, b.end_time,
                   b.total_amount, b.status
            FROM bookings b
            INNER JOIN race_tracks rt ON b.track_id = rt.track_id
            WHERE b.booking_id = ?
            LIMIT 1";
    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $booking = mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
    } else {
        $paymentError = 'Something went wrong. Please try again later.';
    }

    if ($booking === null && $paymentError === '') {
        $paymentError = 'Booking not found.';
    }
}

if ($booking !== null && $paymentError === '') {
    if ($booking['user_id'] !== $_SESSION['user_id']) {
        $paymentError = 'You do not have permission to view this booking.';
        $booking = null;
    }
}

if ($booking !== null && $paymentError === '') {
    if ($booking['status'] !== 'Pending') {
        $paymentError = 'This booking cannot be paid. Only pending bookings can proceed to payment.';
        $booking = null;
    }
}

if ($booking !== null && $paymentError === '') {
    $datePart = date('Ymd');
    $randomPart = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    $transactionRef = 'TXN-' . $datePart . '-' . $randomPart;
}

$pageTitle = 'Payment Confirmed';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <div class="container">
        <?php if ($paymentError !== ''): ?>
            <div class="error-messages">
                <p><?= htmlspecialchars($paymentError, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <p>
                <a href="<?= BASE_URL . 'user/bookings.php' ?>">Back to My Bookings</a>
            </p>
        <?php else: ?>
            <div class="page-header">
                <h1>Payment Confirmed</h1>
            </div>

            <div class="track-detail-booking">
                <div class="success-message">
                    <p>Simulated Payment — Successful</p>
                </div>

                <p><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Track Name:</strong> <?= htmlspecialchars($booking['track_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Booking Date:</strong> <?= htmlspecialchars($booking['booking_date'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Start Time:</strong> <?= htmlspecialchars($booking['start_time'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>End Time:</strong> <?= htmlspecialchars($booking['end_time'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Total Amount:</strong> <?= htmlspecialchars(number_format((float) $booking['total_amount'], 2), ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Payment Method:</strong> <?= htmlspecialchars($method === 'upi' ? 'UPI Payment' : 'Card Payment', ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Mock Transaction Reference:</strong> <?= htmlspecialchars($transactionRef, ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Payment Result:</strong> Simulated Payment — Successful</p>

                <p class="booking-price-info">
                    No real money was charged. This is a simulated/mock payment.
                </p>

                <p>
                    <a href="<?= BASE_URL . 'user/bookings.php' ?>" class="btn-primary">Back to My Bookings</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>
