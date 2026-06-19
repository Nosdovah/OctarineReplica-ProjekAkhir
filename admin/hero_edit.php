<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$error = '';
$success = '';

// Fetch existing hero settings
try {
    $stmt = $pdo_modifier->query("SELECT * FROM hero_settings WHERE id = 1");
    $hero = $stmt->fetch();

    if (!$hero) {
        $pdo_modifier->exec("INSERT INTO hero_settings (id, title) VALUES (1, 'Title')");
        $hero = ['title' => 'Title', 'subtitle' => '', 'button1_text' => '', 'button1_url' => '', 'button2_text' => '', 'button2_url' => '', 'image_path' => ''];
    }
} catch (\PDOException $e) {
    // If table doesn't exist, provide empty array and show error
    $error = "Hero settings table not found. Please run the database migration (migrate_hero.php).";
    $hero = ['title' => '', 'subtitle' => '', 'button1_text' => '', 'button1_url' => '', 'button2_text' => '', 'button2_url' => '', 'image_path' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $button1_text = trim($_POST['button1_text']);
    $button1_url = trim($_POST['button1_url']);
    $button2_text = trim($_POST['button2_text']);
    $button2_url = trim($_POST['button2_url']);
    
    $image_path = $hero['image_path'];
    
    // Handle image upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['hero_image']['tmp_name']);

        if (!in_array($mime_type, $allowed_types)) {
            $error = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
        } else {
            $ext = pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
            $filename = 'hero_' . time() . '.' . $ext;
            $destination = '../uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $destination)) {
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
            $stmt = $pdo_modifier->prepare("UPDATE hero_settings SET 
                title = ?, subtitle = ?, button1_text = ?, button1_url = ?, button2_text = ?, button2_url = ?, image_path = ?
                WHERE id = 1");
            $stmt->execute([$title, $subtitle, $button1_text, $button1_url, $button2_text, $button2_url, $image_path]);
            $success = "Hero settings updated successfully.";
            
            // Refresh data
            $stmt = $pdo_modifier->query("SELECT * FROM hero_settings WHERE id = 1");
            $hero = $stmt->fetch();
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
    <title>Edit Hero Section - Octarine Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=<?= filemtime('../style.css') ?>">
</head>
<body>
    <header class="header">
        <div class="container navbar" style="justify-content: flex-start; gap: 40px;">
            <a href="dashboard.php" class="logo">OCTARINE ADMIN</a>
            <nav class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="add.php">Add Product</a>
                <a href="hero_edit.php" style="color: var(--main); font-weight: 700;">Hero Settings</a>
                <a href="mid_banner_edit.php">Mid Banner</a>
            </nav>
            <div class="nav-utils" style="margin-left: auto;">
                <a href="../index.php" target="_blank" style="font-weight: 600; font-size: 14px;">View Storefront <i class="fas fa-external-link-alt" style="font-size: 12px; margin-left: 4px;"></i></a>
                <a href="logout.php" style="color: #d93025; font-weight: 600; font-size: 14px; margin-left: 20px;">Logout</a>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <div class="container" style="max-width: 800px;">
            <div class="header-top">
                <h1>Hero Settings</h1>
                <a href="dashboard.php" class="btn-outline-admin" style="padding: 10px 20px; font-size: 13px;">Back to Dashboard</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="form-card">
                <form action="hero_edit.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label>Hero Title *</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($hero['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Subtitle (HTML allowed for line breaks like &lt;br&gt;)</label>
                        <textarea name="subtitle" rows="3"><?= htmlspecialchars($hero['subtitle'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Button 1 Text</label>
                            <input type="text" name="button1_text" value="<?= htmlspecialchars($hero['button1_text'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Button 1 URL</label>
                            <input type="text" name="button1_url" value="<?= htmlspecialchars($hero['button1_url'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Button 2 Text</label>
                            <input type="text" name="button2_text" value="<?= htmlspecialchars($hero['button2_text'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Button 2 URL</label>
                            <input type="text" name="button2_url" value="<?= htmlspecialchars($hero['button2_url'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label>Hero Background Image (Leave empty to keep current)</label>
                        
                        <?php if (!empty($hero['image_path'])): ?>
                            <div style="margin-bottom: 10px;">
                                <p style="font-size: 13px; color: var(--secondary); margin-bottom: 5px;">Current Image:</p>
                                <img src="../uploads/<?= htmlspecialchars($hero['image_path']) ?>" alt="Current Hero" style="max-width: 100%; height: auto; border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        <?php else: ?>
                            <div style="margin-bottom: 10px;">
                                <p style="font-size: 13px; color: var(--secondary); margin-bottom: 5px;">Current Image: <strong>Default (Assets)</strong></p>
                            </div>
                        <?php endif; ?>
                        
                        <label for="hero_image" class="upload-zone" style="display: block; margin-top: 10px;">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload new hero image</p>
                            <span>JPG, PNG or WebP (Recommended: 1920x1080)</span>
                            <input type="file" name="hero_image" id="hero_image" accept="image/jpeg, image/png, image/webp" style="display: none;" onchange="updateFileName(this)">
                        </label>
                        <div id="file-name" style="text-align: center; font-size: 13px; color: var(--primary); margin-top: 8px;"></div>
                    </div>

                    <div class="form-actions" style="margin-top: 30px;">
                        <button type="submit" class="btn-admin">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

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
