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

// Initialize variables.
$profileError = '';
$profileSuccess = '';
$businessName = '';
$ownerName = '';
$email = '';
$phone = '';
$address = '';

// Initialize the current profile image path.
$profileImage = '';

// Load current vendor data from the database.
$loadSql = "SELECT business_name, owner_name, email, phone, address, profile_image, status, created_at FROM vendors WHERE vendor_id = ? LIMIT 1";
$loadStmt = mysqli_prepare($connection, $loadSql);

if ($loadStmt) {
    mysqli_stmt_bind_param($loadStmt, "i", $vendorId);
    mysqli_stmt_execute($loadStmt);
    mysqli_stmt_bind_result($loadStmt, $businessName, $ownerName, $email, $phone, $address, $profileImage, $vendorStatus, $createdAt);
    mysqli_stmt_fetch($loadStmt);
    mysqli_stmt_close($loadStmt);
} else {
    $profileError = 'Something went wrong. Please try again later.';
}

// Handle profile update POST request.
if (isPostRequest() && isset($_POST['update_profile'])) {
    // Collect and sanitize form inputs.
    $newBusinessName = trim($_POST['business_name'] ?? '');
    $newOwnerName = trim($_POST['owner_name'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPhone = trim($_POST['phone'] ?? '');
    $newAddress = trim($_POST['address'] ?? '');

    // Validate business name.
    if ($newBusinessName === '') {
        $profileError = 'Please enter your business name.';
    } elseif (strlen($newBusinessName) < 3 || strlen($newBusinessName) > 150) {
        $profileError = 'Business name must be between 3 and 150 characters.';
    }
    // Validate owner name.
    elseif ($newOwnerName === '') {
        $profileError = 'Please enter the owner name.';
    } elseif (strlen($newOwnerName) < 3 || strlen($newOwnerName) > 100) {
        $profileError = 'Owner name must be between 3 and 100 characters.';
    }
    // Validate email.
    elseif ($newEmail === '') {
        $profileError = 'Please enter your email address.';
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $profileError = 'Please enter a valid email address.';
    } else {
        // Check email uniqueness (excluding current vendor).
        $emailCheckSql = "SELECT vendor_id FROM vendors WHERE email = ? AND vendor_id != ? LIMIT 1";
        $emailCheckStmt = mysqli_prepare($connection, $emailCheckSql);

        if ($emailCheckStmt) {
            mysqli_stmt_bind_param($emailCheckStmt, "si", $newEmail, $vendorId);
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

    // Handle the optional profile/logo image upload.
    $newProfileImage = $profileImage;
    $uploadedImagePath = '';

    if ($profileError === '' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validationResult = validateProfileImageUpload($_FILES['profile_image']);

        if (isset($validationResult['error'])) {
            $profileError = $validationResult['error'];
        } else {
            $uploadDirectory = __DIR__ . '/../' . VENDOR_IMAGE_FOLDER;
            $storeResult = storeProfileImage($_FILES['profile_image']['tmp_name'], $validationResult['extension'], 'vendor', $vendorId, $uploadDirectory, VENDOR_IMAGE_FOLDER);

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
        $updateSql = "UPDATE vendors SET business_name = ?, owner_name = ?, email = ?, phone = ?, address = ?, profile_image = ? WHERE vendor_id = ?";
        $updateStmt = mysqli_prepare($connection, $updateSql);

        if ($updateStmt) {
            mysqli_stmt_bind_param($updateStmt, "ssssssi", $newBusinessName, $newOwnerName, $newEmail, $newPhone, $newAddress, $newProfileImage, $vendorId);
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
                $_SESSION['vendor_name'] = $newBusinessName;
                $_SESSION['vendor_email'] = $newEmail;

                redirect('vendor/profile.php?updated=1');
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
        $businessName = $newBusinessName;
        $ownerName = $newOwnerName;
        $email = $newEmail;
        $phone = $newPhone;
        $address = $newAddress;
    }
}

// Get success message from URL after redirect.
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $profileSuccess = 'Profile updated successfully.';
}

// Set the page title before loading the common header.
$pageTitle = 'Vendor Profile';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="vendor-profile">
            <!-- Page header -->
            <div class="page-header">
                <h1>My Profile</h1>
                <p>View and update your vendor account information.</p>
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

                    <!-- Current Profile/Logo Image -->
                    <div class="form-group">
                        <label>Profile/Logo Image</label>
                        <?php if (!empty($profileImage)) { ?>
                            <img
                                src="<?= BASE_URL . htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Vendor logo"
                                class="profile-image"
                            >
                        <?php } else { ?>
                            <p class="readonly-field">No profile image uploaded yet.</p>
                        <?php } ?>
                    </div>

                    <!-- Upload Profile/Logo Image -->
                    <div class="form-group">
                        <label for="profile_image">Upload Profile/Logo Image (JPG, PNG, or WEBP, max 2 MB)</label>
                        <input
                            type="file"
                            id="profile_image"
                            name="profile_image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >
                    </div>

                    <!-- Read-only: Vendor ID -->
                    <div class="form-group">
                        <label>Vendor ID</label>
                        <p class="readonly-field"><?= htmlspecialchars((string) $vendorId, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <!-- Read-only: Status -->
                    <div class="form-group">
                        <label>Status</label>
                        <p class="readonly-field"><?= htmlspecialchars($vendorStatus ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <!-- Read-only: Account Created -->
                    <div class="form-group">
                        <label>Account Created</label>
                        <p class="readonly-field"><?= htmlspecialchars($createdAt ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <!-- Business Name -->
                    <div class="form-group">
                        <label for="business_name">Business Name</label>
                        <input
                            type="text"
                            id="business_name"
                            name="business_name"
                            placeholder="Enter your business name"
                            maxlength="150"
                            value="<?= htmlspecialchars($businessName ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <!-- Owner Name -->
                    <div class="form-group">
                        <label for="owner_name">Owner Name</label>
                        <input
                            type="text"
                            id="owner_name"
                            name="owner_name"
                            placeholder="Enter the owner name"
                            maxlength="100"
                            value="<?= htmlspecialchars($ownerName ?? '', ENT_QUOTES, 'UTF-8') ?>"
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

                    <!-- Address -->
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea
                            id="address"
                            name="address"
                            placeholder="Enter your business address"
                            rows="4"
                            maxlength="500"
                        ><?= htmlspecialchars($address ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
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
