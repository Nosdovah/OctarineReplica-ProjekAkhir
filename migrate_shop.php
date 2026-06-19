<?php
require_once 'config/db_modifier.php';
try {
    // Categories table
    $pdo_modifier->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL, /* 'gender' or 'scent' */
        name VARCHAR(100) NOT NULL
    )");

    // Insert defaults
    $pdo_modifier->exec("INSERT IGNORE INTO categories (id, type, name) VALUES 
    (1, 'gender', 'MEN'),
    (2, 'gender', 'WOMEN'),
    (3, 'gender', 'UNISEX'),
    (4, 'gender', 'SEGMENTED'),
    (5, 'scent', 'Citrus'),
    (6, 'scent', 'new type'),
    (7, 'scent', 'Ember'),
    (8, 'scent', 'Emberien'),
    (9, 'scent', 'Oud'),
    (10, 'scent', 'Aquatic Citrus'),
    (11, 'scent', 'Arabic')
    ");

    // Add columns to wms_products if they don't exist
    // Using a try-catch for altering table to ignore duplicate column errors gracefully
    try {
        $pdo_modifier->exec("ALTER TABLE wms_products ADD COLUMN gender_id INT NULL");
    } catch (PDOException $e) {}
    
    try {
        $pdo_modifier->exec("ALTER TABLE wms_products ADD COLUMN category_id INT NULL");
    } catch (PDOException $e) {}
    
    echo "Shop migration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
