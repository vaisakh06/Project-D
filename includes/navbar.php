<?php
/**
 * Common navigation bar for INITIAL-D.
 *
 * This file contains the shared website navigation links.
 * It does not include login checking or database logic.
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';
?>
<nav>
    <div class="container">
        <!-- Brand -->
        <h1 class="logo">
            <a href="<?= BASE_URL ?>">
                <?= SITE_NAME ?>
            </a>
        </h1>

        <!-- Main Navigation -->
        <ul>
            <li>
                <a href="<?= BASE_URL . 'index.php' ?>">Home</a>
            </li>
            <li>
                <a href="<?= BASE_URL . 'tracks.php' ?>">Tracks</a>
            </li>
            <li>
                <a href="<?= BASE_URL . 'about.php' ?>">About</a>
            </li>
            <li>
                <a href="<?= BASE_URL . 'contact.php' ?>">Contact</a>
            </li>

            <!-- Authentication -->
            <?php if (isUserLoggedIn()) { ?>
                <li>
                    <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?>
                </li>
                <li>
                    <a href="<?= BASE_URL . 'user/dashboard.php' ?>">Dashboard</a>
                </li>
                <li>
                    <a href="<?= BASE_URL . 'user/logout.php' ?>">Logout</a>
                </li>
            <?php } else { ?>
                <li>
                    <a href="<?= BASE_URL . 'user/login.php' ?>">Login</a>
                </li>
                <li>
                    <a href="<?= BASE_URL . 'user/register.php' ?>">Register</a>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>
