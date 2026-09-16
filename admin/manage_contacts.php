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

// Initialize message variables.
$statusMessage = '';
$successMessage = '';

// Handle POST requests for message actions.
if (isPostRequest()) {
    $messageIdInput = $_POST['message_id'] ?? '';
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Validate CSRF token.
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $csrfToken)) {
        $statusMessage = 'Invalid request. Please try again.';
    } elseif (!ctype_digit((string) $messageIdInput) || (int) $messageIdInput <= 0) {
        // Validate message_id as integer.
        $statusMessage = 'Invalid message ID.';
    } else {
        $messageId = (int) $messageIdInput;

        // Validate action against explicit allowlist.
        $allowedActions = ['mark_read', 'mark_replied', 'delete'];
        if (!in_array($action, $allowedActions, true)) {
            $statusMessage = 'Invalid action.';
        } else {
            if ($action === 'delete') {
                $result = deleteContactMessage($connection, $messageId);
                if (isset($result['success'])) {
                    $successMessage = 'Message deleted successfully.';
                } else {
                    $statusMessage = $result['error'];
                }
            } else {
                $newStatus = $action === 'mark_replied' ? 'Replied' : 'Read';
                $result = updateContactMessageStatus($connection, $messageId, $newStatus);
                if (isset($result['success'])) {
                    $statusLabel = $newStatus === 'Replied' ? 'Replied' : 'Marked as Read';
                    $successMessage = 'Message ' . $statusLabel . ' successfully.';
                } else {
                    $statusMessage = $result['error'];
                }
            }
        }
    }
}

// Fetch all contact messages.
$messagesResult = getContactMessages($connection);
$messageCount = 0;
$unreadCount = getUnreadContactMessageCount($connection);
$messages = [];

if ($messagesResult && mysqli_num_rows($messagesResult) > 0) {
    while ($row = mysqli_fetch_assoc($messagesResult)) {
        $messages[] = $row;
    }
    $messageCount = count($messages);
} elseif ($messagesResult === false) {
    $statusMessage = 'Unable to load messages. Please try again later.';
}

// Set the page title before loading the common header.
$pageTitle = 'Contact Messages';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="admin-contact-messages">
            <!-- Page header -->
            <div class="page-header">
                <h1>Contact Messages</h1>
                <p>View and manage messages submitted through the contact form.</p>
            </div>

            <!-- Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h2><?= $messageCount ?></h2>
                    <p>Total Messages</p>
                </div>
                <div class="stat-card">
                    <h2><?= $unreadCount ?></h2>
                    <p>Unread</p>
                </div>
            </div>

            <!-- Status message -->
            <?php if ($statusMessage !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Success message -->
            <?php if ($successMessage !== ''): ?>
                <div class="success-message">
                    <p><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Messages table -->
            <?php if (!empty($messages)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Message ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Received</th>
                                <th>Replied At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?= (int) $msg['message_id'] ?></td>
                                    <td><?= htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($msg['subject'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($msg['status']) ?>">
                                            <?= htmlspecialchars($msg['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= !empty($msg['replied_at']) ? htmlspecialchars($msg['replied_at'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                    <td class="actions">
                                        <a href="<?= BASE_URL . 'admin/view_contact.php?message_id=' . (int) $msg['message_id'] ?>" class="btn-small btn-info">View</a>
                                        <?php if ($msg['status'] === 'Unread'): ?>
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="message_id" value="<?= (int) $msg['message_id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" name="action" value="mark_read" class="btn-small btn-info">Mark Read</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($msg['status'] === 'Read' || $msg['status'] === 'Unread'): ?>
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="message_id" value="<?= (int) $msg['message_id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" name="action" value="mark_replied" class="btn-small btn-success">Mark Replied</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="message_id" value="<?= (int) $msg['message_id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" name="action" value="delete" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this message?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data" style="padding: 30px 16px; text-align: center; color: var(--color-text-muted); font-style: italic;">
                    No contact messages found.
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
