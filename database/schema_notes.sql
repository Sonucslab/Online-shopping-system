-- ============================================================
-- NEXUS ELECTRONICS — RELATIONAL SCHEMA & 3NF DOCUMENTATION
-- ============================================================


-- ============================================================
-- RELATIONAL SCHEMA
-- (PKs underlined with *, FKs marked with →)
-- ============================================================

-- Customer (*customer_id*, first_name, last_name, email, password_hash,
--           phone, address, city, zip_code, role, created_at)

-- Category (*category_id*, name, description)

-- Product  (*product_id*, category_id→Category, name, description,
--           price, stock_qty, image_url, created_at)

-- Cart     (*cart_id*, customer_id→Customer, created_at)

-- CartItem (*cartitem_id*, cart_id→Cart, product_id→Product, quantity)
--          UNIQUE(cart_id, product_id)

-- Orders   (*order_id*, customer_id→Customer, order_date, status,
--           shipping_address, total_amount)

-- OrderItem(*orderitem_id*, order_id→Orders, product_id→Product,
--           quantity, unit_price)

-- Payment  (*payment_id*, order_id→Orders(UNIQUE), payment_date,
--           method, amount, status)


-- ============================================================
-- FUNCTIONAL DEPENDENCIES
-- ============================================================

-- Customer Table:
-- FD1: customer_id → first_name, last_name, email, password_hash,
--                     phone, address, city, zip_code, role, created_at
-- FD2: email → customer_id (candidate key)

-- Category Table:
-- FD3: category_id → name, description
-- FD4: name → category_id (candidate key)

-- Product Table:
-- FD5: product_id → category_id, name, description, price, stock_qty,
--                    image_url, created_at

-- Cart Table:
-- FD6: cart_id → customer_id, created_at

-- CartItem Table:
-- FD7: cartitem_id → cart_id, product_id, quantity
-- FD8: (cart_id, product_id) → quantity (composite candidate key)

-- Orders Table:
-- FD9: order_id → customer_id, order_date, status, shipping_address,
--                  total_amount

-- OrderItem Table:
-- FD10: orderitem_id → order_id, product_id, quantity, unit_price

-- Payment Table:
-- FD11: payment_id → order_id, payment_date, method, amount, status
-- FD12: order_id → payment_id (1:1 relationship)


-- ============================================================
-- NORMALIZATION PROOF (to Third Normal Form — 3NF)
-- ============================================================

-- FIRST NORMAL FORM (1NF):
-- ✓ All tables have a primary key
-- ✓ All attributes contain atomic (indivisible) values
-- ✓ No repeating groups or arrays
-- ✓ Each column contains values of a single type
--
-- Evidence: address is decomposed into (address, city, zip_code)
-- rather than being stored as a single composite string.

-- SECOND NORMAL FORM (2NF):
-- ✓ Already in 1NF
-- ✓ No partial dependencies (no non-key attribute depends on only
--    PART of a composite key)
--
-- Evidence: All tables use single-column surrogate primary keys
-- (auto_increment INT). The only composite candidate key is
-- (cart_id, product_id) in CartItem, and 'quantity' depends on
-- the FULL combination, not on cart_id or product_id alone.

-- THIRD NORMAL FORM (3NF):
-- ✓ Already in 2NF
-- ✓ No transitive dependencies (no non-key attribute depends on
--    another non-key attribute)
--
-- Analysis per table:
--
-- Customer: customer_id → all attributes directly. No attribute
--           depends on another non-key attribute. city and zip_code
--           are independent attributes, not derived from each other.
--
-- Category: category_id → name, description. Both depend only on PK.
--
-- Product:  product_id → all attributes. category_id is a FK, not a
--           transitive dependency (category details live in Category table,
--           not duplicated here).
--
-- Cart:     cart_id → customer_id, created_at. No transitive deps.
--
-- CartItem: cartitem_id → cart_id, product_id, quantity.
--           No non-key → non-key dependency.
--
-- Orders:   order_id → all attributes. total_amount is stored as a
--           computed value at transaction time (not re-derived from
--           OrderItem at query time), which is an intentional
--           denormalization for performance, but acceptable for a
--           "snapshot" value.
--
-- OrderItem: orderitem_id → all attributes. unit_price is a snapshot
--            of Product.price at time of purchase (not a live lookup),
--            so it's an independent attribute of OrderItem, not a
--            transitive dependency.
--
-- Payment:  payment_id → all attributes. No transitive deps.


-- ============================================================
-- ER-TO-RELATIONAL MAPPING RULES APPLIED
-- ============================================================

-- Rule 1: Strong Entity → Table
--   Applied to: Customer, Category, Product, Cart, Orders, Payment

-- Rule 2: Weak Entity → Table with FK to owner
--   Applied to: CartItem (owner = Cart, identifying relationship)
--   CartItem includes cart_id as FK with CASCADE delete

-- Rule 3: 1:N Relationship → FK in the "many" side
--   Applied to:
--   - Product has FK category_id → Category (Category 1:N Product)
--   - Cart has FK customer_id → Customer (Customer 1:N Cart)
--   - Orders has FK customer_id → Customer (Customer 1:N Orders)
--   - CartItem has FK cart_id → Cart (Cart 1:N CartItem)
--   - CartItem has FK product_id → Product (Product 1:N CartItem)
--   - OrderItem has FK order_id → Orders (Orders 1:N OrderItem)
--   - OrderItem has FK product_id → Product (Product 1:N OrderItem)

-- Rule 4: 1:1 Relationship → FK with UNIQUE constraint
--   Applied to: Payment has FK order_id → Orders (UNIQUE)

-- Rule 5: M:N Relationship → Junction/Bridge table
--   Applied to:
--   - Orders ←→ Product resolved by OrderItem (stores qty + unit_price)
--   - Cart ←→ Product resolved by CartItem (stores quantity)
