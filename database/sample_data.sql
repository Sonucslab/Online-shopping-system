-- ============================================================
-- NEXUS ELECTRONICS — SAMPLE DATA (DML)
-- Realistic test data for all 8 tables
-- ============================================================

USE nexus_shop;

-- ============================================================
-- 1. CUSTOMERS (10 rows — 1 admin + 9 customers)
-- Password for all: "password123" → bcrypt hash
-- ============================================================
INSERT INTO Customer (first_name, last_name, email, password_hash, phone, address, city, zip_code, role) VALUES
('Admin',   'Nexus',    'admin@nexus.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000001', 'Nexus HQ, Tower A',       'Mumbai',    '400001', 'admin'),
('Rahul',   'Sharma',   'rahul.sharma@gmail.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', '12 MG Road',              'Bangalore', '560001', 'customer'),
('Priya',   'Patel',    'priya.patel@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', '45 Jubilee Hills',        'Hyderabad', '500033', 'customer'),
('Amit',    'Kumar',    'amit.kumar@yahoo.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543212', '78 Sector 15',            'Noida',     '201301', 'customer'),
('Sneha',   'Reddy',    'sneha.reddy@outlook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543213', '23 Anna Nagar',           'Chennai',   '600040', 'customer'),
('Vikram',  'Singh',    'vikram.singh@gmail.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543214', '56 Civil Lines',          'Delhi',     '110054', 'customer'),
('Ananya',  'Gupta',    'ananya.gupta@gmail.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543215', '89 Park Street',          'Kolkata',   '700016', 'customer'),
('Rohan',   'Joshi',    'rohan.joshi@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543216', '34 FC Road',              'Pune',      '411004', 'customer'),
('Kavya',   'Nair',     'kavya.nair@gmail.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543217', '67 Marine Drive',         'Kochi',     '682001', 'customer'),
('Arjun',   'Mehta',    'arjun.mehta@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543218', '12 SG Highway',           'Ahmedabad', '380015', 'customer');

-- ============================================================
-- 2. CATEGORIES (6 rows)
-- ============================================================
INSERT INTO Category (name, description) VALUES
('Laptops',      'Notebook computers, ultrabooks, and gaming laptops'),
('Smartphones',  'Mobile phones and phablets'),
('Audio',        'Headphones, earbuds, speakers, and microphones'),
('Desktops',     'Desktop PCs, monitors, and workstations'),
('Tablets',      'Tablet computers and 2-in-1 devices'),
('Accessories',  'Chargers, cables, cases, keyboards, and mice');

-- ============================================================
-- 3. PRODUCTS (18 rows)
-- ============================================================
INSERT INTO Product (category_id, name, description, price, stock_qty, image_url) VALUES
-- Laptops (cat 1)
(1, 'Nexus ProBook M2',        '14" Laptop, Intel i7, 16GB RAM, 512GB SSD',            1299.00, 25, 'images/products/probook-m2.jpg'),
(1, 'Nexus UltraSlim X1',      '13.3" Ultrabook, AMD Ryzen 7, 8GB RAM, 256GB SSD',      899.00, 30, 'images/products/ultraslim-x1.jpg'),
(1, 'Nexus GameForce RTX',     '15.6" Gaming Laptop, RTX 4060, 32GB RAM, 1TB SSD',     1799.00, 12, 'images/products/gameforce-rtx.jpg'),

-- Smartphones (cat 2)
(2, 'Aura X1 Smartphone',      '6.5" AMOLED, Snapdragon 8 Gen 2, 128GB, 50MP Camera',   899.00, 40, 'images/products/aura-x1.jpg'),
(2, 'Aura Lite S3',            '6.1" LCD, MediaTek Dimensity 700, 64GB, 12MP Camera',    349.00, 60, 'images/products/aura-lite-s3.jpg'),
(2, 'Aura Pro Max',            '6.7" AMOLED, Snapdragon 8 Gen 3, 256GB, 200MP Camera',  1199.00, 18, 'images/products/aura-pro-max.jpg'),

-- Audio (cat 3)
(3, 'Sonic ANC Pro Buds',      'True Wireless ANC Earbuds, 30hr battery, IPX5',          199.00, 75, 'images/products/sonic-anc.jpg'),
(3, 'BassMax Studio Headset',  'Over-ear Studio Headphones, Hi-Res Audio, Foldable',     149.00, 50, 'images/products/bassmax-studio.jpg'),
(3, 'SoundWave BT Speaker',   'Portable Bluetooth Speaker, 20W, Waterproof IP67',        79.00, 90, 'images/products/soundwave-bt.jpg'),

-- Desktops (cat 4)
(4, 'ClearView 4K Monitor',    '27" 4K IPS Monitor, 144Hz, HDR400, USB-C',              549.00, 20, 'images/products/clearview-4k.jpg'),
(4, 'Nexus WorkStation Pro',   'Desktop PC, Intel i9, 64GB RAM, 2TB NVMe, RTX 4080',   2499.00,  8, 'images/products/workstation-pro.jpg'),
(4, 'Nexus MiniPC Cube',       'Compact Desktop, Ryzen 5, 16GB RAM, 512GB SSD',         599.00, 35, 'images/products/minipc-cube.jpg'),

