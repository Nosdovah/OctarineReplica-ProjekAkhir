<?php
require_once 'config/db_modifier.php';

try {
    $pdo_modifier->exec("CREATE TABLE IF NOT EXISTS promo_settings (
        id INT PRIMARY KEY,
        hero_heading VARCHAR(255) DEFAULT 'Exclusive Promos & Campaigns',
        hero_subheading TEXT NULL,
        hero_image VARCHAR(255) NULL,
        carousel_heading VARCHAR(255) DEFAULT 'Collaboration Promo'
    )");

    $stmt = $pdo_modifier->query("SELECT COUNT(*) FROM promo_settings WHERE id = 1");
    if ($stmt->fetchColumn() == 0) {
        $subheading = "Discover exclusive promotions, limited-time campaigns, and special deals tailored just for you.";
        $stmt = $pdo_modifier->prepare("INSERT INTO promo_settings (id, hero_heading, hero_subheading, carousel_heading) VALUES (1, 'Exclusive Promos & Campaigns', ?, 'Collaboration Promo')");
        $stmt->execute([$subheading]);
    }
    
    echo "Promo settings migration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
