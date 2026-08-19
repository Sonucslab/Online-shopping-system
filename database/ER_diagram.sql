-- ============================================================
-- NEXUS ELECTRONICS — ER DIAGRAM DOCUMENTATION
-- ============================================================
-- This file describes the Entity-Relationship diagram for the
-- Online Shopping (E-commerce) System.
--
-- NOTE: For the visual ER diagram, use dbdiagram.io or draw.io
--       and import the structure below.
-- ============================================================


-- ============================================================
-- ENTITIES & ATTRIBUTES
-- ============================================================

-- ENTITY 1: Customer
-- ---------------------------------------------------------
-- PK: customer_id (INT, Auto Increment)
-- Attributes:
--   first_name       (VARCHAR)      — Simple
--   last_name        (VARCHAR)      — Simple
--   email            (VARCHAR)      — Simple, UNIQUE
--   password_hash    (VARCHAR)      — Simple
--   phone            (VARCHAR)      — Simple
--   address          (VARCHAR)      — Composite (street + city + zip)
--   city             (VARCHAR)      — Part of composite 'address'
--   zip_code         (VARCHAR)      — Part of composite 'address'
--   role             (ENUM)         — Simple {customer, admin}
--   created_at       (TIMESTAMP)    — Derived (auto-generated)

-- ENTITY 2: Category
-- ---------------------------------------------------------
-- PK: category_id (INT, Auto Increment)
-- Attributes:
--   name             (VARCHAR)      — Simple, UNIQUE
--   description      (VARCHAR)      — Simple

-- ENTITY 3: Product
-- ---------------------------------------------------------
-- PK: product_id (INT, Auto Increment)
-- FK: category_id → Category
-- Attributes:
--   name             (VARCHAR)      — Simple
--   description      (TEXT)         — Simple
--   price            (DECIMAL)      — Simple
--   stock_qty        (INT)          — Simple
--   image_url        (VARCHAR)      — Simple
--   created_at       (TIMESTAMP)    — Derived (auto-generated)

-- ENTITY 4: Cart
-- ---------------------------------------------------------
-- PK: cart_id (INT, Auto Increment)
-- FK: customer_id → Customer
-- Attributes:
--   created_at       (TIMESTAMP)    — Derived

-- ENTITY 5: CartItem  *** WEAK ENTITY ***
-- ---------------------------------------------------------
-- Depends on: Cart (identifying relationship)
-- PK: cartitem_id (INT, Auto Increment)
-- Partial Key: (cart_id, product_id) — UNIQUE composite
-- FK: cart_id → Cart
-- FK: product_id → Product
-- Attributes:
--   quantity          (INT)         — Simple

-- ENTITY 6: Orders
-- ---------------------------------------------------------
-- PK: order_id (INT, Auto Increment)
-- FK: customer_id → Customer
-- Attributes:
--   order_date        (TIMESTAMP)  — Derived
--   status            (ENUM)       — Simple {pending, processing, shipped, delivered, cancelled}
--   shipping_address  (VARCHAR)    — Composite
--   total_amount      (DECIMAL)    — Derived (SUM of OrderItem.qty × unit_price)

-- ENTITY 7: OrderItem
-- ---------------------------------------------------------
-- PK: orderitem_id (INT, Auto Increment)
-- FK: order_id → Orders
-- FK: product_id → Product
-- Attributes:
--   quantity          (INT)        — Simple
--   unit_price        (DECIMAL)    — Simple (snapshot at time of purchase)

-- ENTITY 8: Payment
-- ---------------------------------------------------------
-- PK: payment_id (INT, Auto Increment)
-- FK: order_id → Orders (UNIQUE — 1:1)
-- Attributes:
--   payment_date      (TIMESTAMP)  — Derived
--   method            (ENUM)       — Simple {credit_card, debit_card, upi, net_banking, cod}
--   amount            (DECIMAL)    — Simple
--   status            (ENUM)       — Simple {pending, completed, failed, refunded}


-- ============================================================
-- RELATIONSHIPS & CARDINALITY
-- ============================================================

-- 1. Customer —— places ——> Orders
--    Cardinality:  1 : N  (One customer places many orders)
--    Participation: Customer — Partial | Orders — Total
--    FK: Orders.customer_id → Customer.customer_id

-- 2. Customer —— has ——> Cart
--    Cardinality:  1 : N  (One customer can have carts over time)
--    Participation: Customer — Partial | Cart — Total
--    FK: Cart.customer_id → Customer.customer_id