-- Tablets (cat 5)
(5, 'FlexPad Ultra S2',        '11" 2K Display, Snapdragon 870, 128GB, S Pen included',  699.00, 22, 'images/products/flexpad-ultra.jpg'),
(5, 'FlexPad Lite 10',         '10.1" HD Display, Helio G80, 64GB, WiFi only',           249.00, 45, 'images/products/flexpad-lite.jpg'),

-- Accessories (cat 6)
(6, 'Nexus USB-C Hub 7-in-1',  'USB-C to HDMI, USB 3.0, SD Card, PD 100W',               49.00, 100, 'images/products/usbc-hub.jpg'),
(6, 'ErgoType Mech Keyboard',  'Mechanical Keyboard, Cherry MX Blue, RGB Backlit',        129.00,  55, 'images/products/ergotype-kb.jpg'),
(6, 'PrecisionGlide Mouse',    'Wireless Ergonomic Mouse, 4000 DPI, USB-C Rechargeable',   59.00,  70, 'images/products/precision-mouse.jpg'),
(6, 'NexCharge 65W GaN',       '65W GaN Charger, Dual USB-C + USB-A, Foldable Plug',      39.00, 120, 'images/products/nexcharge-65w.jpg');

-- ============================================================
-- 4. CARTS (3 active carts)
-- ============================================================
INSERT INTO Cart (customer_id) VALUES
(2),  -- Rahul's cart
(4),  -- Amit's cart
(7);  -- Ananya's cart

-- ============================================================
-- 5. CART ITEMS (5 rows)
-- ============================================================
INSERT INTO CartItem (cart_id, product_id, quantity) VALUES
(1, 1, 1),   -- Rahul: 1× ProBook M2
(1, 7, 2),   -- Rahul: 2× Sonic ANC Pro Buds
(2, 4, 1),   -- Amit: 1× Aura X1
(2, 15, 1),  -- Amit: 1× USB-C Hub
(3, 13, 1);  -- Ananya: 1× FlexPad Ultra

-- ============================================================
-- 6. ORDERS (8 rows)
-- ============================================================
INSERT INTO Orders (customer_id, order_date, status, shipping_address, total_amount) VALUES
(2, '2026-08-01 10:30:00', 'delivered',   '12 MG Road, Bangalore 560001',       1498.00),
(3, '2026-08-03 14:15:00', 'delivered',   '45 Jubilee Hills, Hyderabad 500033',  899.00),
(5, '2026-08-05 09:45:00', 'shipped',     '23 Anna Nagar, Chennai 600040',       348.00),
(6, '2026-08-08 16:20:00', 'shipped',     '56 Civil Lines, Delhi 110054',       1928.00),
(8, '2026-08-10 11:00:00', 'processing',  '34 FC Road, Pune 411004',             728.00),
(9, '2026-08-12 13:30:00', 'processing',  '67 Marine Drive, Kochi 682001',       249.00),
(4, '2026-08-14 08:00:00', 'pending',     '78 Sector 15, Noida 201301',         2548.00),
(10,'2026-08-16 17:45:00', 'pending',     '12 SG Highway, Ahmedabad 380015',     188.00);

-- ============================================================
-- 7. ORDER ITEMS (14 rows)
-- ============================================================
INSERT INTO OrderItem (order_id, product_id, quantity, unit_price) VALUES
-- Order 1: Rahul — ProBook + Sonic Buds
(1, 1, 1, 1299.00),
(1, 7, 1,  199.00),
-- Order 2: Priya — Aura X1
(2, 4, 1,  899.00),
-- Order 3: Sneha — Aura Lite + Hub
(3, 5, 1,  349.00),
-- Order 4: Vikram — GameForce + ErgoType
(4, 3, 1, 1799.00),
(4, 16,1,  129.00),
-- Order 5: Rohan — FlexPad Ultra + Mouse
(5, 13,1,  699.00),
(5, 17,1,   59.00),  -- unit_price at time of sale was 29
-- Order 6: Kavya — FlexPad Lite
(6, 14,1,  249.00),
-- Order 7: Amit — WorkStation Pro + Hub
(7, 11,1, 2499.00),
(7, 15,1,   49.00),
-- Order 8: Arjun — BassMax + NexCharge
(8, 8, 1,  149.00),
(8, 18,1,   39.00);

-- ============================================================
-- 8. PAYMENTS (8 rows — one per order)
-- ============================================================
INSERT INTO Payment (order_id, payment_date, method, amount, status) VALUES
(1, '2026-08-01 10:31:00', 'credit_card',  1498.00, 'completed'),
(2, '2026-08-03 14:16:00', 'upi',           899.00, 'completed'),
(3, '2026-08-05 09:46:00', 'debit_card',    348.00, 'completed'),
(4, '2026-08-08 16:21:00', 'net_banking',  1928.00, 'completed'),
(5, '2026-08-10 11:01:00', 'upi',           728.00, 'completed'),
(6, '2026-08-12 13:31:00', 'credit_card',   249.00, 'completed'),
(7, '2026-08-14 08:01:00', 'credit_card',  2548.00, 'pending'),
(8, '2026-08-16 17:46:00', 'cod',           188.00, 'pending');
