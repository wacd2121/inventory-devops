-- Create Database if not exists (for local development outside docker if needed)
CREATE DATABASE IF NOT EXISTS inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin User (Password is 'admin123' hashed with Bcrypt)
INSERT INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@inventory.com', '$2y$10$k1wX.Wn5wzE/lH0d1/nkeOq3C0aI704P4.K7Yv0Uj2w8XpY1CjFzS', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed 8 Categories
INSERT INTO categories (id, name, description) VALUES
(1, 'Floor Tiles', 'Tiles suitable for flooring applications'),
(2, 'Wall Tiles', 'Decorative and functional wall tiles'),
(3, 'Bathroom & Washroom', 'Waterproof tiles and fixtures'),
(4, 'Kitchen Backsplash', 'Heat-resistant and easy-to-clean tiles'),
(5, 'Outdoor & Terracotta', 'Weatherproof tiles for outdoor spaces'),
(6, 'Adhesives & Grout', 'Installation materials for tiling'),
(7, 'Tools & Accessories', 'Tiling spacers, cutters, and tools'),
(8, 'Polished Porcelain', 'High-end glossy porcelain tiles')
ON DUPLICATE KEY UPDATE id=id;

-- 3. Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed 25 Products (with 4 Low Stock items where quantity <= minimum_stock)
INSERT INTO products (id, product_code, name, category_id, price, quantity, minimum_stock) VALUES
-- Floor Tiles (Cat 1)
(1, 'FL-CER-01', 'Ceramic Beige Floor Tile', 1, 150.00, 200, 20),
(2, 'FL-POR-02', 'Polished Grey Marble Porcelain', 1, 350.00, 150, 15),
(3, 'FL-WD-03', 'Wood-Look Oak Plank Tile', 1, 280.00, 80, 15),
(4, 'FL-SL-04', 'Dark Slate Floor Tile', 1, 220.00, 110, 10),
-- Wall Tiles (Cat 2)
(5, 'WL-SUB-01', 'Classic White Subway Tile', 2, 85.00, 500, 50),
(6, 'WL-MOS-02', 'Blue Glass Mosaic Wall Tile', 2, 450.00, 12, 15), -- LOW STOCK (quantity 12 <= min 15)
(7, 'WL-HEX-03', 'Hexagonal Matte Black Tile', 2, 190.00, 75, 10),
-- Bathroom Tiles (Cat 3)
(8, 'BT-NS-01', 'Non-Slip Shower Floor Tile', 3, 160.00, 95, 10),
(9, 'BT-PT-02', 'Pebble Stone Bath Mat Tile', 3, 380.00, 4, 10), -- LOW STOCK (quantity 4 <= min 10)
(10, 'BT-MR-03', 'Carrara White Bath Wall Tile', 3, 420.00, 60, 8),
-- Kitchen Backsplash (Cat 4)
(11, 'KT-AR-01', 'Arabesque Pattern Backsplash', 4, 310.00, 45, 10),
(12, 'KT-GL-02', 'Glazed Green Ceramic Backsplash', 4, 140.00, 120, 20),
(13, 'KT-MT-03', 'Stainless Steel Mosaic Tile', 4, 600.00, 3, 5), -- LOW STOCK (quantity 3 <= min 5)
-- Outdoor Tiles (Cat 5)
(14, 'OD-TC-01', 'Rustic Terracotta Patio Tile', 5, 110.00, 180, 25),
(15, 'OD-QZ-02', 'Grey Quartzite Outdoor Flagstone', 5, 270.00, 90, 12),
(16, 'OD-BY-03', 'Interlocking Deck Tile Wood', 5, 180.00, 22, 10),
-- Adhesives & Grout (Cat 6)
(17, 'AD-FL-01', 'Flexi-Bond Tile Adhesive 20kg', 6, 45.00, 300, 30),
(18, 'AD-WP-02', 'Waterproof Grout White 5kg', 6, 25.00, 150, 15),
(19, 'AD-GR-03', 'Charcoal Grey Grout 5kg', 6, 25.00, 140, 15),
-- Tools & Accessories (Cat 7)
(20, 'TL-SP-01', 'Tile Spacers 2mm (Pack of 500)', 7, 8.50, 400, 40),
(21, 'TL-CT-02', 'Professional Manual Tile Cutter', 7, 1250.00, 15, 3),
(22, 'TL-TR-03', 'Notched Trowel 10mm', 7, 18.00, 60, 5),
(23, 'TL-NIP-04', 'Tile Nippers Heavy Duty', 7, 24.00, 2, 5), -- LOW STOCK (quantity 2 <= min 5)
-- Polished Porcelain (Cat 8)
(24, 'PP-SL-01', 'Super Glossy Snow White 60x60', 8, 300.00, 160, 20),
(25, 'PP-ON-02', 'Polished Onyx Gold Porcelain', 8, 480.00, 85, 12)
ON DUPLICATE KEY UPDATE id=id;

-- 4. Stock Transactions Table (Optional, but implemented to demonstrate proper DB practices)
CREATE TABLE IF NOT EXISTS stock_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    transaction_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed some transactions
INSERT INTO stock_transactions (product_id, transaction_type, quantity) VALUES
(1, 'IN', 200),
(2, 'IN', 150),
(5, 'IN', 500),
(6, 'IN', 20),
(6, 'OUT', 8),
(9, 'IN', 10),
(9, 'OUT', 6),
(13, 'IN', 10),
(13, 'OUT', 7),
(23, 'IN', 5),
(23, 'OUT', 3)
ON DUPLICATE KEY UPDATE id=id;