-- 3. Category —— contains ——> Product
--    Cardinality:  1 : N  (One category contains many products)
--    Participation: Category — Partial | Product — Total
--    FK: Product.category_id → Category.category_id

-- 4. Cart —— has ——> CartItem  (Identifying relationship for weak entity)
--    Cardinality:  1 : N  (One cart has many items)
--    Participation: Cart — Partial | CartItem — Total (weak entity)
--    FK: CartItem.cart_id → Cart.cart_id

-- 5. Product —— appears in ——> CartItem
--    Cardinality:  1 : N
--    Participation: Product — Partial | CartItem — Total
--    FK: CartItem.product_id → Product.product_id

-- 6. Orders —— contains ——> OrderItem
--    Cardinality:  1 : N  (One order has many order items)
--    Participation: Orders — Total | OrderItem — Total
--    FK: OrderItem.order_id → Orders.order_id
--    NOTE: This resolves the M:N between Orders and Product

-- 7. Product —— sold via ——> OrderItem
--    Cardinality:  1 : N
--    Participation: Product — Partial | OrderItem — Total
--    FK: OrderItem.product_id → Product.product_id

-- 8. Orders —— paid by ——> Payment
--    Cardinality:  1 : 1  (One payment per order)
--    Participation: Orders — Total | Payment — Total
--    FK: Payment.order_id → Orders.order_id (UNIQUE)


-- ============================================================
-- WEAK ENTITY EXPLANATION
-- ============================================================
-- CartItem is a WEAK ENTITY because:
-- 1. It cannot exist without its owning Cart (existence dependency)
-- 2. Its partial key (product_id) is only meaningful within a specific cart
-- 3. It is identified by the combination of cart_id (from owner) + product_id
-- 4. If the Cart is deleted, all CartItems are CASCADE deleted
--
-- The identifying relationship is: Cart ——HAS——> CartItem
-- Notation: Cart is drawn with a single rectangle, CartItem with double rectangle
--           The "HAS" relationship is drawn with a double diamond


-- ============================================================
-- SPECIAL ATTRIBUTES
-- ============================================================
-- Composite Attributes:
--   Customer.address = (address, city, zip_code) — stored as separate columns
--
-- Derived Attributes:
--   Customer.created_at — auto-generated by TIMESTAMP DEFAULT
--   Orders.total_amount — derived from SUM(OrderItem.quantity × OrderItem.unit_price)
--   Orders.order_date   — auto-generated
--   Payment.payment_date — auto-generated
--
-- Multivalued Attributes:
--   (None in this schema — all multivalued cases are resolved
--    into separate tables: CartItem, OrderItem)
--
-- Key Attributes:
--   All PKs are underlined: customer_id, category_id, product_id,
--   cart_id, cartitem_id, order_id, orderitem_id, payment_id


-- ============================================================
-- DBDIAGRAM.IO CODE (paste at dbdiagram.io to render)
-- ============================================================
-- Table Customer {
--   customer_id int [pk, increment]
--   first_name varchar
--   last_name varchar
--   email varchar [unique]
--   password_hash varchar
--   phone varchar
--   address varchar
--   city varchar
--   zip_code varchar
--   role enum
--   created_at timestamp
-- }
-- Table Category {
--   category_id int [pk, increment]
--   name varchar [unique]
--   description varchar
-- }
-- Table Product {
--   product_id int [pk, increment]
--   category_id int [ref: > Category.category_id]
--   name varchar
--   description text
--   price decimal
--   stock_qty int
--   image_url varchar
--   created_at timestamp
-- }
-- Table Cart {
--   cart_id int [pk, increment]
--   customer_id int [ref: > Customer.customer_id]
--   created_at timestamp
-- }
-- Table CartItem {
--   cartitem_id int [pk, increment]
--   cart_id int [ref: > Cart.cart_id]
--   product_id int [ref: > Product.product_id]
--   quantity int
-- }
-- Table Orders {
--   order_id int [pk, increment]
--   customer_id int [ref: > Customer.customer_id]
--   order_date timestamp
--   status enum
--   shipping_address varchar
--   total_amount decimal
-- }
-- Table OrderItem {
--   orderitem_id int [pk, increment]
--   order_id int [ref: > Orders.order_id]
--   product_id int [ref: > Product.product_id]
--   quantity int
--   unit_price decimal
-- }
-- Table Payment {
--   payment_id int [pk, increment]
--   order_id int [ref: - Orders.order_id]
--   payment_date timestamp
--   method enum
--   amount decimal
--   status enum
-- }
