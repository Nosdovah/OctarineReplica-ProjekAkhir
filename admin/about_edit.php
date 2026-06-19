<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$error = '';
$success = '';

// Handle File Uploads Helper
function handleUpload($fileInputName, $currentImage = null) {
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

// Handle Delete Collaboration
if (isset($_GET['delete_collab'])) {
    try {
        $id = (int)$_GET['delete_collab'];
        $stmt = $pdo_modifier->prepare("SELECT image_path FROM collaborations WHERE id = ?");
        $stmt->execute([$id]);
        $collab = $stmt->fetch();
        
        if ($collab && $collab['image_path']) {
            $path = __DIR__ . '/../uploads/' . $collab['image_path'];
            if (file_exists($path)) unlink($path);
        }

        $stmt = $pdo_modifier->prepare("DELETE FROM collaborations WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Collaboration deleted successfully.";
    } catch (\PDOException $e) {
        $error = "Error deleting collaboration: " . $e->getMessage();
    }
}

// Handle Forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_settings') {
        $history_heading = $_POST['history_heading'] ?? '';
        $history_text = $_POST['history_text'] ?? '';
        
        $stmt = $pdo_modifier->query("SELECT * FROM about_settings WHERE id = 1");
        $about = $stmt->fetch();

        $banner_image = handleUpload('banner_image', $about['banner_image']);
        $history_image = handleUpload('history_image', $about['history_image']);

        try {
            $stmt = $pdo_modifier->prepare("
                UPDATE about_settings SET 
                banner_image = ?, history_image = ?, history_heading = ?, history_text = ?
                WHERE id = 1
            ");
            $stmt->execute([$banner_image, $history_image, $history_heading, $history_text]);
            $success = "About Page settings updated successfully.";
        } catch (\PDOException $e) {
            $error = "Error updating settings: " . $e->getMessage();
        }
    } 
    elseif ($action === 'add_collab') {
        $title = trim($_POST['collab_title']);
        $text = trim($_POST['collab_text']);
        $image = handleUpload('collab_image');
        
        if ($title && $text) {
            try {
                $stmt = $pdo_modifier->prepare("INSERT INTO collaborations (title, description, image_path) VALUES (?, ?, ?)");
                $stmt->execute([$title, $text, $image]);
                $success = "Collaboration added successfully.";
            } catch (\PDOException $e) {
                $error = "Error adding collaboration: " . $e->getMessage();
            }
        } else {
            $error = "Title and description are required for a collaboration.";
        }
    }
}

// Fetch current settings
$stmt = $pdo_modifier->query("SELECT * FROM about_settings WHERE id = 1");
$about = $stmt->fetch();

// Fetch collaborations
try {
    $stmt = $pdo_modifier->query("SELECT * FROM collaborations ORDER BY id DESC");
    $collaborations = $stmt->fetchAll();
} catch (\PDOException $e) {
    $collaborations = [];
    $error = "Collaborations table not found. Please run migration (migrate_collaborations.php).";
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

                    <div class="card" style="margin-bottom: 40px;">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_settings">
                            
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

                            <div class="form-actions" style="margin-top: 40px;">
                                <button type="submit" class="btn-admin"><i class="fas fa-save"></i> Save Page Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- Dynamic Collaborations Section -->
                    <h2 style="margin: 60px 0 20px;">Dynamic Collaborations</h2>
                    
                    <div class="card" style="margin-bottom: 40px; background: #fafafa;">
                        <h3 style="margin-bottom: 20px; font-weight: 600;">Add New Collaboration</h3>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_collab">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label>Title *</label>
                                    <input type="text" name="collab_title" required placeholder="e.g. Octarine X Creator">
                                </div>
                                <div class="form-group full-width">
                                    <label>Description Text *</label>
                                    <textarea name="collab_text" rows="3" required placeholder="Describe the collaboration..."></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label>Image</label>
                                    <input type="file" name="collab_image" accept=".jpg,.jpeg,.png,.webp">
                                </div>
                            </div>
                            <div class="form-actions" style="margin-top: 20px; padding-top: 0; border: none;">
                                <button type="submit" class="btn-admin"><i class="fas fa-plus"></i> Add Collaboration</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-card">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="padding: 16px; text-align: left; background: #fafafa; border-bottom: 1px solid #eaeaea; width: 80px;">Image</th>
                                    <th style="padding: 16px; text-align: left; background: #fafafa; border-bottom: 1px solid #eaeaea;">Title & Description</th>
                                    <th style="padding: 16px; text-align: center; background: #fafafa; border-bottom: 1px solid #eaeaea;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($collaborations)): ?>
                                <tr>
                                    <td colspan="3" style="padding: 30px; text-align: center; color: var(--secondary);">No collaborations found. Add one above!</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($collaborations as $c): ?>
                                    <tr>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea;">
                                            <?php if ($c['image_path']): ?>
                                                <img src="../uploads/<?= htmlspecialchars($c['image_path']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #aaa;"><i class="far fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea;">
                                            <div style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($c['title']) ?></div>
                                            <div style="font-size: 13px; color: var(--secondary); max-width: 400px;"><?= htmlspecialchars(substr($c['description'], 0, 100)) ?>...</div>
                                        </td>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea; text-align: center;">
                                            <div class="action-btns" style="justify-content: center;">
                                                <a href="about_edit.php?delete_collab=<?= $c['id'] ?>" class="btn-icon btn-delete" onclick="return confirm('Delete this collaboration?')" title="Delete"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </main>
        </div>
    </div>
</body>
</html>
