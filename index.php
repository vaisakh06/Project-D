<?php
// Load project configuration, database connection, and helper functions.
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Fetch featured tracks for homepage (limit to 3 most recent approved tracks)
$featuredTracks = [];

$sql = "SELECT rt.track_id, rt.track_name, rt.location, rt.price_per_hour, rt.track_type,
               ti.image_path AS main_image
        FROM race_tracks rt
        LEFT JOIN track_images ti ON rt.track_id = ti.track_id AND ti.is_main = 1
        WHERE rt.status = 'Approved'
        ORDER BY rt.created_at DESC
        LIMIT 3";

$result = mysqli_query($connection, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featuredTracks[] = $row;
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Home';

// Load reusable page layout sections.
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-grid">
            <!-- Hero Content (Left Side) -->
            <div class="hero-content">
                <div class="hero-branding">
                    <div class="hero-logo">INITIAL-D</div>
                    <div class="hero-tagline">PRECISION CONTROL</div>
                </div>

                <div class="hero-text">
                    <h1 class="hero-title">BOOK<br>RACE<br>TRACKS</h1>
                    <p class="hero-subtitle">
                        Discover premium circuits and book your driving experience
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="<?= BASE_URL . 'tracks.php' ?>" class="hero-cta">EXPLORE TRACKS</a>
                    <a href="<?= BASE_URL . 'how-it-works.php' ?>" class="hero-cta-secondary">HOW IT WORKS</a>
                </div>

                <!-- Technical Data -->
                <div class="hero-data">
                    <div class="data-item">
                        <div class="data-label">SESSION</div>
                        <div class="data-value">01</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">TRACK</div>
                        <div class="data-value">03</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">LOCATION</div>
                        <div class="data-value">KERALA</div>
                    </div>
                </div>
            </div>

            <!-- Hero Visual (Right Side — LARGE CINEMATIC IMAGE) -->
            <div class="hero-visual">
                <img src="<?= BASE_URL . 'assets/images/hero-racing.png' ?>"
                     alt="Racing Circuit"
                     class="hero-image">

                <!-- Image Overlay -->
                <div class="hero-image-overlay"></div>

                <!-- Technical Overlays -->
                <div class="hero-overlay">
                    <div class="overlay-line overlay-horizontal-top"></div>
                    <div class="overlay-line overlay-horizontal-bottom"></div>
                    <div class="overlay-line overlay-vertical-left"></div>
                    <div class="overlay-line overlay-vertical-right"></div>
                    <div class="overlay-crosshair"></div>
                    <div class="overlay-corner overlay-tl"></div>
                    <div class="overlay-corner overlay-tr"></div>
                    <div class="overlay-corner overlay-bl"></div>
                    <div class="overlay-corner overlay-br"></div>
                </div>

                <!-- Image Technical Labels -->
                <div class="hero-image-labels">
                    <div class="img-label-group img-label-top-left">
                        <div class="img-label-block">
                            <span class="img-label-text">TRACK</span>
                            <span class="img-label-text img-label-sub">KERALA</span>
                        </div>
                        <div class="img-label-block">
                            <span class="img-label-text">01</span>
                        </div>
                    </div>
                    <div class="img-label-group img-label-top-right">
                        <div class="img-label-block">
                            <span class="img-label-text">DRIVE</span>
                            <span class="img-label-text img-label-sub">EXPLORE</span>
                            <span class="img-label-text img-label-sub">EXPERIENCE</span>
                        </div>
                    </div>
                    <div class="img-label-group img-label-center-left">
                        <div class="img-label-block">
                            <span class="img-label-text">CIRCUITS</span>
                            <span class="img-label-text img-label-sub">DRIVERS</span>
                            <span class="img-label-text img-label-sub">PASSION</span>
                            <span class="img-label-text img-label-sub">COMMUNITY</span>
                        </div>
                    </div>
                    <div class="img-label-group img-label-center-right">
                        <div class="img-label-block">
                            <span class="img-label-text">SPEED</span>
                            <span class="img-label-text img-label-sub">BUILT</span>
                            <span class="img-label-text img-label-sub">BETTER</span>
                            <span class="img-label-text img-label-sub">HUMANS</span>
                        </div>
                    </div>
                    <div class="img-label-group img-label-bottom-left">
                        <div class="img-label-block">
                            <span class="img-label-text">TRACK</span>
                            <span class="img-label-text img-label-sub">KERALA</span>
                        </div>
                    </div>
                    <div class="img-label-group img-label-bottom-right">
                        <div class="img-label-block">
                            <span class="img-label-text">EXPLORE</span>
                            <span class="img-label-text img-label-sub">TRACKS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="hero-scroll-indicator">
            <a href="#intro" class="scroll-down">
                <span>SCROLL DOWN</span>
                <div class="scroll-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </a>
        </div>
    </section>

    <!-- Brand Intro / Experience Section -->
    <section id="intro" class="brand-intro">
        <div class="container">
            <div class="brand-content">
                <div class="brand-section-number">
                    <span class="section-num">01</span>
                    <div class="section-num-line"></div>
                </div>
                <div class="brand-text">
                    <h2 class="brand-title">THE TRACK<br>BOOKING EXPERIENCE</h2>
                    <p class="brand-description">
                        INITIAL-D connects drivers with premium race tracks across Kerala.
                        From professional circuits to local driving grounds, we simplify the
                        process of discovering, booking, and experiencing the track.
                    </p>
                    <div class="brand-cta">
                        <a href="<?= BASE_URL . 'tracks.php' ?>" class="brand-link">MORE THAN TRACKS <span class="arrow">→</span></a>
                    </div>
                </div>
                <div class="brand-divider">
                    <div class="divider-line"></div>
                </div>
                <div class="brand-side-text">
                    <p class="side-label">A DRIVING COMMUNITY</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Tracks Section -->
    <section id="featured" class="featured-section">
        <div class="container">
            <header class="section-header">
                <div class="section-number-block">
                    <span class="section-num">02</span>
                    <div class="section-num-line"></div>
                </div>
                <div>
                    <h2 class="section-title">FEATURED TRACKS</h2>
                    <p class="section-subtitle">SELECTED CIRCUITS</p>
                </div>
            </header>

            <div class="tracks-display">
                <?php if (!empty($featuredTracks)): ?>
                    <?php $i = 1; foreach ($featuredTracks as $track): ?>
                        <article class="track-feature fade-in-up" style="animation-delay: <?php echo $i * 0.2; ?>s">
                            <div class="track-image-wrap">
                                <?php if (!empty($track['main_image'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($track['main_image'], ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?>"
                                         loading="lazy" class="track-image">
                                <?php else: ?>
                                    <div class="track-image-fallback">
                                        <div class="fallback-grid-small"></div>
                                        <div class="fallback-label">RACING TRACK</div>
                                    </div>
                                <?php endif; ?>
                                <div class="track-image-overlay"></div>
                            </div>
                            <div class="track-info">
                                <div class="track-header">
                                    <div class="track-number-badge"><?php echo $i; ?></div>
                                    <h3 class="track-name"><?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                </div>
                                <div class="track-details">
                                    <div class="detail-row">
                                        <div class="detail-label">LOCATION</div>
                                        <div class="detail-value"><?= htmlspecialchars($track['location'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-label">PRICE/HOUR</div>
                                        <div class="detail-value">₹<?= number_format((float)$track['price_per_hour'], 2) ?></div>
                                    </div>
                                    <?php if (!empty($track['track_type'])): ?>
                                        <div class="detail-row">
                                            <div class="detail-label">TYPE</div>
                                            <div class="detail-value"><?= htmlspecialchars($track['track_type'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= BASE_URL . 'track.php?track_id=' . (int)$track['track_id'] ?>" class="track-action">VIEW TRACK</a>
                            </div>
                        </article>
                    <?php $i++; endforeach; ?>
                <?php else: ?>
                    <div class="no-tracks-message">
                        <p>No tracks available at the moment</p>
                        <p>Please check back soon</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="view-all-section">
                <a href="<?= BASE_URL . 'tracks.php' ?>" class="view-all-tracks">VIEW ALL TRACKS <span class="arrow">→</span></a>
            </div>
        </div>
    </section>

    <!-- How It Works / Racing Experience Section -->
    <section id="how-it-works" class="experience-section">
        <div class="container">
            <header class="section-header">
                <div class="section-number-block">
                    <span class="section-num">03</span>
                    <div class="section-num-line"></div>
                </div>
                <div>
                    <h2 class="section-title">HOW IT WORKS</h2>
                    <p class="section-subtitle">THE BOOKING PROCESS</p>
                </div>
            </header>

            <div class="experience-grid">
                <!-- Step 1 -->
                <div class="experience-step fade-in-up" style="animation-delay: 0.2s">
                    <div class="step-number">01</div>
                    <h3 class="step-title">DISCOVER</h3>
                    <p class="step-description">
                        Browse our collection of verified race tracks.
                        Filter by location, price, and facilities.
                    </p>
                </div>
                <div class="experience-divider vertical"></div>
                <!-- Step 2 -->
                <div class="experience-step fade-in-up" style="animation-delay: 0.4s">
                    <div class="step-number">02</div>
                    <h3 class="step-title">SELECT</h3>
                    <p class="step-description">
                        Choose your preferred date and time slot.
                        Real-time availability tracking.
                    </p>
                </div>
                <div class="experience-divider vertical"></div>
                <!-- Step 3 -->
                <div class="experience-step fade-in-up" style="animation-delay: 0.6s">
                    <div class="step-number">03</div>
                    <h3 class="step-title">BOOK</h3>
                    <p class="step-description">
                        Secure booking with instant confirmation.
                        All major payment methods accepted.
                    </p>
                </div>
                <div class="experience-divider vertical"></div>
                <!-- Step 4 -->
                <div class="experience-step fade-in-up" style="animation-delay: 0.8s">
                    <div class="step-number">04</div>
                    <h3 class="step-title">DRIVE</h3>
                    <p class="step-description">
                        Arrive at the venue, check in with your booking reference,
                        and experience the track.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Large CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <div class="section-number-block">
                    <span class="section-num">04</span>
                    <div class="section-num-line"></div>
                </div>
                <div class="cta-inner">
                    <h2 class="cta-title">READY TO BOOK<br>YOUR SESSION?</h2>
                    <p class="cta-description">
                        Browse available tracks, select your preferred time,
                        and confirm your booking in minutes.
                    </p>
                    <div class="cta-action-group">
                        <a href="<?= BASE_URL . 'tracks.php' ?>" class="cta-button-primary">EXPLORE TRACKS</a>
                        <a href="<?= BASE_URL . 'about.php' ?>" class="cta-button-secondary">LEARN MORE</a>
                    </div>
                </div>
                <!-- Technical overlay -->
                <div class="cta-overlay">
                    <div class="overlay-grid"></div>
                    <div class="overlay-corner overlay-tl"></div>
                    <div class="overlay-corner overlay-tr"></div>
                    <div class="overlay-corner overlay-bl"></div>
                    <div class="overlay-corner overlay-br"></div>
                </div>
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

    // Hero entrance animation
    setTimeout(() => {
        document.querySelector('.hero-content').style.opacity = '1';
        document.querySelector('.hero-content').style.transform = 'translateY(0)';
        document.querySelector('.hero-visual').style.opacity = '1';
        document.querySelector('.hero-visual').style.transform = 'translateY(0)';

        const heroImg = document.querySelector('.hero-image');
        const heroOverlay = document.querySelector('.hero-image-overlay');
        if (heroImg) {
            heroImg.classList.add('visible');
        }
        if (heroOverlay && heroImg) {
            heroImg.addEventListener('load', function() {
                heroOverlay.classList.add('ready');
            });
            if (heroImg.complete) {
                heroOverlay.classList.add('ready');
            }
        }
    }, 300);
});
</script>

<?php
// Load the common footer and close the HTML document.
require_once 'includes/footer.php';
?>