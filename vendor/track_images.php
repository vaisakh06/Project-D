<?php
// Load project configuration, database connection, and helper functions.
require_once '../config/constants.php';
require_once '../config/database.php';
/** @var mysqli $connection */

require_once '../includes/functions.php';

// Protect this page so only logged-in vendors can access it.
if (!isset($_SESSION['vendor_id'], $_SESSION['vendor_role']) || $_SESSION['vendor_role'] !== 'vendor') {
    redirect('vendor/login.php');
}

// Always use the authenticated vendor ID from the session.
$vendorId = $_SESSION['vendor_id'];

// Create variables to store page data and messages.
$tracks = [];
$images = [];
$errors = [];
$successMessage = '';
$selectedTrackId = 0;
$selectedTrackName = '';
$selectedTrackFound = false;

// The uploaded file will be stored inside the existing track upload folder.
$uploadDirectory = __DIR__ . '/../' . TRACK_IMAGE_FOLDER;
$maxFileSize = 2 * 1024 * 1024;
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

// Show simple success messages after image actions are completed.
if (isset($_GET['uploaded']) && $_GET['uploaded'] === '1') {
    $successMessage = 'Track image uploaded successfully.';
} elseif (isset($_GET['main_updated']) && $_GET['main_updated'] === '1') {
    $successMessage = 'Main track image updated successfully.';
} elseif (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $successMessage = 'Track image deleted successfully.';
}

// Validate the selected track ID from the URL or submitted form.
$trackIdInput = $_GET['track_id'] ?? ($_POST['track_id'] ?? '');

if ($trackIdInput !== '') {
    if (ctype_digit((string) $trackIdInput) && (int) $trackIdInput > 0) {
        $selectedTrackId = (int) $trackIdInput;
    } else {
        $errors[] = 'Race track not found.';
    }
}

// Load all race tracks belonging to the logged-in vendor for the track selector.
$tracksSql = "SELECT track_id, track_name, location
    FROM race_tracks
    WHERE vendor_id = ?
    ORDER BY track_name ASC";

$tracksStatement = mysqli_prepare($connection, $tracksSql);

if ($tracksStatement) {
    // Bind the logged-in vendor ID as an integer.
    mysqli_stmt_bind_param($tracksStatement, "i", $vendorId);

    // Run the prepared SELECT query.
    if (mysqli_stmt_execute($tracksStatement)) {
        $trackId = null;
        $trackName = '';
        $location = '';

        // Store each track row into PHP variables.
        mysqli_stmt_bind_result($tracksStatement, $trackId, $trackName, $location);

        // Build a simple list of this vendor's tracks.
        while (mysqli_stmt_fetch($tracksStatement)) {
            $tracks[] = [
                'track_id' => $trackId,
                'track_name' => $trackName,
                'location' => $location,
            ];
        }
    } else {
        // Do not show internal database errors to vendors.
        $errors[] = 'Unable to load race tracks. Please try again later.';
    }

    // Close the prepared statement after loading tracks.
    mysqli_stmt_close($tracksStatement);
} else {
    // Do not show internal database errors to vendors.
    $errors[] = 'Unable to load race tracks. Please try again later.';
}

// Verify the selected track belongs to the logged-in vendor.
if ($selectedTrackId > 0) {
    $trackSql = "SELECT track_name
        FROM race_tracks
        WHERE track_id = ?
        AND vendor_id = ?
        LIMIT 1";

    $trackStatement = mysqli_prepare($connection, $trackSql);

    if ($trackStatement) {
        // Bind both track ID and vendor ID so vendors cannot access another vendor's track.
        mysqli_stmt_bind_param($trackStatement, "ii", $selectedTrackId, $vendorId);

        if (mysqli_stmt_execute($trackStatement)) {
            mysqli_stmt_bind_result($trackStatement, $selectedTrackName);

            if (mysqli_stmt_fetch($trackStatement)) {
                $selectedTrackFound = true;
            } else {
                $errors[] = 'Race track not found.';
            }
        } else {
            // Do not show internal database errors to vendors.
            $errors[] = 'Unable to load the selected race track. Please try again later.';
        }

        // Close the prepared statement after checking track ownership.
        mysqli_stmt_close($trackStatement);
    } else {
        // Do not show internal database errors to vendors.
        $errors[] = 'Unable to load the selected race track. Please try again later.';
    }
}

