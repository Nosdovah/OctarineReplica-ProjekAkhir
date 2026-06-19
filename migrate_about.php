<?php
require_once 'config/db_modifier.php';

try {
    $pdo_modifier->exec("CREATE TABLE IF NOT EXISTS about_settings (
        id INT PRIMARY KEY,
        banner_image VARCHAR(255) NULL,
        history_image VARCHAR(255) NULL,
        history_heading VARCHAR(255) DEFAULT 'OCTARINE HISTORY',
        history_text TEXT NULL,
        
        collab1_title VARCHAR(255) NULL,
        collab1_text TEXT NULL,
        collab1_image VARCHAR(255) NULL,
        
        collab2_title VARCHAR(255) NULL,
        collab2_text TEXT NULL,
        collab2_image VARCHAR(255) NULL,
        
        collab3_title VARCHAR(255) NULL,
        collab3_text TEXT NULL,
        collab3_image VARCHAR(255) NULL
    )");

    $stmt = $pdo_modifier->query("SELECT id FROM about_settings WHERE id = 1");
    if (!$stmt->fetch()) {
        $default_text = "Finding the right perfume is more than just picking a nice-smelling bottle off the shelf. It's a personal journey — one that connects deeply with your personality, mood, style, and even your memories. A well-chosen fragrance can boost your confidence, leave a lasting impression, and become an invisible part of your identity. Whether you're new to the world of perfumes or a seasoned scent enthusiast, here's a deeper look into how to discover your perfect fragrance match:";
        
        $stmt = $pdo_modifier->prepare("INSERT INTO about_settings (id, history_text, collab1_title, collab1_text, collab2_title, collab2_text, collab3_title, collab3_text) VALUES (1, ?, 'Octarine X Mewwa', ?, 'Octarine X Mohan', ?, 'Octarine x Gamagudabo', ?)");
        $stmt->execute([$default_text, $default_text, $default_text, $default_text]);
    }
    
    echo "About page migration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
