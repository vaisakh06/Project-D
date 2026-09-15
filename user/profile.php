<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Protect this page so only logged-in users can access it.
requireUserLogin();

// Always use the authenticated user ID from the session.
$userId = $_SESSION['user_id'];

// Initialize variables.
$profileError = '';
$profileSuccess = '';
$fullName = '';
$email = '';
$phone = '';
$profileImage = '';

// Load current user data from the database.
$loadSql = "SELECT full_name, email, phone, profile_image, status, created_at FROM users WHERE user_id = ? LIMIT 1";
$loadStmt = mysqli_prepare($connection, $loadSql);

if ($loadStmt) {
    mysqli_stmt_bind_param($loadStmt, "i", $userId);
    mysqli_stmt_execute($loadStmt);
    mysqli_stmt_bind_result($loadStmt, $fullName, $email, $phone, $profileImage, $userStatus, $createdAt);
    mysqli_stmt_fetch($loadStmt);
    mysqli_stmt_close($loadStmt);
} else {
    $profileError = 'Something went wrong. Please try again later.';
}

// Handle profile update POST request.
if (isPostRequest() && isset($_POST['update_profile'])) {
    // Collect and sanitize form inputs.
    $newFullName = trim($_POST['full_name'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPhone = trim($_POST['phone'] ?? '');

    // Validate full name.
    if ($newFullName === '') {
        $profileError = 'Please enter your full name.';
    } elseif (strlen($newFullName) < 3 || strlen($newFullName) > 100) {
        $profileError = 'Full name must be between 3 and 100 characters.';
    }
    // Validate email.
    elseif ($newEmail === '') {
        $profileError = 'Please enter your email address.';
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $profileError = 'Please enter a valid email address.';
    } else {
        // Check email uniqueness (excluding current user).
        $emailCheckSql = "SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1";
        $emailCheckStmt = mysqli_prepare($connection, $emailCheckSql);

        if ($emailCheckStmt) {
            mysqli_stmt_bind_param($emailCheckStmt, "si", $newEmail, $userId);
            mysqli_stmt_execute($emailCheckStmt);
            mysqli_stmt_store_result($emailCheckStmt);
            $emailTaken = mysqli_stmt_num_rows($emailCheckStmt) > 0;
            mysqli_stmt_close($emailCheckStmt);

            if ($emailTaken) {
                $profileError = 'This email is already registered by another account.';
            }
        } else {
            $profileError = 'Something went wrong. Please try again later.';
        }
    }

    // Validate phone.
    if ($profileError === '' && $newPhone === '') {
        $profileError = 'Please enter your phone number.';
    } elseif ($profileError === '' && !ctype_digit($newPhone)) {
        $profileError = 'Phone number must contain only digits.';
    } elseif ($profileError === '' && (strlen($newPhone) < 10 || strlen($newPhone) > 15)) {
        $profileError = 'Phone number must be between 10 and 15 digits.';
    }

    // Handle the optional profile image upload.
    $newProfileImage = $profileImage;
    $uploadedImagePath = '';

    if ($profileError === '' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validationResult = validateProfileImageUpload($_FILES['profile_image']);

        if (isset($validationResult['error'])) {
            $profileError = $validationResult['error'];
        } else {
            $uploadDirectory = __DIR__ . '/../' . USER_IMAGE_FOLDER;
            $storeResult = storeProfileImage($_FILES['profile_image']['tmp_name'], $validationResult['extension'], 'user', $userId, $uploadDirectory, USER_IMAGE_FOLDER);

            if (isset($storeResult['error'])) {
                $profileError = $storeResult['error'];
            } else {
                $newProfileImage = $storeResult['path'];
                $uploadedImagePath = $storeResult['path'];
            }
        }
    }

    // Update only after all validation passes.
    if ($profileError === '') {
        $updateSql = "UPDATE users SET full_name = ?, email = ?, phone = ?, profile_image = ? WHERE user_id = ?";
        $updateStmt = mysqli_prepare($connection, $updateSql);

        if ($updateStmt) {
            mysqli_stmt_bind_param($updateStmt, "ssssi", $newFullName, $newEmail, $newPhone, $newProfileImage, $userId);
            $updateSuccessful = mysqli_stmt_execute($updateStmt);
            $affectedRows = mysqli_stmt_affected_rows($updateStmt);
            mysqli_stmt_close($updateStmt);

            if ($updateSuccessful && $affectedRows > 0) {
                // Remove the old profile image only after the new one is saved.
                if ($uploadedImagePath !== '' && $profileImage !== '' && $profileImage !== $newProfileImage) {
                    $oldFilePath = __DIR__ . '/../' . $profileImage;

                    if (is_file($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Update session variables with new values.
                $_SESSION['user_name'] = $newFullName;
                $_SESSION['user_email'] = $newEmail;

                redirect('user/profile.php?updated=1');
                exit;
            } else {
                // Remove the newly uploaded file when the database update fails.
                if ($uploadedImagePath !== '') {
                    $newFilePath = __DIR__ . '/../' . $uploadedImagePath;

                    if (is_file($newFilePath)) {
                        unlink($newFilePath);
                    }
                }

                $profileError = 'No changes were made or unable to update.';
            }
        } else {
            // Remove the newly uploaded file when the database statement cannot be prepared.
            if ($uploadedImagePath !== '') {
                $newFilePath = __DIR__ . '/../' . $uploadedImagePath;

                if (is_file($newFilePath)) {
                    unlink($newFilePath);
                }
            }

            $profileError = 'Something went wrong. Please try again later.';
        }
    }

    // On validation failure, use submitted values for the form.
    if ($profileError !== '') {
        $fullName = $newFullName;
        $email = $newEmail;
        $phone = $newPhone;
    }
}

// Get success message from URL after redirect.
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $profileSuccess = 'Profile updated successfully.';
}

// Set the page title before loading the common header.
$pageTitle = 'My Profile';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="user-profile">
            <!-- Page header -->
            <div class="page-header">
                <h1>My Profile</h1>
                <p>View and update your account information.</p>
            </div>

            <!-- Success message -->
            <?php if ($profileSuccess !== ''): ?>
                <div class="success-message">
                    <p><?= htmlspecialchars($profileSuccess, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <!-- Error message -->
            <?php if ($profileError !== ''): ?>
                <div class="error-messages">
                    <p><?= htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <?php if ($profileError !== 'Something went wrong. Please try again later.'): ?>
                <!-- Profile form -->
                <form method="POST" action="profile.php" class="booking-form" enctype="multipart/form-data">
                    <input type="hidden" name="update_profile" value="1">

                    <!-- Current Profile Image -->
                    <div class="form-group">
                        <label>Profile Image</label>
                        <?php if (!empty($profileImage)) { ?>
                            <img
                                src="<?= BASE_URL . htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Profile image"
                                class="profile-image"
                            >
                        <?php } else { ?>
                            <p class="readonly-field">No profile image uploaded yet.</p>
                        <?php } ?>
                    </div>

                    <!-- Upload Profile Image -->
                    <div class="form-group">
                        <label for="profile_image">Upload Profile Image (JPG, PNG, or WEBP, max 2 MB)</label>
                        <input
                            type="file"
                            id="profile_image"
                            name="profile_image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >
                    </div>

                    <!-- Read-only: User ID -->
                    <div class="form-group">
                        <label>User ID</label>
                        <p class="readonly-field"><?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <!-- Read-only: Status -->
                    <div class="form-group">
                        <label>Status</label>
                        <p class="readonly-field"><?= htmlspecialchars($userStatus ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <!-- Read-only: Account Created -->
                    <div class="form-group">
                        <label>Account Created</label>
                        <p class="readonly-field"><?= htmlspecialchars($createdAt ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            maxlength="100"
                            value="<?= htmlspecialchars($fullName ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email address"
                            maxlength="100"
                            value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone number"
                            maxlength="15"
                            value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary">Update Profile</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
?>
