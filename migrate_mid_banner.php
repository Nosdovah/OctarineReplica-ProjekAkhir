<?php
require_once 'config/db_modifier.php';
try {
    $pdo_modifier->exec("CREATE TABLE IF NOT EXISTS mid_banner_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        subtitle TEXT NULL,
        button_text VARCHAR(100) NULL,
        button_url VARCHAR(255) NULL,
        image_path VARCHAR(255) NULL
    )");

    $pdo_modifier->exec("INSERT IGNORE INTO mid_banner_settings (id, title, subtitle, button_text, button_url, image_path) VALUES 
    (1, 'Made with the finest natural ingredients.', 'Explore our ingredient database to learn about where and how these are harvested.', 'Discover Now', '#', NULL)");

    echo "Mid banner migration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
