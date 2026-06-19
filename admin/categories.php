<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

$error = '';
$success = '';

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo_modifier->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Category deleted successfully.";
    } catch (\PDOException $e) {
        $error = "Error deleting category: " . $e->getMessage();
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $type = $_POST['type'];
    $name = trim($_POST['name']);
    
    if ($name && in_array($type, ['gender', 'scent'])) {
        try {
            $stmt = $pdo_modifier->prepare("INSERT INTO categories (type, name) VALUES (?, ?)");
            $stmt->execute([$type, $name]);
            $success = ucfirst($type) . " category added successfully.";
        } catch (\PDOException $e) {
            $error = "Error adding category: " . $e->getMessage();
        }
    } else {
        $error = "Please fill all fields correctly.";
    }
}

// Fetch categories
try {
    $stmt = $pdo_modifier->query("SELECT * FROM categories ORDER BY type ASC, id DESC");
    $categories = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error = "Categories table not found. Please run migration (migrate_shop.php).";
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Octarine Admin</title>
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
                <a href="categories.php" class="active"><i class="fas fa-tags" style="width: 20px; text-align: center;"></i> Categories</a>
                <a href="hero_edit.php"><i class="fas fa-image" style="width: 20px; text-align: center;"></i> Hero Settings</a>
                <a href="mid_banner_edit.php"><i class="fas fa-flag" style="width: 20px; text-align: center;"></i> Mid Banner</a>
                <a href="about_edit.php"><i class="fas fa-address-card" style="width: 20px; text-align: center;"></i> About Page</a>
                <a href="promo_edit.php"><i class="fas fa-percent" style="width: 20px; text-align: center;"></i> Promo Page</a>
                <a href="reviews.php"><i class="fas fa-star" style="width: 20px; text-align: center;"></i> Customer Reviews</a>
                <div style="border-top: 1px solid #eaeaea; margin: 15px 0;"></div>
                <a href="../shop.php" target="_blank"><i class="fas fa-external-link-alt" style="width: 20px; text-align: center;"></i> Storefront</a>
                <a href="logout.php" style="color: #d93025;"><i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
                <div class="container" style="max-width: 900px;">
                    <div class="header-top">
                        <h1>Manage Categories (Filters)</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <!-- Add Category Form -->
                    <div class="form-card" style="margin-bottom: 40px; padding: 30px;">
                        <h3 style="margin-bottom: 20px; font-weight: 600;">Add New Category Filter</h3>
                        <form action="categories.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Filter Group *</label>
                                    <select name="type" required>
                                        <option value="gender">Gender Preference</option>
                                        <option value="scent">Product Type (Scent)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Category Name *</label>
                                    <input type="text" name="name" required placeholder="e.g. MEN or Citrus">
                                </div>
                            </div>

                            <div class="form-actions" style="margin-top: 20px; padding-top: 0; border: none;">
                                <button type="submit" class="btn-admin">Add Category</button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories List -->
                    <div class="table-card">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="padding: 16px; text-align: left; background: #fafafa; border-bottom: 1px solid #eaeaea;">Filter Group</th>
                                    <th style="padding: 16px; text-align: left; background: #fafafa; border-bottom: 1px solid #eaeaea;">Category Name</th>
                                    <th style="padding: 16px; text-align: center; background: #fafafa; border-bottom: 1px solid #eaeaea;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" style="padding: 30px; text-align: center; color: var(--secondary);">No categories found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea; font-weight: 500; text-transform: capitalize;">
                                            <?php if($cat['type'] === 'gender') echo 'Gender Preference'; else echo 'Product Type (Scent)'; ?>
                                        </td>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea;"><?= htmlspecialchars($cat['name']) ?></td>
                                        <td style="padding: 16px; border-bottom: 1px solid #eaeaea; text-align: center;">
                                            <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn-icon btn-delete" onclick="return confirm('Are you sure you want to delete this category?')" style="display: inline-flex; text-decoration: none;"><i class="fas fa-trash"></i></a>
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
