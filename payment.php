<?php
require_once 'config/constants.php';
require_once 'config/database.php';
/** @var mysqli $connection */
require_once 'includes/functions.php';

requireUserLogin();

$bookingId = 0;
$booking = null;
$paymentError = '';
$method = 'card';
$paymentErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingIdInput = $_POST['booking_id'] ?? '';
    $method = sanitizeInput($_POST['payment_method'] ?? 'card');

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

    if ($paymentError === '' && $booking !== null) {
        if ($method === 'card') {
            $cardName = sanitizeInput($_POST['card_name'] ?? '');
            $cardNumber = sanitizeInput($_POST['card_number'] ?? '');
            $cardExpiry = sanitizeInput($_POST['card_expiry'] ?? '');
            $cardCvv = sanitizeInput($_POST['card_cvv'] ?? '');

            if ($cardName === '') {
                $paymentErrors[] = 'Cardholder name must not be empty.';
            }

            $cardNumberDigits = preg_replace('/\s+/', '', $cardNumber);
            if ($cardNumberDigits === '' || !ctype_digit($cardNumberDigits) || strlen($cardNumberDigits) < 13 || strlen($cardNumberDigits) > 19) {
                $paymentErrors[] = 'Card number must contain 13–19 digits.';
            }

            if (!preg_match('/^(\d{2})\/(\d{2})$/', $cardExpiry, $expiryMatches)) {
                $paymentErrors[] = 'Expiry must use MM/YY format.';
            } else {
                $expiryMonth = (int)$expiryMatches[1];
                $expiryYear = (int)$expiryMatches[2];
                $fullExpiryYear = 2000 + $expiryYear;
                $currentYear = (int)date('Y');
                $currentMonth = (int)date('n');

                if ($expiryMonth < 1 || $expiryMonth > 12) {
                    $paymentErrors[] = 'Invalid expiry month.';
                } elseif ($fullExpiryYear < $currentYear || ($fullExpiryYear === $currentYear && $expiryMonth < $currentMonth)) {
                    $paymentErrors[] = 'Card has expired.';
                }
            }

            $cvvDigits = preg_replace('/\s+/', '', $cardCvv);
            if (!ctype_digit($cvvDigits) || (strlen($cvvDigits) !== 3 && strlen($cvvDigits) !== 4)) {
                $paymentErrors[] = 'CVV must contain 3 or 4 digits.';
            }

            if (empty($paymentErrors)) {
                redirect('payment_success.php?booking_id=' . (int) $bookingId . '&method=card');
            }
        } elseif ($method === 'upi') {
            $upiId = sanitizeInput($_POST['upi_id'] ?? '');

            if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/', $upiId)) {
                $paymentErrors[] = 'UPI ID must have a valid format such as name@provider.';
            }

            if (empty($paymentErrors)) {
                redirect('payment_success.php?booking_id=' . (int) $bookingId . '&method=upi');
            }
        } else {
            $paymentError = 'Invalid payment method.';
        }
    }
} else {
    $bookingIdInput = $_GET['booking_id'] ?? '';

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
}

