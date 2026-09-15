<?php
// Common registration page for users and vendors.
// Admin registration stays private and is intentionally not offered here.
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
/** @var mysqli $connection */

require_once 'includes/functions.php';

// Allowed account types for the account selector.
$allowedTypes = ['user', 'vendor'];

// Preselect the account type from the URL or fall back to user.
$accountType = $_GET['type'] ?? 'user';

if (!in_array($accountType, $allowedTypes, true)) {
    $accountType = 'user';
}

// Create empty variables to store form values.
$fullName = '';
$businessName = '';
$ownerName = '';
$email = '';
$phone = '';
$address = '';
$password = '';
$confirmPassword = '';
$errors = [];

// Check if the registration form was submitted using the POST method.
if (isPostRequest()) {
    // Read the selected account type and fall back to user for invalid values.
    $accountType = $_POST['account_type'] ?? 'user';

    if (!in_array($accountType, $allowedTypes, true)) {
        $accountType = 'user';
    }

    // Sanitize text inputs before using them in validation or database logic.
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $businessName = sanitizeInput($_POST['business_name'] ?? '');
    $ownerName = sanitizeInput($_POST['owner_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    // Read passwords exactly as entered. Passwords should not be sanitized before hashing.
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($accountType === 'user') {
        // Validate full name.
        if ($fullName === '') {
            $errors[] = 'Please enter your full name.';
        } elseif (strlen($fullName) < 3 || strlen($fullName) > 100) {
            $errors[] = 'Full name must be between 3 and 100 characters.';
        }
    } else {
        // Validate business name.
        if ($businessName === '') {
            $errors[] = 'Please enter your business name.';
        } elseif (strlen($businessName) < 3 || strlen($businessName) > 150) {
            $errors[] = 'Business name must be between 3 and 150 characters.';
        }

        // Validate owner name.
        if ($ownerName === '') {
            $errors[] = 'Please enter the owner name.';
        } elseif (strlen($ownerName) < 3 || strlen($ownerName) > 100) {
            $errors[] = 'Owner name must be between 3 and 100 characters.';
        }

        // Validate optional address length.
        if ($address !== '' && strlen($address) > 1000) {
            $errors[] = 'Address must not be more than 1000 characters.';
        }
    }

    // Validate email address.
    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validate phone number.
    if ($phone === '') {
        $errors[] = 'Please enter your phone number.';
    } elseif (!ctype_digit($phone)) {
        $errors[] = 'Phone number must contain only digits.';
    } elseif (strlen($phone) < 10 || strlen($phone) > 15) {
        $errors[] = 'Phone number must be between 10 and 15 digits.';
    }

    // Validate password.
    if ($password === '') {
        $errors[] = 'Please enter a password.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    // Validate confirm password.
    if ($confirmPassword === '') {
        $errors[] = 'Please confirm your password.';
    } elseif ($confirmPassword !== $password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for duplicate email in both account tables so the common login stays unambiguous.
    if (empty($errors)) {
        $emailTaken = false;

        $userCheckSql = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
        $userCheckStmt = mysqli_prepare($connection, $userCheckSql);

        if ($userCheckStmt) {
            mysqli_stmt_bind_param($userCheckStmt, "s", $email);
            mysqli_stmt_execute($userCheckStmt);
            mysqli_stmt_store_result($userCheckStmt);

            if (mysqli_stmt_num_rows($userCheckStmt) > 0) {
                $emailTaken = true;
            }

            mysqli_stmt_close($userCheckStmt);
        } else {
            $errors[] = 'Something went wrong. Please try again later.';
        }

        if (empty($errors) && !$emailTaken) {
            $vendorCheckSql = "SELECT vendor_id FROM vendors WHERE email = ? LIMIT 1";
            $vendorCheckStmt = mysqli_prepare($connection, $vendorCheckSql);

            if ($vendorCheckStmt) {
                mysqli_stmt_bind_param($vendorCheckStmt, "s", $email);
                mysqli_stmt_execute($vendorCheckStmt);
                mysqli_stmt_store_result($vendorCheckStmt);

                if (mysqli_stmt_num_rows($vendorCheckStmt) > 0) {
                    $emailTaken = true;
                }

                mysqli_stmt_close($vendorCheckStmt);
            } else {
                $errors[] = 'Something went wrong. Please try again later.';
            }
        }

        if (empty($errors) && $emailTaken) {
            $errors[] = 'An account with this email address already exists.';
        }
    }

    // Register the account only when validation passes and the email is not already in use.
    if (empty($errors)) {
        // Create a secure password hash before storing the password in the database.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($accountType === 'user') {
            // Prepare the SQL query that adds the new user to the users table.
            $insertSql = "INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)";
            $insertStatement = mysqli_prepare($connection, $insertSql);

            if ($insertStatement) {
                // Bind all user values as strings to the prepared SQL statement.
                mysqli_stmt_bind_param($insertStatement, "ssss", $fullName, $email, $phone, $hashedPassword);

                // Run the INSERT query and store whether it completed successfully.
                $registrationSuccessful = mysqli_stmt_execute($insertStatement);

                // Close the INSERT statement after it has been used.
                mysqli_stmt_close($insertStatement);

                if ($registrationSuccessful) {
                    // Send the new user to the common login page after successful registration.
                    redirect('login.php?role=user&registered=1');
                }

                // Do not show internal database errors to users.
                $errors[] = 'Registration failed. Please try again.';
            } else {
                // Do not show internal database errors to users.
                $errors[] = 'Registration failed. Please try again.';
            }
        } else {
            // Prepare the SQL query that adds the new vendor to the vendors table.
            $insertSql = "INSERT INTO vendors (business_name, owner_name, email, phone, password, address) VALUES (?, ?, ?, ?, ?, ?)";
            $insertStatement = mysqli_prepare($connection, $insertSql);

            if ($insertStatement) {
                // Bind all vendor values as strings to the prepared SQL statement.
                mysqli_stmt_bind_param($insertStatement, "ssssss", $businessName, $ownerName, $email, $phone, $hashedPassword, $address);

                // Run the INSERT query and store whether it completed successfully.
                $registrationSuccessful = mysqli_stmt_execute($insertStatement);

                // Close the INSERT statement after it has been used.
                mysqli_stmt_close($insertStatement);

                if ($registrationSuccessful) {
                    // Send the new vendor to the common login page after successful registration.
                    redirect('login.php?role=vendor&registered=1');
                }

                // Do not show internal database errors to users.
                $errors[] = 'Vendor registration failed. Please try again.';
            } else {
                // Do not show internal database errors to users.
                $errors[] = 'Vendor registration failed. Please try again.';
            }
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Register';

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="registration">
            <!-- Registration page heading -->
            <h1>Create Your Account</h1>
            <p>Select your account type and register for INITIAL-D.</p>

            <?php if (!empty($errors)) { ?>
                <!-- Validation error messages -->
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error) { ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <!-- Common registration form -->
            <form method="POST" action="register.php">
                <!-- Account Type -->
                <div class="form-group">
                    <label for="account_type">Account Type</label>
                    <select id="account_type" name="account_type" required>
                        <option value="user" <?= $accountType === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="vendor" <?= $accountType === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                    </select>
                </div>

                <?php if ($accountType === 'user') { ?>
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            maxlength="100"
                            value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>
                <?php } else { ?>
                    <!-- Business Name -->
                    <div class="form-group">
                        <label for="business_name">Business Name</label>
                        <input
                            type="text"
                            id="business_name"
                            name="business_name"
                            placeholder="Enter your business name"
                            autocomplete="organization"
                            maxlength="150"
                            value="<?= htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8') ?>"
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
                            placeholder="Enter owner name"
                            autocomplete="name"
                            maxlength="100"
                            value="<?= htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>
                <?php } ?>

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        maxlength="100"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <!-- Phone Number -->
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="Enter your phone number"
                        autocomplete="tel"
                        maxlength="15"
                        value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <?php if ($accountType === 'vendor') { ?>
                    <!-- Business Address -->
                    <div class="form-group">
                        <label for="address">Business Address</label>
                        <textarea
                            id="address"
                            name="address"
                            placeholder="Enter your business address"
                            autocomplete="street-address"
                            maxlength="1000"
                        ><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                <?php } ?>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        autocomplete="new-password"
                        maxlength="255"
                        required
                    >
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        autocomplete="new-password"
                        maxlength="255"
                        required
                    >
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary">Create Account</button>
            </form>

            <!-- Login link for visitors who already have an account -->
            <p>
                Already have an account?
                <a href="<?= BASE_URL ?>login.php">Login</a>
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
