<?php
require_once 'config/db_modifier.php';
try {
    $pdo_modifier->exec("CREATE TABLE IF NOT EXISTS hero_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        subtitle TEXT NULL,
        button1_text VARCHAR(100) NULL,
        button1_url VARCHAR(255) NULL,
        button2_text VARCHAR(100) NULL,
        button2_url VARCHAR(255) NULL,
        image_path VARCHAR(255) NULL
    )");

    $pdo_modifier->exec("INSERT IGNORE INTO hero_settings (id, title, subtitle, button1_text, button1_url, button2_text, button2_url, image_path) VALUES 
    (1, 'OCTARINE', 'Parfum lokal premium dengan aroma kelas dunia.<br>Discover your scent.', 'Shop on Tokopedia', 'https://www.tokopedia.com/octarineperfumeofficial', 'Shop on Shopee', 'https://shopee.co.id/octarineperfume.official', NULL)");

    echo "Migration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
