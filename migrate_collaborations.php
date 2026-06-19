<?php
require_once 'config/db_modifier.php';

try {
    $pdo_modifier->exec("CREATE TABLE IF NOT EXISTS collaborations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        image_path VARCHAR(255) NULL
    )");

    $stmt = $pdo_modifier->query("SELECT COUNT(*) FROM collaborations");
    if ($stmt->fetchColumn() == 0) {
        $default_text = "Finding the right perfume is more than just picking a nice-smelling bottle off the shelf. It's a personal journey — one that connects deeply with your personality, mood, style, and even your memories. A well-chosen fragrance can boost your confidence, leave a lasting impression, and become an invisible part of your identity.";
        
        $stmt = $pdo_modifier->prepare("INSERT INTO collaborations (title, description) VALUES (?, ?)");
        $stmt->execute(['Octarine X Mewwa', $default_text]);
        $stmt->execute(['Octarine X Mohan', $default_text]);
        $stmt->execute(['Octarine x Gamagudabo', $default_text]);
    }
    
    echo "Collaborations migration successful.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
