CREATE DATABASE IF NOT EXISTS perfume_store;
USE perfume_store;

CREATE TABLE IF NOT EXISTS wms_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wms_id INT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    brand VARCHAR(100) NULL,
    product_type VARCHAR(50) DEFAULT 'SINGLE',
    base_price DECIMAL(12, 2) NOT NULL,
    weight DECIMAL(8, 2) DEFAULT 0.00,
    variants_count INT DEFAULT 0,
    is_visible TINYINT(1) DEFAULT 1,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    last_synced TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hero Settings
CREATE TABLE IF NOT EXISTS hero_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT NULL,
    button1_text VARCHAR(100) NULL,
    button1_url VARCHAR(255) NULL,
    button2_text VARCHAR(100) NULL,
    button2_url VARCHAR(255) NULL,
    image_path VARCHAR(255) NULL
);

INSERT IGNORE INTO hero_settings (id, title, subtitle, button1_text, button1_url, button2_text, button2_url, image_path) VALUES 
(1, 'OCTARINE', 'Parfum lokal premium dengan aroma kelas dunia.<br>Discover your scent.', 'Shop on Tokopedia', 'https://www.tokopedia.com/octarineperfumeofficial', 'Shop on Shopee', 'https://shopee.co.id/octarineperfume.official', NULL);

-- Least privilege user creation
CREATE USER IF NOT EXISTS 'viewer'@'localhost' IDENTIFIED BY 'viewer_password';
GRANT SELECT ON perfume_store.* TO 'antigravity_viewer'@'localhost';

CREATE USER IF NOT EXISTS 'modifier'@'localhost' IDENTIFIED BY 'modifier_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON perfume_store.* TO 'modifier'@'localhost';

FLUSH PRIVILEGES;
