<?php
session_start();
require_once 'config/db_viewer.php';

try {
    $stmt = $pdo_viewer->query("SELECT * FROM about_settings WHERE id = 1");
    $about = $stmt->fetch();
} catch (PDOException $e) {
    $about = null;
}

if (!$about) {
    // Fallback defaults
    $default_text = "Finding the right perfume is more than just picking a nice-smelling bottle off the shelf. It's a personal journey — one that connects deeply with your personality, mood, style, and even your memories. A well-chosen fragrance can boost your confidence, leave a lasting impression, and become an invisible part of your identity. Whether you're new to the world of perfumes or a seasoned scent enthusiast, here's a deeper look into how to discover your perfect fragrance match:";
    $about = [
        'banner_image' => '',
        'history_image' => '',
        'history_heading' => 'OCTARINE HISTORY',
        'history_text' => $default_text,
        'collab1_title' => 'Octarine X Mewwa',
        'collab1_text' => $default_text,
        'collab1_image' => '',
        'collab2_title' => 'Octarine X Mohan',
        'collab2_text' => $default_text,
        'collab2_image' => '',
        'collab3_title' => 'Octarine x Gamagudabo',
        'collab3_text' => $default_text,
        'collab3_image' => ''
    ];
}

try {
    $stmt = $pdo_viewer->query("SELECT * FROM collaborations ORDER BY id ASC");
    $collaborations = $stmt->fetchAll();
} catch (PDOException $e) {
    $collaborations = [];
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
    <title>About Us - Octarine</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= filemtime('style.css') ?>">
    <style>
        .about-banner {
            width: 100%;
            height: 400px;
            background-size: cover;
            background-position: center;
            background-color: #eaeaea;
            margin-bottom: 80px;
        }
        
        .history-section {
            display: flex;
            align-items: center;
            gap: 60px;
            margin-bottom: 100px;
        }
        
        .history-image {
            flex: 1;
            height: 600px;
            background-size: cover;
            background-position: center;
            background-color: #f5f5f5;
        }
        
        .history-content {
            flex: 1;
            padding-right: 40px;
        }
        
        .history-content h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .history-content p {
            color: var(--secondary);
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .collab-section {
            margin-bottom: 100px;
        }
        
        .collab-section h2 {
            font-size: 32px;
            text-align: center;
            margin-bottom: 50px;
            text-transform: capitalize;
        }
        
        .collab-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }
        
        .collab-card {
            display: flex;
            flex-direction: column;
        }
        
        .collab-image {
            width: 100%;
            height: 250px;
            background-size: cover;
            background-position: center;
            background-color: #f5f5f5;
            margin-bottom: 24px;
        }
        
        .collab-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .collab-card p {
            color: var(--secondary);
            font-size: 15px;
            line-height: 1.7;
        }

        .btn-check-now {
            display: inline-block;
            background: #111;
            color: #fff;
            padding: 14px 32px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            transition: 0.3s;
        }
        
        .btn-check-now:hover {
            background: #333;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container navbar" style="justify-content: space-between;">
            <a href="index.php" class="logo">OCTARINE<span style="font-size: 20px;">.</span></a>
            <nav class="nav-links">
                <a href="shop.php">SHOP</a>
                <a href="#">SPECIAL COLLABORATION</a>
                <a href="#">PROMO</a>
                <a href="about.php" style="color: var(--main); font-weight: 700;">ABOUT</a>
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

    <!-- Banner -->
    <div class="about-banner" style="<?= $about['banner_image'] ? "background-image: url('uploads/".htmlspecialchars($about['banner_image'])."');" : "" ?>"></div>

    <div class="container">
        <!-- History Section -->
        <div class="history-section">
            <div class="history-image" style="<?= $about['history_image'] ? "background-image: url('uploads/".htmlspecialchars($about['history_image'])."');" : "" ?>"></div>
            <div class="history-content">
                <h2><?= htmlspecialchars($about['history_heading']) ?></h2>
                <p><?= nl2br(htmlspecialchars($about['history_text'])) ?></p>
                <a href="shop.php" class="btn-check-now">Check Now!</a>
            </div>
        </div>

        <!-- Special Collaborations -->
        <div class="collab-section">
            <h2>Special Collaborations</h2>
            <div class="collab-grid">
                <?php if (empty($collaborations)): ?>
                    <p style="text-align: center; grid-column: 1 / -1; color: var(--secondary);">No collaborations found.</p>
                <?php else: ?>
                    <?php foreach ($collaborations as $collab): ?>
                        <div class="collab-card">
                            <div class="collab-image" style="<?= $collab['image_path'] ? "background-image: url('uploads/".htmlspecialchars($collab['image_path'])."');" : "" ?>"></div>
                            <h3><?= htmlspecialchars($collab['title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($collab['description'])) ?></p>
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
