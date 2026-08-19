<?php
// ============================================================
// order.php — Place Order (Transaction)
// POST: shipping_address, payment_method, cart (JSON string)
// Uses a transaction: BEGIN → Order → OrderItems → Stock → Payment → COMMIT
// ============================================================
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../checkout.html');
    exit;
}

// Accept both session-based login and guest checkout with POST data
$customer_id     = $_SESSION['user_id'] ?? null;
$shipping_address = trim($_POST['shipping_address'] ?? '');
$payment_method   = $_POST['payment_method'] ?? 'credit_card';
$cart_json        = $_POST['cart'] ?? '[]';

if (empty($shipping_address)) {
    jsonResponse(['error' => 'Shipping address is required'], 400);
}

$cart_items = json_decode($cart_json, true);
if (empty($cart_items)) {
    jsonResponse(['error' => 'Cart is empty'], 400);
}

// Validate payment method
$valid_methods = ['credit_card', 'debit_card', 'upi', 'net_banking', 'cod'];
if (!in_array($payment_method, $valid_methods)) {
    $payment_method = 'credit_card';
}

// ===================== START TRANSACTION =====================
$conn->begin_transaction();

try {
    // 1. Calculate total & validate stock
    $total = 0;
    $order_items = [];

    foreach ($cart_items as $item) {
        $pid = intval($item['id']);
        $qty = intval($item['quantity']);

        if ($qty < 1) continue;

        // Fetch current product price & stock (with row lock)
        $stmt = $conn->prepare("SELECT product_id, name, price, stock_qty FROM Product WHERE product_id = ? FOR UPDATE");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception("Product ID $pid not found.");
        }
        if ($product['stock_qty'] < $qty) {
            throw new Exception("Insufficient stock for '{$product['name']}'. Available: {$product['stock_qty']}");
        }

        $unit_price = $product['price'];
        $total += $unit_price * $qty;
        $order_items[] = [
            'product_id' => $pid,
            'quantity'   => $qty,
            'unit_price' => $unit_price
        ];
    }

    if (empty($order_items)) {
        throw new Exception("No valid items in cart.");
    }

    // 2. Create Order
    // If guest (not logged in), use customer_id = NULL or create a guest record
    if (!$customer_id) {
        // For guest checkout, require name and create a guest customer
        $guest_fname = trim($_POST['first_name'] ?? 'Guest');
        $guest_lname = trim($_POST['last_name'] ?? 'User');
        $guest_email = trim($_POST['email'] ?? 'guest_' . time() . '@nexus.com');

        $stmt = $conn->prepare(
            "INSERT INTO Customer (first_name, last_name, email, password_hash, address, role)
             VALUES (?, ?, ?, '', ?, 'customer')"
        );
        $stmt->bind_param("ssss", $guest_fname, $guest_lname, $guest_email, $shipping_address);
        $stmt->execute();
        $customer_id = $conn->insert_id;
        $stmt->close();
    }

    $stmt = $conn->prepare(
        "INSERT INTO Orders (customer_id, status, shipping_address, total_amount)
         VALUES (?, 'pending', ?, ?)"
    );
    $stmt->bind_param("isd", $customer_id, $shipping_address, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    // 3. Insert OrderItems
    $stmt = $conn->prepare(
        "INSERT INTO OrderItem (order_id, product_id, quantity, unit_price)
         VALUES (?, ?, ?, ?)"
    );
    foreach ($order_items as $oi) {
        $stmt->bind_param("iiid", $order_id, $oi['product_id'], $oi['quantity'], $oi['unit_price']);
        $stmt->execute();
    }
    $stmt->close();

    // 4. Decrease stock
    $stmt = $conn->prepare("UPDATE Product SET stock_qty = stock_qty - ? WHERE product_id = ?");
    foreach ($order_items as $oi) {
        $stmt->bind_param("ii", $oi['quantity'], $oi['product_id']);
        $stmt->execute();
    }
    $stmt->close();

    // 5. Insert Payment
    $stmt = $conn->prepare(
        "INSERT INTO Payment (order_id, method, amount, status) VALUES (?, ?, ?, 'pending')"
    );
    $stmt->bind_param("isd", $order_id, $payment_method, $total);
    $stmt->execute();
    $stmt->close();

    // ===================== COMMIT =====================
    $conn->commit();

    // Return success
    jsonResponse([
        'success'  => true,
        'order_id' => $order_id,
        'total'    => $total,
        'message'  => 'Order placed successfully!'
    ]);

} catch (Exception $e) {
    // ===================== ROLLBACK =====================
    $conn->rollback();
    jsonResponse(['error' => $e->getMessage()], 400);
}

$conn->close();
?>
