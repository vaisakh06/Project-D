<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */
require_once '../includes/functions.php';

// Protect this page so only logged-in admins can access it.
if (!isset($_SESSION['admin_id'], $_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    redirect('admin/login.php');
}

// Initialize CSRF token.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate message ID.
$messageId = 0;
$message = null;
$viewError = '';
$replySuccess = false;

$messageIdInput = $_GET['message_id'] ?? '';

if (!ctype_digit((string) $messageIdInput) || (int) $messageIdInput <= 0) {
    $viewError = 'Invalid message ID.';
} else {
    $messageId = (int) $messageIdInput;

    // Fetch the message using the helper function.
    $message = getContactMessageById($connection, $messageId);

    if ($message === false) {
        $viewError = 'Message not found.';
    }
}

// Handle reply submission.
if (isPostRequest() && isset($_POST['reply_submit'])) {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $replyIdInput = $_POST['reply_message_id'] ?? '';
    $reply = trim($_POST['admin_reply'] ?? '');

    // Validate CSRF token.
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $csrfToken)) {
        $viewError = 'Invalid request. Please try again.';
    } elseif (!ctype_digit((string) $replyIdInput) || (int) $replyIdInput <= 0) {
        $viewError = 'Invalid message ID.';
    } elseif ($reply === '') {
        $viewError = 'Reply cannot be empty.';
    } elseif (strlen($reply) > 5000) {
        $viewError = 'Reply must not exceed 5000 characters.';
    } else {
        $replyId = (int) $replyIdInput;

        // Verify the message being replied to is the same as the one viewed.
        if ($replyId !== $messageId) {
            $viewError = 'Invalid message ID.';
        } else {
            // Sanitize the reply text.
            $reply = sanitizeInput($reply);

            // Save the reply.
            $result = saveContactReply($connection, $replyId, $reply);

            if (isset($result['success'])) {
                // PRG: redirect after successful reply to prevent resubmission on refresh.
                redirect('admin/view_contact.php?message_id=' . $messageId . '&replied=1');
                exit;
            } else {
                $viewError = $result['error'];
            }
        }
    }
}

// Check for PRG success flag.
if (isset($_GET['replied']) && $_GET['replied'] === '1' && $message !== null) {
    $replySuccess = true;
    // Refresh the message to show the updated reply and status.
    $message = getContactMessageById($connection, $messageId);
}

// Set the page title before loading the common header.
$pageTitle = 'View Message';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="admin-view-contact">
            <!-- Back link -->
            <p>
                <a href="<?= BASE_URL . 'admin/manage_contacts.php' ?>">&larr; Back to All Messages</a>
            </p>

            <?php if ($viewError !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($viewError, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <!-- Message detail -->
                <div class="page-header">
                    <h1>Message #<?= (int) $message['message_id'] ?></h1>
                    <p>Received on <?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="dashboard-card">
                    <div class="form-group">
                        <label>Name</label>
                        <p class="readonly-field"><?= htmlspecialchars($message['name'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <p class="readonly-field"><?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <div class="form-group">
                        <label>Subject</label>
                        <p class="readonly-field"><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <p>
                            <span class="status-badge status-<?= strtolower($message['status']) ?>">
                                <?= htmlspecialchars($message['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Original Message</label>
                        <p class="readonly-field" style="white-space: pre-wrap;"><?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <?php if (!empty($message['admin_reply'])): ?>
                        <div class="form-group">
                            <label>Admin Reply</label>
                            <p class="readonly-field" style="white-space: pre-wrap;"><?= htmlspecialchars($message['admin_reply'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="form-group">
                            <label>Replied At</label>
                            <p class="readonly-field"><?= htmlspecialchars($message['replied_at'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Received</label>
                        <p class="readonly-field"><?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <?php if ($message['updated_at'] !== $message['created_at']): ?>
                        <div class="form-group">
                            <label>Last Updated</label>
                            <p class="readonly-field"><?= htmlspecialchars($message['updated_at'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Reply form -->
                <div class="page-header" style="margin-top: 24px;">
                    <h2>Reply to This Message</h2>
                </div>

                <?php if ($replySuccess): ?>
                    <div class="success-message">
                        <p>Your reply has been saved successfully.</p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="view_contact.php?message_id=<?= (int) $messageId ?>" class="booking-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="reply_message_id" value="<?= (int) $messageId ?>">

                    <div class="form-group">
                        <label for="admin_reply">Your Reply</label>
                        <textarea
                            id="admin_reply"
                            name="admin_reply"
                            placeholder="Enter your reply"
                            rows="5"
                            maxlength="5000"
                            required
                        ><?= htmlspecialchars($_POST['admin_reply'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <button type="submit" name="reply_submit" class="btn-primary">Submit Reply</button>
                </form>

                <p>
                    <a href="<?= BASE_URL . 'admin/manage_contacts.php' ?>" class="btn-primary">Back to All Messages</a>
                </p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
