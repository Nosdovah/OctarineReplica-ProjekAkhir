<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$error = '';
$success = '';

// Fetch existing mid banner settings
try {
    $stmt = $pdo_modifier->query("SELECT * FROM mid_banner_settings WHERE id = 1");
    $banner = $stmt->fetch();

    if (!$banner) {
        $pdo_modifier->exec("INSERT INTO mid_banner_settings (id, title) VALUES (1, 'Title')");
        $banner = ['title' => 'Title', 'subtitle' => '', 'button_text' => '', 'button_url' => '', 'image_path' => ''];
    }
} catch (\PDOException $e) {
    $error = "Mid Banner settings table not found. Please run the database migration (migrate_mid_banner.php).";
    $banner = ['title' => '', 'subtitle' => '', 'button_text' => '', 'button_url' => '', 'image_path' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $button_text = trim($_POST['button_text']);
    $button_url = trim($_POST['button_url']);
    
    $image_path = $banner['image_path'];
    
    // Handle image upload
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['banner_image']['tmp_name']);

        if (!in_array($mime_type, $allowed_types)) {
            $error = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
        } else {
            $ext = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $filename = 'mid_banner_' . time() . '.' . $ext;
            $destination = '../uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $destination)) {
                // Delete old image if it's not the default one and exists
                if ($image_path && file_exists('../uploads/' . $image_path)) {
                    unlink('../uploads/' . $image_path);
                }
                $image_path = $filename;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    if (empty($error)) {
        try {
            $stmt = $pdo_modifier->prepare("UPDATE mid_banner_settings SET 
                title = ?, subtitle = ?, button_text = ?, button_url = ?, image_path = ?
                WHERE id = 1");
            $stmt->execute([$title, $subtitle, $button_text, $button_url, $image_path]);
            $success = "Mid banner settings updated successfully.";
            
            // Refresh data
            $stmt = $pdo_modifier->query("SELECT * FROM mid_banner_settings WHERE id = 1");
            $banner = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Error updating settings: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mid Banner - Octarine Admin</title>
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
                <a href="hero_edit.php"><i class="fas fa-image" style="width: 20px; text-align: center;"></i> Hero Settings</a>
                <a href="mid_banner_edit.php" class="active"><i class="fas fa-flag" style="width: 20px; text-align: center;"></i> Mid Banner</a>
                <div style="border-top: 1px solid #eaeaea; margin: 15px 0;"></div>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt" style="width: 20px; text-align: center;"></i> Storefront</a>
                <a href="logout.php" style="color: #d93025;"><i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
        <div class="container" style="max-width: 800px;">
            <div class="header-top">
                <h1>Mid Banner Settings</h1>
                <a href="dashboard.php" class="btn-outline-admin" style="padding: 10px 20px; font-size: 13px;">Back to Dashboard</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="form-card">
                <form action="mid_banner_edit.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label>Banner Title *</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($banner['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Subtitle / Description</label>
                        <textarea name="subtitle" rows="3"><?= htmlspecialchars($banner['subtitle'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Button Text</label>
                            <input type="text" name="button_text" value="<?= htmlspecialchars($banner['button_text'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Button URL</label>
                            <input type="text" name="button_url" value="<?= htmlspecialchars($banner['button_url'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label>Background Image (Leave empty to keep current)</label>
                        
                        <?php if (!empty($banner['image_path'])): ?>
                            <div style="margin-bottom: 10px;">
                                <p style="font-size: 13px; color: var(--secondary); margin-bottom: 5px;">Current Image:</p>
                                <img src="../uploads/<?= htmlspecialchars($banner['image_path']) ?>" alt="Current Banner" style="max-width: 100%; height: auto; border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        <?php else: ?>
                            <div style="margin-bottom: 10px;">
                                <p style="font-size: 13px; color: var(--secondary); margin-bottom: 5px;">Current Image: <strong>Default (Unsplash)</strong></p>
                            </div>
                        <?php endif; ?>
                        
                        <label for="banner_image" class="upload-zone" style="display: block; margin-top: 10px;">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload new banner image</p>
                            <span>JPG, PNG or WebP</span>
                            <input type="file" name="banner_image" id="banner_image" accept="image/jpeg, image/png, image/webp" style="display: none;" onchange="updateFileName(this)">
                        </label>
                        <div id="file-name" style="text-align: center; font-size: 13px; color: var(--primary); margin-top: 8px;"></div>
                    </div>

                    <div class="form-actions" style="margin-top: 30px;">
                        <button type="submit" class="btn-admin">Save Settings</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                fileNameDisplay.textContent = "Selected: " + input.files[0].name;
                fileNameDisplay.style.color = "var(--main)";
            } else {
                fileNameDisplay.textContent = "";
            }
        }
    </script>
</body>
</html>