// Handle image upload, set main, and delete actions for the selected vendor-owned track.
if (isPostRequest() && $selectedTrackFound) {
    $action = sanitizeInput($_POST['action'] ?? '');

    if ($action === 'upload') {
        // Validate that a file was uploaded.
        if (!isset($_FILES['track_image'])) {
            $errors[] = 'Please choose an image to upload.';
        } elseif ($_FILES['track_image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed. Please try again.';
        } elseif ($_FILES['track_image']['size'] > $maxFileSize) {
            $errors[] = 'Image size must not be more than 2 MB.';
        } else {
            $originalFileName = $_FILES['track_image']['name'];
            $temporaryFilePath = $_FILES['track_image']['tmp_name'];
            $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

            // Validate the file extension.
            if (!in_array($fileExtension, $allowedExtensions, true)) {
                $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
            }

            // Validate the MIME type using PHP's file information functions.
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($fileInfo === false) {
                $errors[] = 'Unable to validate the uploaded image. Please try again.';
            } else {
                $mimeType = finfo_file($fileInfo, $temporaryFilePath);
                finfo_close($fileInfo);

                if (!in_array($mimeType, $allowedMimeTypes, true)) {
                    $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
                }
            }

            // Store the file only after upload validation passes.
            if (empty($errors)) {
                if (!is_dir($uploadDirectory)) {
                    $errors[] = 'Track image upload folder is not available.';
                } else {
                    $safeFileName = 'track_' . $selectedTrackId . '_' . str_replace('.', '_', uniqid('', true)) . '.' . $fileExtension;
                    $storedImagePath = TRACK_IMAGE_FOLDER . $safeFileName;
                    $destinationPath = $uploadDirectory . $safeFileName;

                    // Check how many images this track already has.
                    $countSql = "SELECT COUNT(*) FROM track_images WHERE track_id = ?";
                    $countStatement = mysqli_prepare($connection, $countSql);
                    $imageCount = 0;

                    if ($countStatement) {
                        mysqli_stmt_bind_param($countStatement, "i", $selectedTrackId);

                        if (mysqli_stmt_execute($countStatement)) {
                            mysqli_stmt_bind_result($countStatement, $imageCount);
                            mysqli_stmt_fetch($countStatement);
                        } else {
                            $errors[] = 'Unable to upload the track image. Please try again later.';
                        }

                        mysqli_stmt_close($countStatement);
                    } else {
                        $errors[] = 'Unable to upload the track image. Please try again later.';
                    }

                    if (empty($errors)) {
                        $isMain = ((int) $imageCount === 0) ? 1 : 0;

                        if (move_uploaded_file($temporaryFilePath, $destinationPath)) {
                            // Store only the relative image path in the database.
                            $insertSql = "INSERT INTO track_images (track_id, image_path, is_main)
                                VALUES (?, ?, ?)";

                            $insertStatement = mysqli_prepare($connection, $insertSql);

                            if ($insertStatement) {
                                mysqli_stmt_bind_param($insertStatement, "isi", $selectedTrackId, $storedImagePath, $isMain);

                                $imageInserted = mysqli_stmt_execute($insertStatement);

                                mysqli_stmt_close($insertStatement);

                                if ($imageInserted) {
                                    redirect('vendor/track_images.php?track_id=' . $selectedTrackId . '&uploaded=1');
                                }

                                // Remove the uploaded file if the database insert fails.
                                if (is_file($destinationPath)) {
                                    unlink($destinationPath);
                                }

                                $errors[] = 'Unable to save the track image. Please try again later.';
                            } else {
                                // Remove the uploaded file if the database statement cannot be prepared.
                                if (is_file($destinationPath)) {
                                    unlink($destinationPath);
                                }

                                $errors[] = 'Unable to save the track image. Please try again later.';
                            }
                        } else {
                            $errors[] = 'Unable to upload the track image. Please try again.';
                        }
                    }
                }
            }
        }
    } elseif ($action === 'set_main') {
        $imageIdInput = sanitizeInput($_POST['image_id'] ?? '');

        if (!ctype_digit($imageIdInput) || (int) $imageIdInput <= 0) {
            $errors[] = 'Track image not found.';
        } else {
            $imageId = (int) $imageIdInput;

            // Verify the selected image belongs to the selected vendor-owned track.
            $imageSql = "SELECT ti.image_id
                FROM track_images ti
                INNER JOIN race_tracks rt ON ti.track_id = rt.track_id
                WHERE ti.image_id = ?
                AND ti.track_id = ?
                AND rt.vendor_id = ?
                LIMIT 1";

            $imageStatement = mysqli_prepare($connection, $imageSql);

            if ($imageStatement) {
                mysqli_stmt_bind_param($imageStatement, "iii", $imageId, $selectedTrackId, $vendorId);

                if (mysqli_stmt_execute($imageStatement)) {
                    mysqli_stmt_store_result($imageStatement);

                    if (mysqli_stmt_num_rows($imageStatement) === 0) {
                        $errors[] = 'Track image not found.';
                    }
                } else {
                    $errors[] = 'Unable to update the main image. Please try again later.';
                }

                mysqli_stmt_close($imageStatement);
            } else {
                $errors[] = 'Unable to update the main image. Please try again later.';
            }

            if (empty($errors)) {
                // Remove the main flag from all images for this track.
                $clearMainSql = "UPDATE track_images SET is_main = 0 WHERE track_id = ?";
                $clearMainStatement = mysqli_prepare($connection, $clearMainSql);

                if ($clearMainStatement) {
                    mysqli_stmt_bind_param($clearMainStatement, "i", $selectedTrackId);

                    if (!mysqli_stmt_execute($clearMainStatement)) {
                        $errors[] = 'Unable to update the main image. Please try again later.';
                    }

                    mysqli_stmt_close($clearMainStatement);
                } else {
                    $errors[] = 'Unable to update the main image. Please try again later.';
                }
            }

            if (empty($errors)) {
                // Mark the selected image as the main image.
                $setMainSql = "UPDATE track_images SET is_main = 1 WHERE image_id = ? AND track_id = ?";
                $setMainStatement = mysqli_prepare($connection, $setMainSql);

                if ($setMainStatement) {
                    mysqli_stmt_bind_param($setMainStatement, "ii", $imageId, $selectedTrackId);

                    if (mysqli_stmt_execute($setMainStatement)) {
                        redirect('vendor/track_images.php?track_id=' . $selectedTrackId . '&main_updated=1');
                    }

                    mysqli_stmt_close($setMainStatement);
                    $errors[] = 'Unable to update the main image. Please try again later.';
                } else {
                    $errors[] = 'Unable to update the main image. Please try again later.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $imageIdInput = sanitizeInput($_POST['image_id'] ?? '');

        if (!ctype_digit($imageIdInput) || (int) $imageIdInput <= 0) {
            $errors[] = 'Track image not found.';
        } else {
            $imageId = (int) $imageIdInput;
            $imagePath = '';
            $isMainImage = 0;
            $imageFound = false;

            // Load the image only if it belongs to a track owned by the logged-in vendor.
            $imageSql = "SELECT ti.image_path, ti.is_main
                FROM track_images ti
                INNER JOIN race_tracks rt ON ti.track_id = rt.track_id
                WHERE ti.image_id = ?
                AND ti.track_id = ?
                AND rt.vendor_id = ?
                LIMIT 1";

            $imageStatement = mysqli_prepare($connection, $imageSql);

            if ($imageStatement) {
                mysqli_stmt_bind_param($imageStatement, "iii", $imageId, $selectedTrackId, $vendorId);

                if (mysqli_stmt_execute($imageStatement)) {
                    mysqli_stmt_bind_result($imageStatement, $imagePath, $isMainImage);

                    if (mysqli_stmt_fetch($imageStatement)) {
                        $imageFound = true;
                    } else {
                        $errors[] = 'Track image not found.';
                    }
                } else {
                    $errors[] = 'Unable to delete the track image. Please try again later.';
                }

                mysqli_stmt_close($imageStatement);
            } else {
                $errors[] = 'Unable to delete the track image. Please try again later.';
            }

            if (empty($errors) && $imageFound) {
                // Build a safe file path inside the configured track upload folder.
                $storedFileName = basename($imagePath);
                $filePath = $uploadDirectory . $storedFileName;

                // Delete only the intended uploaded file from the configured upload folder.
                if (is_file($filePath) && !unlink($filePath)) {
                    $errors[] = 'Unable to delete the track image file. Please try again later.';
                }

                if (empty($errors)) {
                    // Delete the database record after the file path has been handled safely.
                    $deleteSql = "DELETE FROM track_images WHERE image_id = ? AND track_id = ?";
                    $deleteStatement = mysqli_prepare($connection, $deleteSql);

                    if ($deleteStatement) {
                        mysqli_stmt_bind_param($deleteStatement, "ii", $imageId, $selectedTrackId);

                        if (mysqli_stmt_execute($deleteStatement)) {
                            // If the deleted image was main, choose another image as main if one remains.
                            if ((int) $isMainImage === 1) {
                                $remainingImageId = 0;
                                $remainingSql = "SELECT image_id
                                    FROM track_images
                                    WHERE track_id = ?
                                    ORDER BY image_id ASC
                                    LIMIT 1";

                                $remainingStatement = mysqli_prepare($connection, $remainingSql);

                                if ($remainingStatement) {
                                    mysqli_stmt_bind_param($remainingStatement, "i", $selectedTrackId);

                                    if (mysqli_stmt_execute($remainingStatement)) {
                                        mysqli_stmt_bind_result($remainingStatement, $remainingImageId);
                                        mysqli_stmt_fetch($remainingStatement);
                                    }

                                    mysqli_stmt_close($remainingStatement);
                                }

                                if ($remainingImageId > 0) {
                                    $newMainSql = "UPDATE track_images SET is_main = 1 WHERE image_id = ? AND track_id = ?";
                                    $newMainStatement = mysqli_prepare($connection, $newMainSql);

                                    if ($newMainStatement) {
                                        mysqli_stmt_bind_param($newMainStatement, "ii", $remainingImageId, $selectedTrackId);
                                        mysqli_stmt_execute($newMainStatement);
                                        mysqli_stmt_close($newMainStatement);
                                    }
                                }
                            }

                            mysqli_stmt_close($deleteStatement);
                            redirect('vendor/track_images.php?track_id=' . $selectedTrackId . '&deleted=1');
                        }

                        mysqli_stmt_close($deleteStatement);
                        $errors[] = 'Unable to delete the track image. Please try again later.';
                    } else {
                        $errors[] = 'Unable to delete the track image. Please try again later.';
                    }
                }
            }
        }
    }
}

// Load uploaded images for the selected vendor-owned track.
if ($selectedTrackFound) {
    $imagesSql = "SELECT image_id, image_path, is_main
        FROM track_images
        WHERE track_id = ?
        ORDER BY is_main DESC, image_id ASC";

    $imagesStatement = mysqli_prepare($connection, $imagesSql);

    if ($imagesStatement) {
        mysqli_stmt_bind_param($imagesStatement, "i", $selectedTrackId);

        if (mysqli_stmt_execute($imagesStatement)) {
            $imageId = null;
            $imagePath = '';
            $isMain = 0;

            mysqli_stmt_bind_result($imagesStatement, $imageId, $imagePath, $isMain);

            while (mysqli_stmt_fetch($imagesStatement)) {
                $images[] = [
                    'image_id' => $imageId,
                    'image_path' => $imagePath,
                    'is_main' => $isMain,
                ];
            }
        } else {
            $errors[] = 'Unable to load track images. Please try again later.';
        }

        mysqli_stmt_close($imagesStatement);
    } else {
        $errors[] = 'Unable to load track images. Please try again later.';
    }
}

// Set the page title before loading the common header.
$pageTitle = 'Track Images';

// Load reusable page layout sections.
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>
    <div class="container">
        <section class="track-images">
            <!-- Track image management heading -->
            <div class="track-images-header">
                <h1>Track Images</h1>
                <p>Upload and manage images for your race track listings.</p>
            </div>

            <?php if (!empty($errors)) { ?>
                <!-- Validation and database error messages -->
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error) { ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if ($successMessage !== '') { ?>
                <!-- Image action success message -->
                <div class="success-message">
                    <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php } ?>

            <!-- Vendor track selector -->
            <div class="track-selector">
                <h2>Select Race Track</h2>

                <?php if (empty($tracks)) { ?>
                    <p>No race tracks have been added yet.</p>
                <?php } ?>

                <?php if (!empty($tracks)) { ?>
                    <ul>
                        <?php foreach ($tracks as $track) { ?>
                            <li>
                                <a href="<?= BASE_URL . 'vendor/track_images.php?track_id=' . htmlspecialchars((string) $track['track_id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($track['track_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                -
                                <?= htmlspecialchars($track['location'], ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>

            <?php if ($selectedTrackFound) { ?>
                <!-- Selected track image tools -->
                <div class="selected-track-images">
                    <h2>Manage Images for <?= htmlspecialchars($selectedTrackName, ENT_QUOTES, 'UTF-8') ?></h2>

                    <!-- Image upload form -->
                    <form method="POST" action="track_images.php?track_id=<?= htmlspecialchars((string) $selectedTrackId, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="track-image-form">
                        <input type="hidden" name="action" value="upload">
                        <input
                            type="hidden"
                            name="track_id"
                            value="<?= htmlspecialchars((string) $selectedTrackId, ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <!-- Track Image -->
                        <div class="form-group">
                            <label for="track_image">Upload Image</label>
                            <input
                                type="file"
                                id="track_image"
                                name="track_image"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                required
                            >
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-primary">Upload Image</button>
                    </form>

                    <?php if (empty($images)) { ?>
                        <!-- Empty image message -->
                        <p>No images have been uploaded for this track yet.</p>
                    <?php } ?>

                    <?php if (!empty($images)) { ?>
                        <!-- Uploaded image list -->
                        <div class="track-image-list">
                            <?php foreach ($images as $image) { ?>
                                <article class="track-image-item">
                                    <img
                                        src="<?= BASE_URL . htmlspecialchars($image['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($selectedTrackName, ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                    <?php if ((int) $image['is_main'] === 1) { ?>
                                        <p><strong>Main Image</strong></p>
                                    <?php } else { ?>
                                        <!-- Set main image form -->
                                        <form method="POST" action="track_images.php?track_id=<?= htmlspecialchars((string) $selectedTrackId, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="set_main">
                                            <input
                                                type="hidden"
                                                name="track_id"
                                                value="<?= htmlspecialchars((string) $selectedTrackId, ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                            <input
                                                type="hidden"
                                                name="image_id"
                                                value="<?= htmlspecialchars((string) $image['image_id'], ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                            <button type="submit" class="btn-primary">Set as Main</button>
                                        </form>
                                    <?php } ?>

                                    <!-- Delete image form -->
                                    <form method="POST" action="track_images.php?track_id=<?= htmlspecialchars((string) $selectedTrackId, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input
                                            type="hidden"
                                            name="track_id"
                                            value="<?= htmlspecialchars((string) $selectedTrackId, ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                        <input
                                            type="hidden"
                                            name="image_id"
                                            value="<?= htmlspecialchars((string) $image['image_id'], ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                        <button type="submit" class="btn-primary">Delete</button>
                                    </form>
                                </article>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>
    </div>
</main>

<?php
// Load the common footer and close the HTML document.
require_once '../includes/footer.php';
