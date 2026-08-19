-- ============================================================
-- NEXUS ELECTRONICS — SQL QUERIES
-- DDL (already in schema.sql), DML, and 5 Academic Queries
-- ============================================================

USE nexus_shop;

-- ============================================================
-- DML EXAMPLES — INSERT / UPDATE / DELETE
-- ============================================================

-- Insert a new product
INSERT INTO Product (category_id, name, description, price, stock_qty, image_url)
VALUES (3, 'BassPods X3', 'True Wireless Earbuds with ANC, 40hr Battery', 99.00, 50, 'images/products/basspods-x3.jpg');

-- Update product price
UPDATE Product SET price = 89.00 WHERE name = 'BassPods X3';

-- Update order status
UPDATE Orders SET status = 'shipped' WHERE order_id = 5;

-- Delete a cart item
DELETE FROM CartItem WHERE cartitem_id = 5;

-- Update stock after a return
UPDATE Product SET stock_qty = stock_qty + 1 WHERE product_id = 4;


-- ============================================================
-- QUERY 1: Total Revenue Per Category
-- Uses: JOIN (3 tables), GROUP BY, SUM, ORDER BY
-- Purpose: Shows which product category generates the most revenue
-- ============================================================
SELECT
    c.name AS category_name,
    COUNT(DISTINCT o.order_id) AS total_orders,
    SUM(oi.quantity) AS units_sold,
    SUM(oi.quantity * oi.unit_price) AS total_revenue
FROM Category c
JOIN Product p ON c.category_id = p.category_id
JOIN OrderItem oi ON p.product_id = oi.product_id
JOIN Orders o ON oi.order_id = o.order_id
WHERE o.status != 'cancelled'
GROUP BY c.category_id, c.name
ORDER BY total_revenue DESC;


-- ============================================================
-- QUERY 2: Top 5 Customers by Total Order Value
-- Uses: JOIN, GROUP BY, SUM, ORDER BY, LIMIT
-- Purpose: Identify the highest-spending customers
-- ============================================================
SELECT
    CONCAT(cu.first_name, ' ', cu.last_name) AS customer_name,
    cu.email,
    COUNT(o.order_id) AS order_count,
    SUM(o.total_amount) AS total_spent
FROM Customer cu
JOIN Orders o ON cu.customer_id = o.customer_id
WHERE o.status != 'cancelled'
GROUP BY cu.customer_id, cu.first_name, cu.last_name, cu.email
ORDER BY total_spent DESC
LIMIT 5;


-- ============================================================
-- QUERY 3: Products Low on Stock (< 15 units)
-- Uses: JOIN, WHERE, ORDER BY
-- Purpose: Inventory alert — products that need restocking
-- ============================================================
SELECT
    p.product_id,
    p.name AS product_name,
    c.name AS category,
    p.stock_qty,
    p.price
FROM Product p
JOIN Category c ON p.category_id = c.category_id
WHERE p.stock_qty < 15
ORDER BY p.stock_qty ASC;


-- ============================================================
-- QUERY 4: Monthly Sales Report for 2026
-- Uses: DATE functions, GROUP BY MONTH, SUM, COUNT
-- Purpose: Shows sales trend month-by-month
-- ============================================================
SELECT
    MONTH(o.order_date) AS month_number,
    MONTHNAME(o.order_date) AS month_name,
    COUNT(o.order_id) AS total_orders,
    SUM(o.total_amount) AS monthly_revenue,
    ROUND(AVG(o.total_amount), 2) AS avg_order_value
FROM Orders o
WHERE YEAR(o.order_date) = 2026
  AND o.status != 'cancelled'
GROUP BY MONTH(o.order_date), MONTHNAME(o.order_date)
ORDER BY month_number;


-- ============================================================
-- QUERY 5: Average Order Value Per Payment Method
-- Uses: JOIN (2 tables), GROUP BY, AVG, COUNT
-- Purpose: Analyze which payment methods are used for
--          higher/lower value transactions
-- ============================================================
SELECT
    p.method AS payment_method,
    COUNT(p.payment_id) AS transaction_count,
    ROUND(AVG(p.amount), 2) AS avg_order_value,
    SUM(p.amount) AS total_collected,
    MIN(p.amount) AS min_transaction,
    MAX(p.amount) AS max_transaction
FROM Payment p
JOIN Orders o ON p.order_id = o.order_id
WHERE p.status IN ('completed', 'pending')
GROUP BY p.method
ORDER BY avg_order_value DESC;
