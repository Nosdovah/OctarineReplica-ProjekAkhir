<?php
require_once 'config/db_modifier.php';

try {
    $pdo_modifier->exec("ALTER TABLE wms_products ADD COLUMN is_promo TINYINT(1) DEFAULT 0");
    echo "Successfully added 'is_promo' column to wms_products.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'is_promo' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
