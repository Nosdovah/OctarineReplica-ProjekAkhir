<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$error = '';
$success = '';

// Fetch current settings
$stmt = $pdo_modifier->query("SELECT * FROM about_settings WHERE id = 1");
$about = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $history_heading = $_POST['history_heading'] ?? '';
    $history_text = $_POST['history_text'] ?? '';
    
    $c1_title = $_POST['collab1_title'] ?? '';
    $c1_text = $_POST['collab1_text'] ?? '';
    $c2_title = $_POST['collab2_title'] ?? '';
    $c2_text = $_POST['collab2_text'] ?? '';
    $c3_title = $_POST['collab3_title'] ?? '';
    $c3_text = $_POST['collab3_text'] ?? '';

    // Handle File Uploads Helper
    function handleUpload($fileInputName, $currentImage) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES[$fileInputName]['tmp_name'];
            $file_size = $_FILES[$fileInputName]['size'];
            
            if ($file_size > 5 * 1024 * 1024) return $currentImage; // Exceeds 5MB
            
            $file_ext = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($file_ext, $allowed_exts)) return $currentImage;

            $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../uploads/';
            
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                if ($currentImage && file_exists($upload_dir . $currentImage)) {
                    unlink($upload_dir . $currentImage);
                }
                return $new_filename;
            }
        }
        return $currentImage;
    }

    $banner_image = handleUpload('banner_image', $about['banner_image']);
    $history_image = handleUpload('history_image', $about['history_image']);
    $collab1_image = handleUpload('collab1_image', $about['collab1_image']);
    $collab2_image = handleUpload('collab2_image', $about['collab2_image']);
    $collab3_image = handleUpload('collab3_image', $about['collab3_image']);

    try {
        $stmt = $pdo_modifier->prepare("
            UPDATE about_settings SET 
            banner_image = ?, history_image = ?, history_heading = ?, history_text = ?,
            collab1_title = ?, collab1_text = ?, collab1_image = ?,
            collab2_title = ?, collab2_text = ?, collab2_image = ?,
            collab3_title = ?, collab3_text = ?, collab3_image = ?
            WHERE id = 1
        ");
        $stmt->execute([
            $banner_image, $history_image, $history_heading, $history_text,
            $c1_title, $c1_text, $collab1_image,
            $c2_title, $c2_text, $collab2_image,
            $c3_title, $c3_text, $collab3_image
        ]);
        $success = "About Page settings updated successfully.";
        
        // Refresh data
        $stmt = $pdo_modifier->query("SELECT * FROM about_settings WHERE id = 1");
        $about = $stmt->fetch();
    } catch (\PDOException $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Page - Octarine Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=<?= filemtime('../style.css') ?>">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="dashboard.php" class="sidebar-logo">OCTARINE.</a>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt" style="width: 20px; text-align: center;"></i> Dashboard</a>
                <a href="add.php"><i class="fas fa-box" style="width: 20px; text-align: center;"></i> Add Product</a>
                <a href="categories.php"><i class="fas fa-tags" style="width: 20px; text-align: center;"></i> Categories</a>
                <a href="hero_edit.php"><i class="fas fa-image" style="width: 20px; text-align: center;"></i> Hero Settings</a>
                <a href="mid_banner_edit.php"><i class="fas fa-flag" style="width: 20px; text-align: center;"></i> Mid Banner</a>
                <a href="about_edit.php" class="active"><i class="fas fa-address-card" style="width: 20px; text-align: center;"></i> About Page</a>
                <a href="reviews.php"><i class="fas fa-star" style="width: 20px; text-align: center;"></i> Customer Reviews</a>
                <div style="border-top: 1px solid #eaeaea; margin: 15px 0;"></div>
                <a href="../about.php" target="_blank"><i class="fas fa-external-link-alt" style="width: 20px; text-align: center;"></i> Storefront</a>
                <a href="logout.php" style="color: #d93025;"><i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
                <div class="container" style="max-width: 900px;">
                    <div class="header-top">
                        <h1>Edit About Page Content</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <!-- Hero Banner Section -->
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">1. Top Banner</h3>
                            <div class="form-group full-width">
                                <label>Banner Image (Wide Collage)</label>
                                <input type="file" name="banner_image" accept=".jpg,.jpeg,.png,.webp">
                                <?php if($about['banner_image']): ?>
                                    <p style="font-size: 13px; margin-top: 5px; color: var(--secondary);">Current: <?= htmlspecialchars($about['banner_image']) ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- History Section -->
                            <h3 style="margin: 40px 0 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">2. Octarine History Section</h3>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label>History Heading</label>
                                    <input type="text" name="history_heading" value="<?= htmlspecialchars($about['history_heading'] ?? '') ?>" required>
                                </div>
                                <div class="form-group full-width">
                                    <label>History Text</label>
                                    <textarea name="history_text" rows="5" required><?= htmlspecialchars($about['history_text'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label>History Image (Left Side Portrait)</label>
                                    <input type="file" name="history_image" accept=".jpg,.jpeg,.png,.webp">
                                    <?php if($about['history_image']): ?>
                                        <p style="font-size: 13px; margin-top: 5px; color: var(--secondary);">Current: <?= htmlspecialchars($about['history_image']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Collaborations Section -->
                            <h3 style="margin: 40px 0 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">3. Special Collaborations</h3>
                            
                            <!-- Collab 1 -->
                            <h4 style="margin-bottom: 15px; color: var(--secondary);">Collaboration 1</h4>
                            <div class="form-grid" style="margin-bottom: 30px; background: #fafafa; padding: 20px; border-radius: 8px;">
                                <div class="form-group full-width">
                                    <label>Title</label>
                                    <input type="text" name="collab1_title" value="<?= htmlspecialchars($about['collab1_title'] ?? '') ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label>Description Text</label>
                                    <textarea name="collab1_text" rows="3"><?= htmlspecialchars($about['collab1_text'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label>Image</label>
                                    <input type="file" name="collab1_image" accept=".jpg,.jpeg,.png,.webp">
                                    <?php if($about['collab1_image']): ?>
                                        <p style="font-size: 13px; margin-top: 5px;">Current: <?= htmlspecialchars($about['collab1_image']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Collab 2 -->
                            <h4 style="margin-bottom: 15px; color: var(--secondary);">Collaboration 2</h4>
                            <div class="form-grid" style="margin-bottom: 30px; background: #fafafa; padding: 20px; border-radius: 8px;">
                                <div class="form-group full-width">
                                    <label>Title</label>
                                    <input type="text" name="collab2_title" value="<?= htmlspecialchars($about['collab2_title'] ?? '') ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label>Description Text</label>
                                    <textarea name="collab2_text" rows="3"><?= htmlspecialchars($about['collab2_text'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label>Image</label>
                                    <input type="file" name="collab2_image" accept=".jpg,.jpeg,.png,.webp">
                                    <?php if($about['collab2_image']): ?>
                                        <p style="font-size: 13px; margin-top: 5px;">Current: <?= htmlspecialchars($about['collab2_image']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Collab 3 -->
                            <h4 style="margin-bottom: 15px; color: var(--secondary);">Collaboration 3</h4>
                            <div class="form-grid" style="margin-bottom: 30px; background: #fafafa; padding: 20px; border-radius: 8px;">
                                <div class="form-group full-width">
                                    <label>Title</label>
                                    <input type="text" name="collab3_title" value="<?= htmlspecialchars($about['collab3_title'] ?? '') ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label>Description Text</label>
                                    <textarea name="collab3_text" rows="3"><?= htmlspecialchars($about['collab3_text'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label>Image</label>
                                    <input type="file" name="collab3_image" accept=".jpg,.jpeg,.png,.webp">
                                    <?php if($about['collab3_image']): ?>
                                        <p style="font-size: 13px; margin-top: 5px;">Current: <?= htmlspecialchars($about['collab3_image']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-actions" style="margin-top: 40px;">
                                <button type="submit" class="btn-admin"><i class="fas fa-save"></i> Save About Page Content</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
