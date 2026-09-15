<?php
// Common login page for users, vendors, and admins.
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
/** @var mysqli $connection */

require_once 'includes/functions.php';

// Allowed roles for the role selector.
$allowedRoles = ['user', 'vendor', 'admin'];

// Preselect the role from the URL or fall back to user.
$role = $_GET['role'] ?? 'user';

if (!in_array($role, $allowedRoles, true)) {
    $role = 'user';
}

// Create empty variables to store form values and messages.
$email = '';
$password = '';
$errors = [];
$successMessage = '';

// Show a simple success message after registration or logout.
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    if ($role === 'vendor') {
        $successMessage = 'Vendor registration successful. Your account is now awaiting administrator approval.';
    } else {
        $successMessage = 'Registration successful. You can now log in.';
    }
} elseif (isset($_GET['logged_out']) && $_GET['logged_out'] === '1') {
    $successMessage = 'You have been logged out successfully.';
}

// Check if the login form was submitted using the POST method.
if (isPostRequest()) {
    // Read the selected role and fall back to user for invalid values.
    $role = $_POST['role'] ?? 'user';

    if (!in_array($role, $allowedRoles, true)) {
        $role = 'user';
    }

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
        if ($role === 'user') {
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

                            // Send the logged-in user to the homepage, preserving the old user login behavior.
                            redirect('index.php');
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
        } elseif ($role === 'vendor') {
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

                            // Send the approved vendor to the vendor dashboard.
                            redirect('vendor/dashboard.php');
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
        } else {
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

                            // Send the logged-in admin to the admin dashboard.
                            redirect('admin/dashboard.php');
                        }

                        // Use a generic error so attackers cannot tell whether the password was wrong.
                        $errors[] = 'Invalid email or password.';
                    } else {
                        // Use the same generic error when no admin is found.
                        $errors[] = 'Invalid email or password.';
                    }
                } else {
                    // Do not show internal database errors because they can reveal sensitive system details.
                    $errors[] = 'Something went wrong. Please try again later.';
                }

                // Close the prepared statement after the login check.
                mysqli_stmt_close($statement);
            } else {
                // Do not show internal database errors because they can reveal sensitive system details.
                $errors[] = 'Something went wrong. Please try again later.';
            }
        }
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Login';

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="login">
            <!-- Login page heading -->
            <h1>Login to Your Account</h1>
            <p>Select your account type and access your INITIAL-D account.</p>

            <?php if ($successMessage !== '') { ?>
                <!-- Registration or logout success message -->
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

            <!-- Common login form -->
            <form method="POST" action="login.php" class="login-form">
                <!-- Account Type -->
                <div class="form-group">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" required>
                        <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="vendor" <?= $role === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
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

            <!-- Registration link for visitors who do not have an account -->
            <p>
                Do not have an account?
                <a href="<?= BASE_URL . 'register.php' ?>">Create an account</a>
            </p>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
