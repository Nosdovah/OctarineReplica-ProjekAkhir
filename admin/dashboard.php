<?php
require_once 'auth.php';
require_once '../config/db_modifier.php';

// Fetch all products
$stmt = $pdo_modifier->query("SELECT * FROM wms_products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Octarine</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css?v=<?= filemtime('../style.css') ?>">
    
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="dashboard.php" class="sidebar-logo">OCTARINE.</a>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt" style="width: 20px; text-align: center;"></i> Dashboard</a>
                <a href="add.php"><i class="fas fa-box" style="width: 20px; text-align: center;"></i> Add Product</a>
                <a href="categories.php"><i class="fas fa-tags" style="width: 20px; text-align: center;"></i> Categories</a>
                <a href="hero_edit.php"><i class="fas fa-image" style="width: 20px; text-align: center;"></i> Hero Settings</a>
                <a href="mid_banner_edit.php"><i class="fas fa-flag" style="width: 20px; text-align: center;"></i> Mid Banner</a>
                <a href="reviews.php"><i class="fas fa-star" style="width: 20px; text-align: center;"></i> Customer Reviews</a>
                <div style="border-top: 1px solid #eaeaea; margin: 15px 0;"></div>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt" style="width: 20px; text-align: center;"></i> Storefront</a>
                <a href="logout.php" style="color: #d93025;"><i class="fas fa-sign-out-alt" style="width: 20px; text-align: center;"></i> Logout</a>
            </nav>
        </aside>

        <div class="admin-content">
            <main class="admin-main">
        <div class="container">
            <div class="header-top">
                <h1>Catalog Management</h1>
                <a href="add.php" class="btn-admin"><i class="fas fa-plus"></i> New Product</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-box"></i></div>
                    <div class="stat-info">
                        <h3><?= count($products) ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-eye"></i></div>
                    <div class="stat-info">
                        <h3><?= count(array_filter($products, fn($p) => $p['is_visible'])) ?></h3>
                        <p>Visible Products</p>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Info</th>
                                <th>SKU / WMS ID</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if ($product['image_path']): ?>
                                        <img src="../uploads/<?= htmlspecialchars($product['image_path']) ?>" class="thumbnail" alt="Product Image">
                                    <?php else: ?>
                                        <div class="thumbnail" style="background: var(--light-bg); display:flex; align-items:center; justify-content:center; color: var(--primary); font-size: 10px; text-transform: uppercase;">No Img</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($product['name']) ?></div>
                                    <div style="color: var(--secondary); font-size: 13px;"><?= htmlspecialchars($product['brand']) ?> &bull; <?= htmlspecialchars($product['product_type']) ?></div>
                                </td>
                                <td>
                                    <div style="font-family: monospace; font-size: 14px;"><?= htmlspecialchars($product['sku']) ?></div>
                                    <div style="color: var(--secondary); font-size: 12px; margin-top: 4px;">ID: <?= htmlspecialchars($product['wms_id']) ?></div>
                                </td>
                                <td style="font-weight: 600;">Rp <?= number_format($product['base_price'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($product['is_visible']): ?>
                                        <span class="badge badge-success">Visible</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit.php?id=<?= $product['id'] ?>" class="btn-icon btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="delete.php?id=<?= $product['id'] ?>" class="btn-icon btn-delete" title="Delete" onclick="return confirm('Delete this product permanently?');"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <p>No products found in the catalog.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    </div>
</body>
</html>