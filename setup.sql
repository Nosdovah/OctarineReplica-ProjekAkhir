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

-- Least privilege user creation
CREATE USER IF NOT EXISTS 'viewer'@'localhost' IDENTIFIED BY 'viewer_password';
GRANT SELECT ON perfume_store.* TO 'antigravity_viewer'@'localhost';

CREATE USER IF NOT EXISTS 'modifier'@'localhost' IDENTIFIED BY 'modifier_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON perfume_store.* TO 'modifier'@'localhost';

FLUSH PRIVILEGES;
