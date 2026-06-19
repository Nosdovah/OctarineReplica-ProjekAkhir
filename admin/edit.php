<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch existing product
$stmt = $pdo_modifier->prepare("SELECT * FROM wms_products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wms_id = $_POST['wms_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $product_type = $_POST['product_type'] ?? 'SINGLE';
    $base_price = $_POST['base_price'] ?? 0;
    $weight = $_POST['weight'] ?? 0;
    $variants_count = $_POST['variants_count'] ?? 0;
    $gender_id = empty($_POST['gender_id']) ? null : (int)$_POST['gender_id'];
    $category_id = empty($_POST['category_id']) ? null : (int)$_POST['category_id'];
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    $is_promo = isset($_POST['is_promo']) ? 1 : 0;
    $description = $_POST['description'] ?? '';
    $image_path = $product['image_path']; // Keep old image by default

    if (empty($wms_id) || empty($name) || empty($sku) || empty($base_price)) {
        $errors[] = "WMS ID, Name, SKU, and Base Price are required.";
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];

        if ($file_size > 5 * 1024 * 1024) {
            $errors[] = "File size exceeds limit (5MB).";
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($tmp_name);

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime_type, $allowed_mimes)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
        }

        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Invalid file extension.";
        }

        if (empty($errors)) {
            $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $destination)) {
                // Optionally delete the old image here
                if ($image_path && file_exists($upload_dir . $image_path)) {
                    unlink($upload_dir . $image_path);
                }
                $image_path = $new_filename;
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo_modifier->prepare("
                UPDATE wms_products SET 
                wms_id = ?, name = ?, sku = ?, brand = ?, product_type = ?, 
                base_price = ?, weight = ?, variants_count = ?, is_visible = ?, is_promo = ?, 
                description = ?, image_path = ?, gender_id = ?, category_id = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $wms_id, $name, $sku, $brand, $product_type, $base_price, 
                $weight, $variants_count, $is_visible, $is_promo, $description, $image_path, $gender_id, $category_id, $id
            ]);
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch categories for dropdowns
try {
    $stmt = $pdo_modifier->query("SELECT * FROM categories ORDER BY name ASC");
    $all_cats = $stmt->fetchAll();
    $genders = array_filter($all_cats, fn($c) => $c['type'] === 'gender');
    $scents = array_filter($all_cats, fn($c) => $c['type'] === 'scent');
} catch (PDOException $e) {
    $genders = [];
    $scents = [];
}

// Fetch promo carousel heading
try {
    $stmt = $pdo_modifier->query("SELECT carousel_heading FROM promo_settings WHERE id = 1");
    $promo_heading = $stmt->fetchColumn() ?: 'Collaboration Promo';
} catch (PDOException $e) {
    $promo_heading = 'Collaboration Promo';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Octarine Admin</title>
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
                <a href="promo_edit.php"><i class="fas fa-percent" style="width: 20px; text-align: center;"></i> Promo Page</a>
                <a href="reviews.php"><i class="fas fa-star" style="width: 20px; text-align: center;"></i> Customer Reviews</a>
                <div style="border-top: 1px solid #eaeaea; margin: 15px 0;"></div>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt" style="width: 20px; text-align: center;"></i> Storefront</a>
                <a href="logout.php" style="color: #d93025;"><i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
        <div class="container" style="max-width: 900px;">
            <div class="header-top">
                <h1>Edit Product</h1>
                <a href="dashboard.php" class="btn-outline-admin"><i class="fas fa-arrow-left"></i> Back to Catalog</a>
            </div>

            <div class="card">
                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <?php foreach ($errors as $err) echo "<div><i class='fas fa-exclamation-circle'></i> " . htmlspecialchars($err) . "</div>"; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" value="<?= htmlspecialchars($_POST['brand'] ?? $product['brand']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>SKU *</label>
                            <input type="text" name="sku" required value="<?= htmlspecialchars($_POST['sku'] ?? $product['sku']) ?>">
                        </div>
                        <div class="form-group">
                            <label>WMS ID *</label>
                            <input type="number" name="wms_id" required value="<?= htmlspecialchars($_POST['wms_id'] ?? $product['wms_id']) ?>">
                        </div>

                        <div class="form-group">
                            <label>Base Price (IDR) *</label>
                            <input type="number" step="0.01" name="base_price" required value="<?= htmlspecialchars($_POST['base_price'] ?? $product['base_price']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Weight (g)</label>
                            <input type="number" step="0.01" name="weight" value="<?= htmlspecialchars($_POST['weight'] ?? $product['weight']) ?>">
                        </div>

                        <div class="form-group">
                            <label>Gender Preference</label>
                            <select name="gender_id">
                                <option value="">-- No Gender Preference --</option>
                                <?php foreach($genders as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ((isset($_POST['gender_id']) ? $_POST['gender_id'] : $product['gender_id']) == $g['id']) ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Product Type (Scent)</label>
                            <select name="category_id">
                                <option value="">-- No Product Type --</option>
                                <?php foreach($scents as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ((isset($_POST['category_id']) ? $_POST['category_id'] : $product['category_id']) == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Variants Count</label>
                            <input type="number" name="variants_count" value="<?= htmlspecialchars($_POST['variants_count'] ?? $product['variants_count']) ?>">
                        </div>

                        <div class="form-group full-width">
                            <label class="checkbox-group">
                                <input type="checkbox" name="is_visible" value="1" <?= ($_POST['is_visible'] ?? $product['is_visible']) ? 'checked' : '' ?>>
                                <span>Make this product visible on the storefront immediately</span>
                            </label>
                        </div>
                        
                        <div class="form-group full-width" style="margin-top: -15px;">
                            <label class="checkbox-group">
                                <input type="checkbox" name="is_promo" value="1" <?= ($_POST['is_promo'] ?? $product['is_promo']) ? 'checked' : '' ?>>
                                <span>Include in <strong><?= htmlspecialchars($promo_heading) ?></strong> (Promo Page)</span>
                            </label>
                        </div>

                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description" rows="5"><?= htmlspecialchars($_POST['description'] ?? $product['description']) ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label>Update Product Image (Leave empty to keep current)</label>
                            <div class="file-upload">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <p style="margin-bottom: 8px; font-weight: 500;">Drag and drop or click to upload</p>
                                <p style="font-size: 13px; color: var(--secondary);">Supports JPG, PNG, WEBP (Max 5MB)</p>
                                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                            <?php if ($product['image_path']): ?>
                                <div class="current-img-container">
                                    <img src="../uploads/<?= htmlspecialchars($product['image_path']) ?>" class="current-img" alt="Current Image">
                                    <div>
                                        <p style="font-size: 12px; color: var(--secondary); text-transform: uppercase; letter-spacing: 1px;">Current Image</p>
                                        <p style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($product['image_path']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn-outline-admin">Cancel</a>
                        <button type="submit" class="btn-admin"><i class="fas fa-save"></i> Update Product</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
