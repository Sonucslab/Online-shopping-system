-- ============================================================
-- NEXUS ELECTRONICS — ORDER TRANSACTION
-- Places an order atomically: insert order → insert items →
-- decrease stock → insert payment → COMMIT or ROLLBACK
-- ============================================================

USE nexus_shop;

-- This is a template showing how the PHP backend will call it.
-- In production, PHP passes the values via prepared statements.

DELIMITER //

-- ============================================================
-- PROCEDURE: place_order
-- Params: customer ID, shipping address, payment method
-- The cart items are read from the Cart/CartItem tables.
-- ============================================================
DROP PROCEDURE IF EXISTS place_order //

CREATE PROCEDURE place_order(
    IN p_customer_id   INT,
    IN p_ship_address  VARCHAR(255),
    IN p_pay_method    ENUM('credit_card','debit_card','upi','net_banking','cod')
)
BEGIN
    DECLARE v_order_id    INT;
    DECLARE v_cart_id     INT;
    DECLARE v_total       DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_item_count  INT DEFAULT 0;

    -- Error handler: rollback on any SQL error
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Order failed — transaction rolled back.';
    END;

    -- ===================== START TRANSACTION =====================
    START TRANSACTION;

    -- 1. Find the customer's active cart
    SELECT cart_id INTO v_cart_id
    FROM Cart
    WHERE customer_id = p_customer_id
    ORDER BY created_at DESC
    LIMIT 1;

    IF v_cart_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No active cart found for this customer.';
    END IF;

    -- 2. Check cart is not empty
    SELECT COUNT(*) INTO v_item_count
    FROM CartItem
    WHERE cart_id = v_cart_id;

    IF v_item_count = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cart is empty — cannot place order.';
    END IF;

    -- 3. Calculate total from cart items × product prices
    SELECT SUM(ci.quantity * p.price) INTO v_total
    FROM CartItem ci
    JOIN Product p ON ci.product_id = p.product_id
    WHERE ci.cart_id = v_cart_id;

    -- 4. Check stock availability for ALL items
    --    (Fail early if any item is out of stock)
    IF EXISTS (
        SELECT 1
        FROM CartItem ci
        JOIN Product p ON ci.product_id = p.product_id
        WHERE ci.cart_id = v_cart_id
          AND p.stock_qty < ci.quantity
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Insufficient stock for one or more items.';
    END IF;

    -- 5. Create the order
    INSERT INTO Orders (customer_id, status, shipping_address, total_amount)
    VALUES (p_customer_id, 'pending', p_ship_address, v_total);

    SET v_order_id = LAST_INSERT_ID();

    -- 6. Copy cart items → order items (snapshot price at time of purchase)
    INSERT INTO OrderItem (order_id, product_id, quantity, unit_price)
    SELECT v_order_id, ci.product_id, ci.quantity, p.price
    FROM CartItem ci
    JOIN Product p ON ci.product_id = p.product_id
    WHERE ci.cart_id = v_cart_id;

    -- 7. Decrease stock for each product
    UPDATE Product p
    JOIN CartItem ci ON p.product_id = ci.product_id
    SET p.stock_qty = p.stock_qty - ci.quantity
    WHERE ci.cart_id = v_cart_id;

    -- 8. Insert payment record
    INSERT INTO Payment (order_id, method, amount, status)
    VALUES (v_order_id, p_pay_method, v_total, 'pending');

    -- 9. Clear the cart (items + cart row)
    DELETE FROM CartItem WHERE cart_id = v_cart_id;
    DELETE FROM Cart WHERE cart_id = v_cart_id;

    -- ===================== COMMIT =====================
    COMMIT;

    -- Return the new order ID
    SELECT v_order_id AS new_order_id, v_total AS order_total;
END //

DELIMITER ;


-- ============================================================
-- EXAMPLE USAGE:
-- CALL place_order(2, '12 MG Road, Bangalore 560001', 'upi');
-- ============================================================
