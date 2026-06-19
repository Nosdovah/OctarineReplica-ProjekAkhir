<?php
require_once 'config/db_modifier.php';
try {
    $pdo_modifier->exec("CREATE TABLE IF NOT EXISTS customer_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        review_text TEXT NOT NULL,
        rating INT DEFAULT 5
    )");

    $pdo_modifier->exec("INSERT IGNORE INTO customer_reviews (id, customer_name, review_text, rating) VALUES 
    (1, 'Sarah J.', 'Aromanya sangat mewah dan tahan lama, benar-benar setara dengan parfum high-end internasional. Sangat merekomendasikan Vanilla Cake!', 5),
    (2, 'Kevin R.', 'Black OPM is my new daily signature scent. The projection is insane and I keep getting compliments at work.', 5)");

    echo "Reviews migration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
