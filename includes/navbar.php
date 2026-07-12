<?php
/**
 * Common navigation bar for INITIAL-D.
 *
 * This file contains the shared website navigation links.
 * It does not include login checking or database logic.
 */
require_once __DIR__ . '/../config/constants.php';
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
            <li>
                <a href="<?= BASE_URL . 'user/login.php' ?>">Login</a>
            </li>
            <li>
                <a href="<?= BASE_URL . 'user/register.php' ?>">Register</a>
            </li>
        </ul>
    </div>
</nav>
