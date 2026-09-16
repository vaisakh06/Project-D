<?php
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
/** @var mysqli $connection */
require_once 'includes/functions.php';

// Initialize CSRF token.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set the page title before loading the common header.
$pageTitle = 'Contact';

$contactError = '';
$contactSent = false;

// Handle contact form submission.
if (isPostRequest() && isset($_POST['contact_submit'])) {
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Validate CSRF token.
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $csrfToken)) {
        $contactError = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validate inputs.
        if ($name === '') {
            $contactError = 'Please enter your name.';
        } elseif ($email === '') {
            $contactError = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contactError = 'Please enter a valid email address.';
        } elseif ($subject === '') {
            $contactError = 'Please enter a subject.';
        } elseif ($message === '') {
            $contactError = 'Please enter a message.';
        } elseif (strlen($message) > 2000) {
            $contactError = 'Message must not exceed 2000 characters.';
        } else {
            // Sanitize inputs before database insertion.
            $name = sanitizeInput($name);
            $email = sanitizeInput($email);
            $subject = sanitizeInput($subject);
            $message = sanitizeInput($message);

            // Insert the message into the database.
            $result = insertContactMessage($connection, $name, $email, $subject, $message);

            if (isset($result['success'])) {
                // PRG: redirect after successful insertion to prevent resubmission on refresh.
                redirect('contact.php?sent=1');
                exit;
            } else {
                $contactError = $result['error'];
            }
        }
    }
}

// Check for success flag from PRG redirect.
if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $contactSent = true;
}

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="contact">
            <!-- Page header -->
            <div class="page-header">
                <h1>Contact Us</h1>
                <p>Have a question or feedback? Send us a message.</p>
            </div>

            <!-- Success message from PRG redirect -->
            <?php if ($contactSent): ?>
                <div class="success-message">
                    <p>Your message has been sent successfully. We will get back to you soon.</p>
                </div>
            <?php endif; ?>

            <!-- Error message -->
            <?php if ($contactError !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($contactError, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Contact form -->
            <form method="POST" action="contact.php" class="booking-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <label for="contact_name">Name</label>
                    <input
                        type="text"
                        id="contact_name"
                        name="name"
                        placeholder="Enter your name"
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contact_email">Email</label>
                    <input
                        type="email"
                        id="contact_email"
                        name="email"
                        placeholder="Enter your email address"
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contact_subject">Subject</label>
                    <input
                        type="text"
                        id="contact_subject"
                        name="subject"
                        placeholder="Enter the subject"
                        maxlength="150"
                        value="<?= htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contact_message">Message</label>
                    <textarea
                        id="contact_message"
                        name="message"
                        placeholder="Enter your message"
                        rows="5"
                        maxlength="2000"
                        required
                    ><?= htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <button type="submit" name="contact_submit" class="btn-primary">Send Message</button>
            </form>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
