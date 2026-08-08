<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Create empty variables to store form values.
$businessName = '';
$ownerName = '';
$email = '';
$phone = '';
$address = '';
$password = '';
$confirmPassword = '';
$errors = [];

// Check if the vendor registration form was submitted using the POST method.
if (isPostRequest()) {
    // Sanitize text inputs before using them in validation or database logic.
    $businessName = sanitizeInput($_POST['business_name'] ?? '');
    $ownerName = sanitizeInput($_POST['owner_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    // Read passwords exactly as entered. Passwords should not be sanitized before hashing.
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

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

    // Validate optional address length.
    if ($address !== '' && strlen($address) > 1000) {
        $errors[] = 'Address must not be more than 1000 characters.';
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

    // Check for duplicate vendor email only after all validation rules have passed.
    if (empty($errors)) {
        $sql = "SELECT vendor_id FROM vendors WHERE email = ?";
        $statement = mysqli_prepare($connection, $sql);

        if ($statement) {
            // Bind the submitted email as a string parameter.
            mysqli_stmt_bind_param($statement, "s", $email);

            // Run the prepared SELECT query.
            mysqli_stmt_execute($statement);

            // Store the result so mysqli_stmt_num_rows() can count matching rows.
            mysqli_stmt_store_result($statement);

            if (mysqli_stmt_num_rows($statement) > 0) {
                $errors[] = 'A vendor account with this email address already exists.';
            }

            // Close the prepared statement after the duplicate email check.
            mysqli_stmt_close($statement);
        } else {
            // Do not show internal database errors to users because they can reveal sensitive system details.
            $errors[] = 'Something went wrong. Please try again later.';
        }
    }

    // Register the vendor only when validation passes and the email is not already in use.
    if (empty($errors)) {
        // Create a secure password hash before storing the password in the database.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

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
                // Send the new vendor to the future vendor login page after successful registration.
                header("Location: " . BASE_URL . "vendor/login.php?registered=1");
                exit;
            }

            // Do not show internal database errors to users.
            $errors[] = "Vendor registration failed. Please try again.";
        } else {
            // Do not show internal database errors to users.
            $errors[] = "Vendor registration failed. Please try again.";
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Vendor Register';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="vendor-registration">
            <!-- Vendor registration page heading -->
            <h1>Create Vendor Account</h1>
            <p>Register your business to list race tracks and manage future booking requests.</p>

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

            <!-- Vendor registration form -->
            <form method="POST" action="register.php">
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
                <button type="submit" class="btn-primary">Create Vendor Account</button>
            </form>

            <!-- Login link for vendors who already have an account -->
            <p>
                Already have a vendor account?
                <a href="<?= BASE_URL . 'vendor/login.php' ?>">Login</a>
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
