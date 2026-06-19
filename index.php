<?php
session_start();
// Initialize restricted viewer connection
require_once 'config/db_viewer.php';

// Fetch only visible products
$stmt = $pdo_viewer->query("SELECT * FROM wms_products WHERE is_visible = 1 ORDER BY id DESC");
$products = $stmt->fetchAll();

// Fetch hero settings
try {
    $stmt_hero = $pdo_viewer->query("SELECT * FROM hero_settings WHERE id = 1");
    $hero = $stmt_hero->fetch();
} catch (\PDOException $e) {
    $hero = false; // Fallback if table doesn't exist yet
}

if (!$hero) {
    // Fallback defaults
    $hero = [
        'title' => 'OCTARINE',
        'subtitle' => 'Parfum lokal premium dengan aroma kelas dunia.<br>Discover your scent.',
        'button1_text' => 'Shop on Tokopedia',
        'button1_url' => 'https://www.tokopedia.com/octarineperfumeofficial',
        'button2_text' => 'Shop on Shopee',
        'button2_url' => 'https://shopee.co.id/octarineperfume.official',
        'image_path' => null
    ];
}

// Fetch mid banner settings
try {
    $stmt_banner = $pdo_viewer->query("SELECT * FROM mid_banner_settings WHERE id = 1");
    $mid_banner = $stmt_banner->fetch();
} catch (\PDOException $e) {
    $mid_banner = false; // Fallback
}

if (!$mid_banner) {
    $mid_banner = [
        'title' => 'Made with the finest natural ingredients.',
        'subtitle' => 'Explore our ingredient database to learn about where and how these are harvested.',
        'button_text' => 'Discover Now',
        'button_url' => '#',
        'image_path' => null
    ];
}

// Fetch customer reviews
try {
    // Get up to 4 reviews for display
    $stmt_reviews = $pdo_viewer->query("SELECT * FROM customer_reviews ORDER BY id DESC LIMIT 4");
    $reviews_data = $stmt_reviews->fetchAll();
} catch (\PDOException $e) {
    $reviews_data = false;
}

if (!$reviews_data || empty($reviews_data)) {
    // Fallback data
    $reviews_data = [
        [
            'customer_name' => 'Sarah J.',
            'review_text' => 'Aromanya sangat mewah dan tahan lama, benar-benar setara dengan parfum high-end internasional. Sangat merekomendasikan Vanilla Cake!',
            'rating' => 5
        ],
        [
            'customer_name' => 'Kevin R.',
            'review_text' => 'Black OPM is my new daily signature scent. The projection is insane and I keep getting compliments at work.',
            'rating' => 5
        ]
    ];
}

