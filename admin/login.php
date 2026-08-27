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

// Check if the admin login form was submitted using the POST method.
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
        $sql = "SELECT admin_id, admin_name, email, password FROM admins WHERE email = ?";
        $statement = mysqli_prepare($connection, $sql);

        if ($statement) {
            // Bind the submitted email as a string parameter.
            mysqli_stmt_bind_param($statement, "s", $email);

            // Run the prepared SELECT query.
            if (mysqli_stmt_execute($statement)) {
                // Initialize variables that will receive the database result.
                $adminId = null;
                $adminName = '';
                $adminEmail = '';
                $hashedPassword = '';

                // Store the selected columns into PHP variables.
                mysqli_stmt_bind_result($statement, $adminId, $adminName, $adminEmail, $hashedPassword);

                // Fetch one matching admin record, if it exists.
                if (mysqli_stmt_fetch($statement)) {
                    // Verify the submitted password against the stored password hash.
                    if (password_verify($password, $hashedPassword)) {
                        // Regenerate the session ID after login to reduce session fixation risk.
                        session_regenerate_id(true);

                        // Store only safe admin details in the session.
                        $_SESSION['admin_id'] = $adminId;
                        $_SESSION['admin_name'] = $adminName;
                        $_SESSION['admin_email'] = $adminEmail;
                        $_SESSION['admin_role'] = 'admin';

                        // Close the prepared statement before redirecting.
                        mysqli_stmt_close($statement);

                        // Send the logged-in admin to the future admin dashboard.
                        header("Location: " . BASE_URL . "admin/dashboard.php");
                        exit;
                    }

                    // Use a generic error so attackers cannot tell whether the password was wrong.
                    $errors[] = 'Invalid email or password.';
                } else {
                    // Use the same generic error when no admin is found.
                    $errors[] = 'Invalid email or password.';
                }
            } else {
                // Do not show internal database errors to admins because they can reveal sensitive system details.
                $errors[] = 'Something went wrong. Please try again later.';
            }

            // Close the prepared statement after the login check.
            mysqli_stmt_close($statement);
        } else {
            // Do not show internal database errors to admins because they can reveal sensitive system details.
            $errors[] = 'Something went wrong. Please try again later.';
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Admin Login';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="admin-login">
            <!-- Admin login page heading -->
            <h1>Admin Login</h1>
            <p>Login to manage the INITIAL-D administration area.</p>

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

            <!-- Admin login form -->
            <form method="POST" action="login.php" class="admin-login-form">
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
                        required
                    >
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary">Login</button>
            </form>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
