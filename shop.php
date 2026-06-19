<?php
session_start();
require_once 'config/db_viewer.php';

// Fetch Categories for Filters
try {
    $stmt = $pdo_viewer->query("SELECT * FROM categories ORDER BY type ASC, id ASC");
    $categories = $stmt->fetchAll();
    $genders = array_filter($categories, fn($c) => $c['type'] === 'gender');
    $scents = array_filter($categories, fn($c) => $c['type'] === 'scent');
} catch (PDOException $e) {
    $genders = [];
    $scents = [];
}

// Handle Filters
$search = $_GET['search'] ?? '';
$gender_filter = $_GET['gender'] ?? [];
$scent_filter = $_GET['scent'] ?? [];

$query = "
    SELECT p.*, g.name as gender_name, c.name as category_name
    FROM wms_products p
    LEFT JOIN categories g ON p.gender_id = g.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_visible = 1
";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($gender_filter)) {
    $placeholders = str_repeat('?,', count($gender_filter) - 1) . '?';
    $query .= " AND p.gender_id IN ($placeholders)";
    $params = array_merge($params, $gender_filter);
}

if (!empty($scent_filter)) {
    $placeholders = str_repeat('?,', count($scent_filter) - 1) . '?';
    $query .= " AND p.category_id IN ($placeholders)";
    $params = array_merge($params, $scent_filter);
}

$query .= " ORDER BY p.id DESC";

try {
    $stmt = $pdo_viewer->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Octarine</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= filemtime('style.css') ?>">
    <style>
        .shop-container {
            display: flex;
            gap: 40px;
            padding: 40px 0;
        }
        .shop-sidebar {
            width: 250px;
            flex-shrink: 0;
        }
        .shop-main {
            flex: 1;
        }
        .filter-section {
            margin-bottom: 30px;
        }
        .filter-section h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--main);
        }
        .filter-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 14px;
            color: var(--secondary);
            text-transform: uppercase;
        }
        .filter-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .search-bar {
            width: 100%;
            padding: 14px 20px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 14px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        .search-bar input {
            border: none;
            outline: none;
            width: 100%;
            font-family: inherit;
        }
        .search-bar i {
            color: var(--secondary);
        }
        .shop-header-row {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .btn-filters {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 1px solid #eaeaea;
            background: #fafafa;
            border-radius: 4px;
            font-weight: 500;
            font-size: 13px;
        }
        .products-found {
            font-size: 14px;
            color: var(--secondary);
        }
        .gender-badge {
            font-size: 11px;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .btn-apply {
            width: 100%;
            padding: 10px;
            background: var(--main);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-apply:hover {
            background: var(--secondary);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container navbar" style="justify-content: space-between;">
            <a href="index.php" class="logo">OCTARINE<span style="font-size: 20px;">.</span></a>
            <nav class="nav-links">
                <a href="shop.php" style="color: var(--main); font-weight: 700;">SHOP</a>
                <a href="#">SPECIAL COLLABORATION</a>
                <a href="#">PROMO</a>
                <a href="about.php">ABOUT</a>
                <a href="#">BLOG</a>
            </nav>
            <div class="nav-utils">
                <a href="#"><i class="fas fa-search"></i></a>
                <a href="login.php"><i class="far fa-user"></i></a>
                <a href="#"><i class="far fa-heart"></i></a>
                <a href="cart.php" style="position: relative;">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge"><?= $cart_count ?></span>
                </a>
            </div>
        </div>
    </header>

    <div class="container shop-container">
        <aside class="shop-sidebar">
            <form action="shop.php" method="GET" id="filterForm">
                <div class="shop-header-row" style="margin-bottom: 40px;">
                    <div class="btn-filters"><i class="fas fa-sliders-h"></i> Filters</div>
                    <div class="products-found"><?= count($products) ?> Products Found</div>
                </div>

                <div class="filter-section">
                    <h3>Gender preferences</h3>
                    <?php foreach($genders as $g): ?>
                        <label class="filter-label">
                            <input type="checkbox" name="gender[]" value="<?= $g['id'] ?>" <?= in_array($g['id'], $gender_filter) ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()">
                            <?= htmlspecialchars($g['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="filter-section">
                    <h3>Product Types</h3>
                    <?php foreach($scents as $s): ?>
                        <label class="filter-label">
                            <input type="checkbox" name="scent[]" value="<?= $s['id'] ?>" <?= in_array($s['id'], $scent_filter) ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()">
                            <?= htmlspecialchars($s['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </form>
        </aside>

        <main class="shop-main">
            <form action="shop.php" method="GET" style="margin-bottom: 0;">
                <?php foreach($gender_filter as $g): ?><input type="hidden" name="gender[]" value="<?= $g ?>"><?php endforeach; ?>
                <?php foreach($scent_filter as $s): ?><input type="hidden" name="scent[]" value="<?= $s ?>"><?php endforeach; ?>
                <div class="search-bar">
                    <input type="text" name="search" placeholder="Searching..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" style="background: none; border: none; cursor: pointer;"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <div class="product-grid" style="grid-template-columns: repeat(3, 1fr);">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if ($product['image_path']): ?>
                    <div class="product-img" style="background-image: url('uploads/<?= htmlspecialchars($product['image_path']) ?>'); background-size: cover; background-position: center;">
                        <div class="quick-add" data-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></div>
                    </div>
                    <?php else: ?>
                    <div class="product-img" style="background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center; color: #aaa;">
                            <i class="far fa-image" style="font-size: 32px; margin-bottom: 8px;"></i><br>
                            <span style="font-size: 12px; font-weight: 600;">NO IMAGE</span>
                        </div>
                        <div class="quick-add" data-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <?php if($product['gender_name']): ?>
                            <div class="gender-badge"><?= htmlspecialchars($product['gender_name']) ?></div>
                        <?php endif; ?>
                        
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p style="font-size: 13px; color: var(--secondary); margin-bottom: 8px;"><?= htmlspecialchars($product['description'] ?? 'Parfum Garansi Tahan Lama Aroma Fresh by Octarine') ?></p>
                        <div style="font-weight: 600; font-size: 15px; margin-bottom: 12px;">Rp <?= number_format($product['base_price'], 0, ',', '.') ?></div>
                        <button class="btn-product quick-add-btn" data-id="<?= $product['id'] ?>" style="cursor: pointer;">Add to Cart</button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($products)): ?>
                    <p style="text-align: center; grid-column: 1 / -1; padding: 40px; color: #666;">No products found matching your criteria.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Ensure checkboxes trigger form submit immediately
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
</body>
</html>
