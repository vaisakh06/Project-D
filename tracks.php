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

<main class="tracks-page">
    <!-- Page Intro -->
    <section class="tracks-hero">
        <div class="tracks-hero-grid">
            <div class="tracks-hero-content">
                <div class="tracks-hero-number">
                    <span class="section-num">01</span>
                    <div class="section-num-line"></div>
                </div>
                <h1 class="tracks-hero-title">AVAILABLE CIRCUITS</h1>
                <h2 class="tracks-hero-subtitle">FIND YOUR<br>TRACK</h2>
                <p class="tracks-hero-description">
                    Explore racing circuits available through INITIAL-D.
                    Choose your venue and continue to track details and booking.
                </p>
            </div>
            <div class="tracks-hero-side">
                <div class="tracks-hero-side-label">INITIAL-D</div>
                <div class="tracks-hero-side-label">CIRCUIT DIRECTORY</div>
                <div class="tracks-hero-side-label">KERALA / INDIA</div>
            </div>
        </div>
        <div class="tracks-hero-overlay">
            <div class="overlay-grid"></div>
            <div class="overlay-corner overlay-tl"></div>
            <div class="overlay-corner overlay-tr"></div>
            <div class="overlay-corner overlay-bl"></div>
            <div class="overlay-corner overlay-br"></div>
        </div>
    </section>

    <!-- Directory Bar -->
    <section class="tracks-directory-bar">
        <div class="container">
            <div class="directory-item">
                <span class="directory-label">CIRCUITS</span>
                <span class="directory-value"><?php echo count($tracks); ?></span>
            </div>
            <div class="directory-divider"></div>
            <div class="directory-item">
                <span class="directory-label">REGION</span>
                <span class="directory-value">KERALA</span>
            </div>
            <div class="directory-divider"></div>
            <div class="directory-item">
                <span class="directory-label">SYSTEM</span>
                <span class="directory-value">INITIAL-D</span>
            </div>
        </div>
    </section>

    <!-- Error messages -->
    <?php if (!empty($errors)): ?>
        <div class="container">
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Track listing -->
    <?php if (empty($errors) && !empty($tracks)): ?>
        <div class="container">
            <div class="tracks-display" id="tracks-directory">
                <?php $i = 0; foreach ($tracks as $track): ?>
                    <?php
                    // Load main track image if available.
                    $imageSql = "SELECT image_path FROM track_images WHERE track_id = ? AND is_main = 1 LIMIT 1";
                    $imageStatement = mysqli_prepare($connection, $imageSql);
                    $hasMainImage = false;
                    $mainImagePath = '';
                    if ($imageStatement) {
                        mysqli_stmt_bind_param($imageStatement, "i", $track['track_id']);
                        mysqli_stmt_execute($imageStatement);
                        mysqli_stmt_bind_result($imageStatement, $mainImagePath);
                        $hasMainImage = mysqli_stmt_fetch($imageStatement);
                        mysqli_stmt_close($imageStatement);
                    }
                    $i++;
                    $isEven = ($i % 2 === 0);
                    ?>
                    <article class="track-directory-item fade-in-up<?php echo $isEven ? ' track-reverse' : ''; ?>" style="animation-delay: <?php echo $i * 0.15; ?>s">
                        <div class="track-directory-number">
                            <span class="track-num"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="track-directory-image-wrap">
                            <?php if ($hasMainImage && !empty($mainImagePath)): ?>
                                <img src="<?= BASE_URL . htmlspecialchars($mainImagePath, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?>"
                                     class="track-directory-image"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="track-directory-image-fallback">
                                    <div class="fallback-grid"></div>
                                    <div class="fallback-corner tl"></div>
                                    <div class="fallback-corner tr"></div>
                                    <div class="fallback-corner bl"></div>
                                    <div class="fallback-corner br"></div>
                                    <div class="fallback-label">
                                        <span>RACING</span>
                                        <span>CIRCUIT</span>
                                        <span>INITIAL-D</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="track-directory-info">
                            <div class="track-directory-header">
                                <h2 class="track-directory-name"><?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            </div>
                            <div class="track-directory-meta">
                                <div class="meta-block">
                                    <span class="meta-label">LOCATION</span>
                                    <span class="meta-value"><?= htmlspecialchars($track['location'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="meta-block">
                                    <span class="meta-label">PRICE / HOUR</span>
                                    <span class="meta-value">₹<?= number_format((float)$track['price_per_hour'], 2) ?></span>
                                </div>
                                <div class="meta-block">
                                    <span class="meta-label">TYPE</span>
                                    <span class="meta-value"><?= htmlspecialchars($track['track_type'] !== null && $track['track_type'] !== '' ? $track['track_type'] : 'Not provided', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                            <a href="<?= BASE_URL . 'track.php?track_id=' . (int)$track['track_id'] ?>" class="track-directory-action">
                                VIEW TRACK <span class="arrow">→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Empty State -->
    <?php if (empty($errors) && empty($tracks)): ?>
        <div class="container">
            <div class="tracks-empty">
                <div class="section-number-block">
                    <span class="section-num">01</span>
                    <div class="section-num-line"></div>
                </div>
                <h1 class="tracks-empty-title">NO CIRCUITS</h1>
                <h2 class="tracks-empty-subtitle">CURRENTLY AVAILABLE</h2>
                <p class="tracks-empty-description">
                    Check back soon for new racing venues.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bottom CTA -->
    <section class="tracks-cta">
        <div class="container">
            <div class="tracks-cta-content">
                <div class="section-number-block">
                    <span class="section-num">02</span>
                    <div class="section-num-line"></div>
                </div>
                <h2 class="tracks-cta-title">READY TO<br>TAKE THE GRID?</h2>
                <p class="tracks-cta-description">
                    Explore a circuit, select your session,
                    and continue to booking.
                </p>
                <?php if (!empty($tracks)): ?>
                    <a href="#tracks-directory" class="cta-button-primary">VIEW AVAILABLE TRACKS</a>
                <?php else: ?>
                    <a href="<?= BASE_URL . 'register.php' ?>" class="cta-button-primary">REGISTER TO BOOK</a>
                <?php endif; ?>
            </div>
            <div class="tracks-cta-overlay">
                <div class="overlay-grid"></div>
                <div class="overlay-corner overlay-tl"></div>
                <div class="overlay-corner overlay-tr"></div>
                <div class="overlay-corner overlay-bl"></div>
                <div class="overlay-corner overlay-br"></div>
            </div>
        </div>
    </section>
</main>

<!-- Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faders = document.querySelectorAll('.fade-in-up');

    const appearOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const appearOnScroll = new IntersectionObserver(function(entries, appearOnScroll) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) {
                return;
            } else {
                entry.target.classList.add('appear');
                appearOnScroll.unobserve(entry.target);
            }
        });
    }, appearOptions);

    faders.forEach(fader => {
        appearOnScroll.observe(fader);
    });

    faders.forEach(fader => {
        if (fader.getBoundingClientRect().top < window.innerHeight &&
            fader.getBoundingClientRect().bottom >= 0) {
            fader.classList.add('appear');
        }
    });
});
</script>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
?>