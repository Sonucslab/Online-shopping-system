-- ============================================================
-- NEXUS ELECTRONICS — ONLINE SHOPPING SYSTEM
-- Database Schema (DDL) — MySQL
-- 8 Tables | 3NF | Referential Integrity
-- ============================================================

-- Drop existing tables (in reverse dependency order)
DROP DATABASE IF EXISTS nexus_shop;
CREATE DATABASE nexus_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE nexus_shop;

-- ============================================================
-- TABLE 1: Customer
-- Stores registered users (both Admin and Customer roles)
-- ============================================================
CREATE TABLE Customer (
    customer_id   INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(50)  NOT NULL,
    last_name     VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(20),
    address       VARCHAR(255),
    city          VARCHAR(50),
    zip_code      VARCHAR(10),
    role          ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE 2: Category
-- Product categories (normalized — no data duplication)
-- ============================================================
CREATE TABLE Category (
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL UNIQUE,
    description   VARCHAR(255)
);

-- ============================================================
-- TABLE 3: Product
-- Products belong to one Category (1:N)
-- ============================================================
CREATE TABLE Product (
    product_id    INT AUTO_INCREMENT PRIMARY KEY,
    category_id   INT NOT NULL,
    name          VARCHAR(150) NOT NULL,
    description   TEXT,
    price         DECIMAL(10,2) NOT NULL CHECK (price > 0),
    stock_qty     INT NOT NULL DEFAULT 0 CHECK (stock_qty >= 0),
    image_url     VARCHAR(255),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES Category(category_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ============================================================
-- TABLE 4: Cart
-- One active cart per customer (1:1 at any time)
-- ============================================================
CREATE TABLE Cart (
    cart_id       INT AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- ============================================================
-- TABLE 5: CartItem  (Weak Entity — depends on Cart)
-- Many-to-many between Cart and Product
-- ============================================================
CREATE TABLE CartItem (
    cartitem_id   INT AUTO_INCREMENT PRIMARY KEY,
    cart_id       INT NOT NULL,
    product_id    INT NOT NULL,
    quantity      INT NOT NULL DEFAULT 1 CHECK (quantity > 0),

    FOREIGN KEY (cart_id)    REFERENCES Cart(cart_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    UNIQUE KEY unique_cart_product (cart_id, product_id)
);

-- ============================================================
-- TABLE 6: Orders
-- One customer can have many orders (1:N)
-- ============================================================
CREATE TABLE Orders (
    order_id         INT AUTO_INCREMENT PRIMARY KEY,
    customer_id      INT NOT NULL,
    order_date       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status           ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled')
                     NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(255) NOT NULL,
    total_amount     DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ============================================================
-- TABLE 7: OrderItem
-- Resolves M:N between Orders and Product
-- Stores quantity and unit_price at time of purchase (snapshot)
-- ============================================================
CREATE TABLE OrderItem (
    orderitem_id  INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    product_id    INT NOT NULL,
    quantity      INT NOT NULL CHECK (quantity > 0),
    unit_price    DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id)   REFERENCES Orders(order_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ============================================================
-- TABLE 8: Payment
-- One payment per order (1:1)
-- ============================================================
CREATE TABLE Payment (
    payment_id    INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL UNIQUE,
    payment_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    method        ENUM('credit_card', 'debit_card', 'upi', 'net_banking', 'cod')
                  NOT NULL,
    amount        DECIMAL(10,2) NOT NULL,
    status        ENUM('pending', 'completed', 'failed', 'refunded')
                  NOT NULL DEFAULT 'pending',

    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- ============================================================
-- INDEXES for performance
-- ============================================================
CREATE INDEX idx_product_category ON Product(category_id);
CREATE INDEX idx_order_customer   ON Orders(customer_id);
CREATE INDEX idx_orderitem_order  ON OrderItem(order_id);
CREATE INDEX idx_cart_customer    ON Cart(customer_id);
