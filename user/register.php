<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Create empty variables to store form values.
$fullName = '';
$email = '';
$phone = '';
$password = '';
$confirmPassword = '';
$errors = [];

// Check if the registration form was submitted using the POST method.
if (isPostRequest()) {
    // Sanitize each submitted input before using it in validation or database logic later.
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate full name.
    if ($fullName === '') {
        $errors[] = 'Please enter your full name.';
    } elseif (strlen($fullName) < 3 || strlen($fullName) > 100) {
        $errors[] = 'Full name must be between 3 and 100 characters.';
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

    // Check for duplicate email only after all validation rules have passed.
    if (empty($errors)) {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $statement = mysqli_prepare($connection, $sql);

        if ($statement) {
            // Bind the submitted email as a string parameter.
            mysqli_stmt_bind_param($statement, "s", $email);

            // Run the prepared SELECT query.
            mysqli_stmt_execute($statement);

            // Store the result so mysqli_stmt_num_rows() can count matching rows.
            mysqli_stmt_store_result($statement);

            if (mysqli_stmt_num_rows($statement) > 0) {
                $errors[] = 'An account with this email address already exists.';
            }

            // Close the prepared statement after the duplicate email check.
            mysqli_stmt_close($statement);
        } else {
            // Do not show internal database errors to users because they can reveal sensitive system details.
            $errors[] = 'Something went wrong. Please try again later.';
        }
    }

    // Register the user only when validation passes and the email is not already in use.
    if (empty($errors)) {
        // Create a secure password hash before storing the password in the database.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

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
                // Send the new user to the login page after successful registration.
                header("Location: " . BASE_URL . "user/login.php?registered=1");
                exit;
            }

            // Do not show internal database errors to users.
            $errors[] = "Registration failed. Please try again.";
        } else {
            // Do not show internal database errors to users.
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Register';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="registration">
            <!-- Registration page heading -->
            <h1>Create Your Account</h1>
            <p>Register as a user to discover race tracks and book driving sessions.</p>

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

            <!-- User registration form interface only -->
            <form method="POST" action="register.php">
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

            <!-- Login link for users who already have an account -->
            <p>
                Already have an account?
                <a href="<?= BASE_URL ?>user/login.php">Login</a>
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
