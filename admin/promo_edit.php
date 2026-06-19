<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$error = '';
$success = '';

// Fetch current settings
$stmt = $pdo_modifier->query("SELECT * FROM promo_settings WHERE id = 1");
$promo = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hero_heading = $_POST['hero_heading'] ?? '';
    $hero_subheading = $_POST['hero_subheading'] ?? '';
    $carousel_heading = $_POST['carousel_heading'] ?? '';
    
    // Handle File Upload
    $hero_image = $promo['hero_image'];
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['hero_image']['tmp_name'];
        $file_size = $_FILES['hero_image']['size'];
        
        if ($file_size <= 5 * 1024 * 1024) { // Max 5MB
            $file_ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
                $upload_dir = __DIR__ . '/../uploads/';
                
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                    if ($hero_image && file_exists($upload_dir . $hero_image)) {
                        unlink($upload_dir . $hero_image);
                    }
                    $hero_image = $new_filename;
                }
            }
        }
    }

    try {
        $stmt = $pdo_modifier->prepare("
            UPDATE promo_settings SET 
            hero_heading = ?, hero_subheading = ?, hero_image = ?, carousel_heading = ?
            WHERE id = 1
        ");
        $stmt->execute([$hero_heading, $hero_subheading, $hero_image, $carousel_heading]);
        $success = "Promo Page settings updated successfully.";
        
        // Refresh data
        $stmt = $pdo_modifier->query("SELECT * FROM promo_settings WHERE id = 1");
        $promo = $stmt->fetch();
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
    <title>Edit Promo Page - Octarine Admin</title>
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
                <a href="about_edit.php"><i class="fas fa-address-card" style="width: 20px; text-align: center;"></i> About Page</a>
                <a href="promo_edit.php" class="active"><i class="fas fa-percent" style="width: 20px; text-align: center;"></i> Promo Page</a>
                <a href="reviews.php"><i class="fas fa-star" style="width: 20px; text-align: center;"></i> Customer Reviews</a>
                <div style="border-top: 1px solid #eaeaea; margin: 15px 0;"></div>
                <a href="../promo.php" target="_blank"><i class="fas fa-external-link-alt" style="width: 20px; text-align: center;"></i> Storefront</a>
                <a href="logout.php" style="color: #d93025;"><i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
                <div class="container" style="max-width: 900px;">
                    <div class="header-top">
                        <h1>Edit Promo Page Content</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <div class="card">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <!-- Hero Section -->
                            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">1. Hero Banner Section</h3>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label>Hero Heading</label>
                                    <input type="text" name="hero_heading" value="<?= htmlspecialchars($promo['hero_heading'] ?? '') ?>" required>
                                </div>
                                <div class="form-group full-width">
                                    <label>Hero Subheading</label>
                                    <textarea name="hero_subheading" rows="3" required><?= htmlspecialchars($promo['hero_subheading'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label>Hero Image (Landscape)</label>
                                    <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp">
                                    <?php if($promo['hero_image']): ?>
                                        <p style="font-size: 13px; margin-top: 5px; color: var(--secondary);">Current: <?= htmlspecialchars($promo['hero_image']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Carousel Section -->
                            <h3 style="margin: 40px 0 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">2. Carousel Section</h3>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label>Carousel Heading</label>
                                    <input type="text" name="carousel_heading" value="<?= htmlspecialchars($promo['carousel_heading'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-actions" style="margin-top: 40px;">
                                <button type="submit" class="btn-admin"><i class="fas fa-save"></i> Save Promo Page Content</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
