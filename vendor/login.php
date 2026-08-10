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

// Show a simple success message after vendor registration.
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $successMessage = 'Vendor registration successful. Your account is now awaiting administrator approval.';
}

// Check if the vendor login form was submitted using the POST method.
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
        $sql = "SELECT vendor_id, business_name, email, password, status FROM vendors WHERE email = ?";
        $statement = mysqli_prepare($connection, $sql);

        if ($statement) {
            // Bind the submitted email as a string parameter.
            mysqli_stmt_bind_param($statement, "s", $email);

            // Run the prepared SELECT query.
            mysqli_stmt_execute($statement);

            // Initialize variables that will receive the database result.
            $vendorId = null;
            $businessName = '';
            $vendorEmail = '';
            $hashedPassword = '';
            $status = '';

            // Store the selected columns into PHP variables.
            mysqli_stmt_bind_result($statement, $vendorId, $businessName, $vendorEmail, $hashedPassword, $status);

            // Fetch one matching vendor record, if it exists.
            if (mysqli_stmt_fetch($statement)) {
                // Verify the submitted password against the stored password hash.
                if (password_verify($password, $hashedPassword)) {
                    if ($status === 'Pending') {
                        $errors[] = 'Your vendor account is awaiting administrator approval.';
                    } elseif ($status === 'Blocked') {
                        $errors[] = 'Your vendor account has been blocked. Please contact the administrator.';
                    } elseif ($status === 'Approved') {
                        // Regenerate the session ID after login to reduce session fixation risk.
                        session_regenerate_id(true);

                        // Store only safe vendor details in the session.
                        $_SESSION['vendor_id'] = $vendorId;
                        $_SESSION['vendor_name'] = $businessName;
                        $_SESSION['vendor_email'] = $vendorEmail;
                        $_SESSION['vendor_role'] = 'vendor';

                        // Close the prepared statement before redirecting.
                        mysqli_stmt_close($statement);

                        // Send the approved vendor to the future vendor dashboard.
                        header("Location: " . BASE_URL . "vendor/dashboard.php");
                        exit;
                    } else {
                        // Use a generic error if the account status is not recognized.
                        $errors[] = 'Invalid email or password.';
                    }
                } else {
                    // Use a generic error so attackers cannot tell whether the email exists.
                    $errors[] = 'Invalid email or password.';
                }
            } else {
                // Use the same generic error when no vendor is found.
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
$pageTitle = 'Vendor Login';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="vendor-login">
            <!-- Vendor login page heading -->
            <h1>Vendor Login</h1>
            <p>Login to manage your race track business account.</p>

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

            <!-- Vendor login form -->
            <form method="POST" action="login.php" class="vendor-login-form">
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

            <!-- Registration link for vendors who do not have an account -->
            <p>
                Do not have a vendor account?
                <a href="<?= BASE_URL . 'vendor/register.php' ?>">Create a vendor account</a>
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
