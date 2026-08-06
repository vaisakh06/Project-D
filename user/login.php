<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Create empty variables to store form values and messages.
$email = '';
$password = '';
$errors = [];
$successMessage = '';

// Show a simple success message after successful registration.
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $successMessage = 'Registration successful. You can now log in.';
} elseif (isset($_GET['logged_out']) && $_GET['logged_out'] === '1') {
    $successMessage = 'You have been logged out successfully.';
}

// Check if the login form was submitted using the POST method.
if (isPostRequest()) {
    // Sanitize the email because it may be displayed again in the form.
    $email = sanitizeInput($_POST['email'] ?? '');

    // Read the password exactly as entered. Passwords should not be sanitized before verification.
    $password = $_POST['password'] ?? '';

    // Validate email address.
    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validate password.
    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    // Only check the database after basic validation passes.
    if (empty($errors)) {
        $sql = "SELECT user_id, full_name, email, password, status FROM users WHERE email = ?";
        $statement = mysqli_prepare($connection, $sql);

        if ($statement) {
            // Bind the submitted email as a string parameter.
            mysqli_stmt_bind_param($statement, "s", $email);

            // Run the prepared SELECT query.
            mysqli_stmt_execute($statement);


            // Initialize variables that will receive the database result.
            $userId = null;
            $fullName = '';
            $userEmail = '';
            $hashedPassword = '';
            $status = '';

            // Store the selected columns into PHP variables.
            mysqli_stmt_bind_result($statement, $userId, $fullName, $userEmail, $hashedPassword, $status);

            // Fetch one matching user record, if it exists.
            if (mysqli_stmt_fetch($statement)) {
                // Verify the submitted password against the stored password hash.
                if (password_verify($password, $hashedPassword)) {
                    if ($status === 'Blocked') {
                        $errors[] = 'Your account has been blocked. Please contact the administrator.';
                    } else {
                        // Regenerate the session ID after login to reduce session fixation risk.
                        session_regenerate_id(true);

                        // Store only safe user details in the session.
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['user_name'] = $fullName;
                        $_SESSION['user_email'] = $userEmail;
                        $_SESSION['user_role'] = 'user';

                        // Close the prepared statement before redirecting.
                        mysqli_stmt_close($statement);

                        // Send the logged-in user to the homepage until a dashboard is created.
                        header("Location: " . BASE_URL . "index.php");
                        exit;
                    }
                } else {
                    // Use a generic error so attackers cannot tell whether the email exists.
                    $errors[] = 'Invalid email or password.';
                }
            } else {
                // Use the same generic error when no user is found.
                $errors[] = 'Invalid email or password.';
            }

            // Close the prepared statement after the login check.
            mysqli_stmt_close($statement);
        } else {
            // Do not show internal database errors to users because they can reveal sensitive system details.
            $errors[] = 'Something went wrong. Please try again later.';
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Login';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="login">
            <!-- Login page heading -->
            <h1>Login to Your Account</h1>
            <p>Access your account to manage race track bookings.</p>

            <?php if ($successMessage !== '') { ?>
                <!-- Registration success message -->
                <div class="success-message">
                    <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php } ?>

            <?php if (!empty($errors)) { ?>
                <!-- Validation and login error messages -->
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error) { ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <!-- User login form -->
            <form method="POST" action="login.php" class="login-form">
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
                        required>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        maxlength="255"
                        required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary">Login</button>
            </form>

            <!-- Registration link for users who do not have an account -->
            <p>
                Do not have an account?
                <a href="<?= BASE_URL . 'user/register.php' ?>">Create an account</a>
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