// Calculate total cart items
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Octarine — parfum lokal premium dengan aroma kelas dunia. Discover your scent.">
    <title>Octarine — Parfum Lokal Premium</title>
    <!-- Linking back to the root style.css with cache busting -->
    <link rel="stylesheet" href="style.css?v=<?= filemtime('style.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <header class="header">
        <div class="container navbar">
            <a href="#" class="logo">OCTARINE</a>
            <nav class="nav-links">
                <a href="shop.php">Shop</a>
                <a href="#special-collab">Special Collaboration</a>
                <a href="#promo">Promo</a>
                <a href="#about">About</a>
                <a href="#blog">Blog</a>
            </nav>
            <div class="nav-utils">
                <a href="#" aria-label="Search"><i class="fas fa-search"></i></a>
                <a href="#" aria-label="Wishlist"><i class="far fa-heart"></i><span class="badge">0</span></a>
                <a href="cart.php" aria-label="Cart"><i class="fas fa-shopping-bag"></i><span class="badge" id="cart-badge"><?= $cart_count ?></span></a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="font-size: 14px; font-weight: 600; margin-left: 10px;">Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="logout.php" class="login-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="login-link">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <?php 
    $hero_style = "";
    if (!empty($hero['image_path'])) {
        $hero_style = "style=\"background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.3)), url('uploads/" . htmlspecialchars($hero['image_path']) . "') center/cover;\"";
    }
    ?>
    <section class="hero" <?= $hero_style ?>>
        <div class="hero-content container">
            <h1><?= htmlspecialchars($hero['title']) ?></h1>
            <p><?= $hero['subtitle'] // allow HTML for <br> ?></p>
            <div class="hero-buttons">
                <?php if (!empty($hero['button1_text'])): ?>
                <a href="<?= htmlspecialchars($hero['button1_url']) ?>" target="_blank" class="btn btn-outline"><?= htmlspecialchars($hero['button1_text']) ?></a>
                <?php endif; ?>
                <?php if (!empty($hero['button2_text'])): ?>
                <a href="<?= htmlspecialchars($hero['button2_url']) ?>" target="_blank" class="btn btn-outline"><?= htmlspecialchars($hero['button2_text']) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Explore Collections -->
    <section class="collections container">
        <div class="section-header">
            <h2>Explore Collections</h2>
            <a href="#shop" class="view-all">View All Collection</a>
        </div>
        <div class="collection-grid">
            <div class="collection-card">
                <div class="img-placeholder bg-men"></div>
                <h3>MEN</h3>
            </div>
            <div class="collection-card">
                <div class="img-placeholder bg-unisex"></div>
                <h3>UNISEX</h3>
            </div>
            <div class="collection-card">
                <div class="img-placeholder bg-women"></div>
                <h3>WOMEN</h3>
            </div>
            <div class="collection-card">
                <div class="img-placeholder bg-segmented"></div>
                <h3>SEGMENTED</h3>
            </div>
        </div>
    </section>

    <!-- Our Products (DYNAMIC PHP CONTENT) -->
    <section class="products container" id="shop">
        <div class="section-header">
            <h2>Our Products</h2>
            <a href="#shop" class="view-all">View All Products</a>
        </div>
        <div class="product-grid" id="product-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <?php if ($product['image_path']): ?>
                <div class="product-img" style="background-image: url('uploads/<?= htmlspecialchars($product['image_path']) ?>'); background-size: cover; background-position: center;">
                    <div class="quick-add" data-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></div>
                </div>
                <?php else: ?>
                <div class="product-img bg-unisex">
                    <div class="quick-add" data-id="<?= $product['id'] ?>"><i class="fas fa-plus"></i></div>
                </div>
                <?php endif; ?>
                
                <div class="product-info">
                    <span class="category"><?= htmlspecialchars($product['product_type']) ?>, <?= htmlspecialchars($product['brand'] ?: 'N/A') ?></span>
                    <h4><?= htmlspecialchars($product['name']) ?></h4>
                    <?php 
                        $desc = $product['description'] ?: 'Premium Fragrance';
                        $words = explode(' ', $desc);
                        if (count($words) > 15) {
                            $desc = implode(' ', array_slice($words, 0, 15)) . '...';
                        }
                    ?>
                    <p><?= htmlspecialchars($desc) ?></p>
                    <div style="font-weight: 600; font-size: 15px; margin-bottom: 12px;">Rp <?= number_format($product['base_price'], 0, ',', '.') ?></div>
                    <button class="btn-product quick-add-btn" data-id="<?= $product['id'] ?>" style="cursor: pointer;">Add to Cart</button>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($products)): ?>
                <p style="text-align: center; grid-column: 1 / -1; padding: 40px; color: #666;">No products available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Mid Banner Section -->
    <?php 
    $banner_style = "";
    if (!empty($mid_banner['image_path'])) {
        $banner_style = "style=\"background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('uploads/" . htmlspecialchars($mid_banner['image_path']) . "') center/cover fixed;\"";
    }
    ?>
    <section class="mid-banner" <?= $banner_style ?>>
        <div class="container banner-content">
            <h2><?= htmlspecialchars($mid_banner['title']) ?></h2>
            <p><?= $mid_banner['subtitle'] ?></p>
            <?php if (!empty($mid_banner['button_text'])): ?>
            <a href="<?= htmlspecialchars($mid_banner['button_url']) ?>" class="btn btn-solid"><?= htmlspecialchars($mid_banner['button_text']) ?></a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials container">
        <div class="section-header center">
            <h2>Customer Reviews</h2>
            <p>Check out what our customer says about our product</p>
        </div>
        <div class="testimonial-grid">
             <?php foreach ($reviews_data as $rev): ?>
             <div class="testimonial-card">
                 <div class="stars">
                     <?php for($i = 0; $i < $rev['rating']; $i++): ?>
                         <i class="fas fa-star"></i>
                     <?php endfor; ?>
                 </div>
                 <p>"<?= htmlspecialchars($rev['review_text']) ?>"</p>
                 <h4>- <?= htmlspecialchars($rev['customer_name']) ?></h4>
             </div>
             <?php endforeach; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <h2 class="footer-logo">OCTARINE</h2>
                <p>hello@octarine.com<br>+62 800 1233 820</p>
                <div class="socials">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h4>Company</h4>
                <a href="#about">About Us</a>
                <a href="#shop">Shop</a>
                <a href="#contact">Contact Us</a>
                <a href="#career">Career</a>
            </div>
            <div class="footer-links">
                <h4>Information</h4>
                <a href="#shipping">Shipping</a>
                <a href="#return-refund">Return & Refund</a>
                <a href="#terms">Terms & Conditions</a>
                <a href="#faqs">FAQs</a>
            </div>
            <div class="footer-links">
                <h4>Legal</h4>
                <a href="#privacy">Privacy Policy</a>
                <a href="#affiliate">Become affiliate</a>
                <a href="admin/dashboard.php">Admin Panel</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if(href === '#') return;
                    
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Sticky Header Shadow on Scroll
            const header = document.querySelector('.header');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.style.boxShadow = '0px 4px 15px 0px rgba(0, 0, 0, 0.1)';
                } else {
                    header.style.boxShadow = '0px 4px 15px 0px rgba(0, 0, 0, 0.05)';
                }
            });

            // Add to cart functionality
            const quickAddBtns = document.querySelectorAll('.quick-add, .quick-add-btn');
            const cartBadge = document.getElementById('cart-badge');
            
            quickAddBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const productId = btn.getAttribute('data-id');
                    
                    fetch('cart_action.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=add&product_id=${productId}&quantity=1`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            cartBadge.textContent = data.total_items;
                            
                            // Animation effect
                            cartBadge.style.transform = 'scale(1.5)';
                            setTimeout(() => {
                                cartBadge.style.transform = 'scale(1)';
                            }, 200);
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
