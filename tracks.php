<?php
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
/** @var mysqli $connection */

require_once 'includes/functions.php';

// Set the page title before loading the common header.
$pageTitle = 'Tracks';

// Retrieve available and approved race tracks from the database.
$tracks = [];
$errors = [];

$sql = "SELECT rt.track_id, rt.track_name, rt.location, rt.description, rt.price_per_hour, rt.track_type, rt.opening_time, rt.closing_time, rt.status, v.business_name, v.owner_name
        FROM race_tracks rt
        INNER JOIN vendors v ON rt.vendor_id = v.vendor_id
        WHERE rt.status = 'Approved'
        ORDER BY rt.created_at DESC";

$statement = mysqli_prepare($connection, $sql);

if ($statement) {
    if (mysqli_stmt_execute($statement)) {
        $trackId = null;
        $trackName = '';
        $location = '';
        $description = '';
        $pricePerHour = '';
        $trackType = '';
        $openingTime = '';
        $closingTime = '';
        $status = '';
        $businessName = '';
        $ownerName = '';

        mysqli_stmt_bind_result(
            $statement,
            $trackId,
            $trackName,
            $location,
            $description,
            $pricePerHour,
            $trackType,
            $openingTime,
            $closingTime,
            $status,
            $businessName,
            $ownerName
        );

        while (mysqli_stmt_fetch($statement)) {
            $tracks[] = [
                'track_id' => $trackId,
                'track_name' => $trackName,
                'location' => $location,
                'description' => $description,
                'price_per_hour' => $pricePerHour,
                'track_type' => $trackType,
                'opening_time' => $openingTime,
                'closing_time' => $closingTime,
                'status' => $status,
                'business_name' => $businessName,
                'owner_name' => $ownerName,
            ];
        }
    } else {
        $errors[] = 'Unable to load race tracks. Please try again later.';
    }

    mysqli_stmt_close($statement);
} else {
    $errors[] = 'Unable to load race tracks. Please try again later.';
}

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <div class="container">
        <!-- Track listing page heading -->
        <div class="page-header">
            <h1>Race Tracks</h1>
            <p>Browse available race tracks and discover exciting driving experiences.</p>
        </div>

        <!-- Error messages -->
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Empty message -->
        <?php if (empty($errors) && empty($tracks)): ?>
            <p>No race tracks are available at the moment. Please check back later.</p>
        <?php endif; ?>

        <!-- Track listing -->
        <?php if (!empty($tracks)): ?>
            <div class="track-list">
                <?php foreach ($tracks as $track): ?>
                    <article class="track-item">
                        <div class="track-item-image-wrap">
                            <?php
                            // Load main track image if available.
                            $imageSql = "SELECT image_path FROM track_images WHERE track_id = ? AND is_main = 1 LIMIT 1";
                            $imageStatement = mysqli_prepare($connection, $imageSql);
                            if ($imageStatement) {
                                mysqli_stmt_bind_param($imageStatement, "i", $track['track_id']);
                                mysqli_stmt_execute($imageStatement);
                                mysqli_stmt_bind_result($imageStatement, $mainImagePath);
                                $hasMainImage = mysqli_stmt_fetch($imageStatement);
                                mysqli_stmt_close($imageStatement);
                            } else {
                                $hasMainImage = false;
                            }

                            if ($hasMainImage && !empty($mainImagePath)): ?>
                                <img
                                    src="<?= BASE_URL . htmlspecialchars($mainImagePath, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?>"
                                    class="track-item-image"
                                >
                            <?php else: ?>
                                <div class="track-item-image-fallback">
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="track-item-details">
                            <h2><?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?></h2>

                            <div class="track-item-meta">
                                <p class="track-item-location">
                                    <span class="meta-icon">📍</span>
                                    <span><?= htmlspecialchars($track['location'], ENT_QUOTES, 'UTF-8') ?></span>
                                </p>
                                <p class="track-item-price">
                                    <span class="meta-label">Price:</span>
                                    <span class="price-value"><?= htmlspecialchars($track['price_per_hour'], ENT_QUOTES, 'UTF-8') ?></span>
                                </p>
                            </div>

                            <p class="track-item-description">
                                <?= htmlspecialchars($track['description'] ?? 'No description provided.', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <div class="track-item-meta-row">
                                <p class="track-item-type">
                                    <span class="meta-label">Type:</span>
                                    <span><?= htmlspecialchars($track['track_type'] !== null && $track['track_type'] !== '' ? $track['track_type'] : 'Not provided', ENT_QUOTES, 'UTF-8') ?></span>
                                </p>
                                <p class="track-item-vendor">
                                    <span class="meta-label">Venue:</span>
                                    <span><?= htmlspecialchars($track['business_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                </p>
                            </div>

                            <p class="track-item-hours">
                                <span class="meta-label">Hours:</span>
                                <span><?= htmlspecialchars($track['opening_time'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($track['closing_time'], ENT_QUOTES, 'UTF-8') ?></span>
                            </p>

                            <a href="<?= BASE_URL . 'track.php?track_id=' . htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">View Details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
?>