$pageTitle = 'Complete Your Booking';
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
                <h1>Complete Your Booking</h1>
            </div>

            <div class="track-detail-booking">
                <p><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Track Name:</strong> <?= htmlspecialchars($booking['track_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Booking Date:</strong> <?= htmlspecialchars($booking['booking_date'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Start Time:</strong> <?= htmlspecialchars($booking['start_time'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>End Time:</strong> <?= htmlspecialchars($booking['end_time'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Total Amount:</strong> <?= htmlspecialchars(number_format((float) $booking['total_amount'], 2), ENT_QUOTES, 'UTF-8') ?></p>

                <p class="booking-price-info">
                    This is a simulated payment. No real money will be charged.
                </p>

                <form method="POST" action="payment.php" class="edit-track-form">
                    <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>">

                    <div class="payment-method-options">
                        <div class="payment-method-option">
                            <input type="radio" name="payment_method" id="method_card" value="card" <?= $method === 'card' ? 'checked' : '' ?>>
                            <label for="method_card" class="payment-method-label">Card Payment</label>
                        </div>
                        <div class="payment-method-option">
                            <input type="radio" name="payment_method" id="method_upi" value="upi" <?= $method === 'upi' ? 'checked' : '' ?>>
                            <label for="method_upi" class="payment-method-label">UPI Payment</label>
                        </div>
                    </div>

                    <div id="card-fields" class="payment-form-section <?= $method === 'card' ? 'active' : '' ?>">
                        <?php if (!empty($paymentErrors) && $method === 'card'): ?>
                            <div class="error-messages">
                                <ul>
                                    <?php foreach ($paymentErrors as $err): ?>
                                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="card_name">Cardholder Name</label>
                            <input type="text" id="card_name" name="card_name" placeholder="Enter cardholder name" maxlength="100"
                                   value="<?= htmlspecialchars($_POST['card_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="23"
                                   value="<?= htmlspecialchars($_POST['card_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="card_expiry">Expiry (MM/YY)</label>
                            <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5"
                                   value="<?= htmlspecialchars($_POST['card_expiry'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="password" id="card_cvv" name="card_cvv" placeholder="3 or 4 digits" maxlength="4" required>
                        </div>
                    </div>

                    <div id="upi-fields" class="payment-form-section <?= $method === 'upi' ? 'active' : '' ?>">
                        <?php if (!empty($paymentErrors) && $method === 'upi'): ?>
                            <div class="error-messages">
                                <ul>
                                    <?php foreach ($paymentErrors as $err): ?>
                                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="upi_id">UPI ID</label>
                            <input type="text" id="upi_id" name="upi_id" placeholder="name@provider" maxlength="50"
                                   value="<?= htmlspecialchars($_POST['upi_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Mock QR Code</label>
                            <div class="qr-code" aria-label="Demo QR code">
                                <?php for ($i = 0; $i < 49; $i++): ?>
                                    <div class="qr-cell <?= (random_int(0, 1) === 0) ? '' : 'empty' ?>"></div>
                                <?php endfor; ?>
                            </div>
                            <p class="qr-label">DEMO ONLY — This is not a real QR code. No payment will be processed.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Pay Now</button>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var cardRadio = document.getElementById('method_card');
                            var upiRadio = document.getElementById('method_upi');

                            var cardName = document.getElementById('card_name');
                            var cardNumber = document.getElementById('card_number');
                            var cardExpiry = document.getElementById('card_expiry');
                            var cardCvv = document.getElementById('card_cvv');
                            var upiId = document.getElementById('upi_id');

                            var cardFields = document.getElementById('card-fields');
                            var upiFields = document.getElementById('upi-fields');

                            function updateFields(method) {
                                var isCard = method === 'card';

                                cardFields.classList.toggle('active', isCard);
                                upiFields.classList.toggle('active', !isCard);

                                cardName.disabled = !isCard;
                                cardNumber.disabled = !isCard;
                                cardExpiry.disabled = !isCard;
                                cardCvv.disabled = !isCard;

                                cardName.required = isCard;
                                cardNumber.required = isCard;
                                cardExpiry.required = isCard;
                                cardCvv.required = isCard;

                                upiId.disabled = isCard;
                                upiId.required = !isCard;
                            }

                            if (cardRadio) {
                                cardRadio.addEventListener('change', function () {
                                    updateFields('card');
                                });
                            }

                            if (upiRadio) {
                                upiRadio.addEventListener('change', function () {
                                    updateFields('upi');
                                });
                            }

                            updateFields('<?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?>');
                        });
                    </script>
                </form>

                <p class="booking-price-info" style="margin-top: 16px;">
                    Card numbers, CVV, and UPI IDs are not stored. This is a simulated/mock payment.
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>
