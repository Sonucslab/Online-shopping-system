<?php
// ============================================================
// cart.php — Cart Operations API
// GET: fetch cart items for logged-in user
// POST: action=add/remove/update/clear
// ============================================================
require_once 'db.php';

// GET — fetch cart
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['items' => [], 'message' => 'Not logged in — using local cart']);
    }

    $customer_id = $_SESSION['user_id'];

    // Get or create cart
    $stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart = $result->fetch_assoc();
    $stmt->close();

    if (!$cart) {
        jsonResponse(['items' => []]);
    }

    // Fetch cart items with product details
    $stmt = $conn->prepare(
        "SELECT ci.cartitem_id, ci.quantity,
                p.product_id, p.name, p.price, p.stock_qty, p.image_url,
                c.name AS category_name
         FROM CartItem ci
         JOIN Product p ON ci.product_id = p.product_id
         JOIN Category c ON p.category_id = c.category_id
         WHERE ci.cart_id = ?"
    );
    $stmt->bind_param("i", $cart['cart_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();

    jsonResponse(['items' => $items]);
}

// POST — modify cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Please login to manage cart'], 401);
    }

    $customer_id = $_SESSION['user_id'];
    $action      = $_POST['action'] ?? '';
    $product_id  = intval($_POST['product_id'] ?? 0);
    $quantity    = intval($_POST['quantity'] ?? 1);

    // Get or create cart
    $stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart = $result->fetch_assoc();
    $stmt->close();

    if (!$cart) {
        $stmt = $conn->prepare("INSERT INTO Cart (customer_id) VALUES (?)");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $cart_id = $conn->insert_id;
        $stmt->close();
    } else {
        $cart_id = $cart['cart_id'];
    }

    switch ($action) {
        case 'add':
            // Check if product already in cart
            $stmt = $conn->prepare("SELECT cartitem_id, quantity FROM CartItem WHERE cart_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $cart_id, $product_id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existing) {
                $new_qty = $existing['quantity'] + $quantity;
                $stmt = $conn->prepare("UPDATE CartItem SET quantity = ? WHERE cartitem_id = ?");
                $stmt->bind_param("ii", $new_qty, $existing['cartitem_id']);
            } else {
                $stmt = $conn->prepare("INSERT INTO CartItem (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
            }
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Item added to cart']);
            break;

        case 'update':
            if ($quantity < 1) {
                // Remove item
                $stmt = $conn->prepare("DELETE FROM CartItem WHERE cart_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $cart_id, $product_id);
            } else {
                $stmt = $conn->prepare("UPDATE CartItem SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                $stmt->bind_param("iii", $quantity, $cart_id, $product_id);
            }
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Cart updated']);
            break;

        case 'remove':
            $stmt = $conn->prepare("DELETE FROM CartItem WHERE cart_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $cart_id, $product_id);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Item removed from cart']);
            break;

        case 'clear':
            $stmt = $conn->prepare("DELETE FROM CartItem WHERE cart_id = ?");
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Cart cleared']);
            break;

        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
}

$conn->close();
?>
