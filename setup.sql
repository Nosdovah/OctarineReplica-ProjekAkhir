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
    gender_id INT NULL,
    category_id INT NULL,
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

-- Mid Banner Settings
CREATE TABLE IF NOT EXISTS mid_banner_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT NULL,
    button_text VARCHAR(100) NULL,
    button_url VARCHAR(255) NULL,
    image_path VARCHAR(255) NULL
);

INSERT IGNORE INTO mid_banner_settings (id, title, subtitle, button_text, button_url, image_path) VALUES 
(1, 'Made with the finest natural ingredients.', 'Explore our ingredient database to learn about where and how these are harvested.', 'Discover Now', '#', NULL);

-- Customer Reviews
CREATE TABLE IF NOT EXISTS customer_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    review_text TEXT NOT NULL,
    rating INT DEFAULT 5
);

INSERT IGNORE INTO customer_reviews (id, customer_name, review_text, rating) VALUES 
(1, 'Sarah J.', 'Aromanya sangat mewah dan tahan lama, benar-benar setara dengan parfum high-end internasional. Sangat merekomendasikan Vanilla Cake!', 5),
(2, 'Kevin R.', 'Black OPM is my new daily signature scent. The projection is insane and I keep getting compliments at work.', 5);

-- Categories (Gender Preferences and Product Types)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL, /* 'gender' or 'scent' */
    name VARCHAR(100) NOT NULL
);

INSERT IGNORE INTO categories (id, type, name) VALUES 
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
(11, 'scent', 'Arabic');



-- Least privilege user creation
CREATE USER IF NOT EXISTS 'viewer'@'localhost' IDENTIFIED BY 'viewer_password';
GRANT SELECT ON perfume_store.* TO 'antigravity_viewer'@'localhost';

CREATE USER IF NOT EXISTS 'modifier'@'localhost' IDENTIFIED BY 'modifier_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON perfume_store.* TO 'modifier'@'localhost';

FLUSH PRIVILEGES;
