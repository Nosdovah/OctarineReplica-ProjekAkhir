<?php
session_start();
require_once 'config/db_viewer.php';

try {
    $stmt = $pdo_viewer->query("
        SELECT p.*, g.name as gender_name, c.name as category_name
        FROM wms_products p
        LEFT JOIN categories g ON p.gender_id = g.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_visible = 1 AND p.is_promo = 1
        ORDER BY p.id DESC
        LIMIT 8
    ");
    $promo_products = $stmt->fetchAll();

    // Fetch promo settings
    $stmt = $pdo_viewer->query("SELECT * FROM promo_settings WHERE id = 1");
    $promo_settings = $stmt->fetch();
} catch (PDOException $e) {
    $promo_products = [];
    $promo_settings = null;
}

if (!$promo_settings) {
    $promo_settings = [
        'hero_heading' => 'Exclusive Promos & Campaigns',
        'hero_subheading' => 'Discover exclusive promotions, limited-time campaigns, and special deals tailored just for you.',
        'hero_image' => '',
        'carousel_heading' => 'Collaboration Promo'
    ];
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
    <title>Promo - Octarine</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= filemtime('style.css') ?>">
    <style>
        .promo-hero {
            text-align: center;
            padding: 80px 20px 40px;
        }
        
        .promo-hero h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #111;
        }
        
        .promo-hero p {
            font-size: 16px;
            color: var(--secondary);
            max-width: 600px;
            margin: 0 auto 50px;
            line-height: 1.6;
        }
        
        .promo-hero-image {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            height: 500px;
            background-color: #f5f5f5;
            background-size: cover;
            background-position: center;
            border-radius: 8px;
            margin-bottom: 80px;
        }
        
        .promo-section {
            margin-bottom: 100px;
        }
        
        .promo-section h2 {
            font-size: 32px;
            text-align: center;
            margin-bottom: 50px;
        }
        
        /* Simple CSS Carousel */
        .carousel-container {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 32px;
            padding-bottom: 40px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        
        .carousel-container::-webkit-scrollbar {
            height: 6px;
        }
        .carousel-container::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
        
        .carousel-item {
            scroll-snap-align: start;
            flex: 0 0 calc(25% - 24px); /* Show 4 items */
            min-width: 250px;
        }
        
        /* Reusing product card styles from index.php / shop.php */
        .gender-badge {
            font-size: 11px;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container navbar" style="justify-content: space-between;">
            <a href="index.php" class="logo">OCTARINE<span style="font-size: 20px;">.</span></a>
            <nav class="nav-links">
                <a href="shop.php">SHOP</a>
                <a href="promo.php" style="color: var(--main); font-weight: 700;">PROMO</a>
                <a href="about.php">ABOUT</a>
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

    <div class="container">
        <!-- Hero Section -->
        <div class="promo-hero">
            <h1><?= htmlspecialchars($promo_settings['hero_heading']) ?></h1>
            <p><?= nl2br(htmlspecialchars($promo_settings['hero_subheading'])) ?></p>
            <div class="promo-hero-image" style="<?= $promo_settings['hero_image'] ? "background-image: url('uploads/".htmlspecialchars($promo_settings['hero_image'])."');" : "background-image: url('https://images.unsplash.com/photo-1615486171448-4ffd3b5eb10f?q=80&w=2672&auto=format&fit=crop');" ?>"></div>
        </div>

        <!-- Carousel Section -->
        <div class="promo-section">
            <h2><?= htmlspecialchars($promo_settings['carousel_heading']) ?></h2>
            
            <div class="carousel-container">
                <?php if (empty($promo_products)): ?>
                    <p style="text-align: center; width: 100%; color: var(--secondary);">No promo products available.</p>
                <?php else: ?>
                    <?php foreach ($promo_products as $product): ?>
                    <div class="carousel-item">
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
                                <div class="gender-badge">
                                    <?php 
                                    $tags = [];
                                    if($product['gender_name']) $tags[] = $product['gender_name'];
                                    if($product['category_name']) $tags[] = $product['category_name'];
                                    echo htmlspecialchars(implode(', ', $tags));
                                    ?>
                                </div>
                                
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p style="font-size: 13px; color: var(--secondary); margin-bottom: 8px;"><?= htmlspecialchars($product['description'] ?? 'Parfum Garansi Tahan Lama Aroma Fresh by Octarine') ?></p>
                                <div style="font-weight: 600; font-size: 15px; margin-bottom: 12px;">Rp <?= number_format($product['base_price'], 0, ',', '.') ?></div>
                                <button class="btn-product quick-add-btn" data-id="<?= $product['id'] ?>" style="cursor: pointer; width: 100%;">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background: #111; color: #fff; padding: 60px 0;">
        <div class="container" style="display: flex; justify-content: space-between;">
            <div>
                <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 20px; letter-spacing: 2px;">OCTARINE.</h3>
                <p style="color: #aaa; margin-bottom: 10px;">hello@octarine@gmail.com</p>
                <p style="color: #aaa;">+62 800 1233 820</p>
            </div>
            <div>
                <h4 style="margin-bottom: 20px; text-transform: uppercase;">Information</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;"><a href="about.php" style="color: #aaa; text-decoration: none;">About Us</a></li>
                    <li style="margin-bottom: 10px;"><a href="shop.php" style="color: #aaa; text-decoration: none;">Shop</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Contact Us</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Career</a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 20px; text-transform: uppercase;">Customer Services</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Shipping</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Return & Refund</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Terms & Conditions</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">FAQs</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Privacy Policy</a></li>
                    <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Become affiliate</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
