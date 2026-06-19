<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wms_id = $_POST['wms_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $product_type = $_POST['product_type'] ?? 'SINGLE';
    $base_price = $_POST['base_price'] ?? 0;
    $weight = $_POST['weight'] ?? 0;
    $variants_count = $_POST['variants_count'] ?? 0;
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    $description = $_POST['description'] ?? '';
    $image_path = null;

    $errors = [];

    // Basic Validation
    if (empty($wms_id) || empty($name) || empty($sku) || empty($base_price)) {
        $errors[] = "WMS ID, Name, SKU, and Base Price are required.";
    }

    // Secure File Upload Handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];

        // 1. Check file size (e.g., max 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            $errors[] = "File size exceeds limit (5MB).";
        }

        // 2. Inspect MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($tmp_name);

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime_type, $allowed_mimes)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
        }

        // 3. Validate file extension whitelist
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Invalid file extension.";
        }

        // If no errors, proceed to save the file securely
        if (empty($errors)) {
            // Cryptographic random string for filename
            $new_filename = bin2hex(random_bytes(16)) . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $destination)) {
                $image_path = $new_filename;
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    }

    // Insert into DB if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo_modifier->prepare("
                INSERT INTO wms_products 
                (wms_id, name, sku, brand, product_type, base_price, weight, variants_count, is_visible, description, image_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $wms_id, $name, $sku, $brand, $product_type, $base_price, $weight, $variants_count, $is_visible, $description, $image_path
            ]);
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Octarine Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=<?= filemtime('../style.css') ?>">
    
</head>
<body>
    <header class="header">
        <div class="container navbar">
            <a href="dashboard.php" class="logo">OCTARINE ADMIN</a>
            <nav class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="add.php" style="color: var(--main); font-weight: 700;">Add Product</a>
                <a href="hero_edit.php">Hero Settings</a>
                <a href="mid_banner_edit.php">Mid Banner</a>
            </nav>
            <div class="nav-utils">
                <a href="../index.php" target="_blank" style="font-weight: 600; font-size: 14px;">View Storefront <i class="fas fa-external-link-alt" style="font-size: 12px; margin-left: 4px;"></i></a>
                <a href="logout.php" style="color: #d93025; font-weight: 600; font-size: 14px; margin-left: 20px;">Logout</a>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <div class="container" style="max-width: 900px;">
            <div class="header-top">
                <h1>Add New Product</h1>
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
                            <input type="text" name="name" required placeholder="Product Title" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" placeholder="e.g. Octarine" value="<?= htmlspecialchars($_POST['brand'] ?? 'Octarine') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>SKU *</label>
                            <input type="text" name="sku" required placeholder="Stock Keeping Unit" value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>WMS ID *</label>
                            <input type="number" name="wms_id" required placeholder="Warehouse System ID" value="<?= htmlspecialchars($_POST['wms_id'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Base Price (IDR) *</label>
                            <input type="number" step="0.01" name="base_price" required placeholder="0" value="<?= htmlspecialchars($_POST['base_price'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Weight (g)</label>
                            <input type="number" step="0.01" name="weight" placeholder="0" value="<?= htmlspecialchars($_POST['weight'] ?? 0) ?>">
                        </div>

                        <div class="form-group">
                            <label>Product Type</label>
                            <input type="text" name="product_type" placeholder="SINGLE" value="<?= htmlspecialchars($_POST['product_type'] ?? 'SINGLE') ?>">
                        </div>
                        <div class="form-group">
                            <label>Variants Count</label>
                            <input type="number" name="variants_count" placeholder="0" value="<?= htmlspecialchars($_POST['variants_count'] ?? 0) ?>">
                        </div>

                        <div class="form-group full-width">
                            <label class="checkbox-group">
                                <input type="checkbox" name="is_visible" value="1" <?= (isset($_POST['is_visible']) || $_SERVER['REQUEST_METHOD'] !== 'POST') ? 'checked' : '' ?>>
                                <span>Make this product visible on the storefront immediately</span>
                            </label>
                        </div>

                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description" rows="5" placeholder="Detailed product description..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label>Product Image</label>
                            <div class="file-upload">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <p style="margin-bottom: 8px; font-weight: 500;">Drag and drop or click to upload</p>
                                <p style="font-size: 13px; color: var(--secondary);">Supports JPG, PNG, WEBP (Max 5MB)</p>
                                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn-outline-admin">Cancel</a>
                        <button type="submit" class="btn-admin"><i class="fas fa-save"></i> Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
