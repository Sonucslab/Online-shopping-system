<?php
// ============================================================
// php/admin/orders_manage.php — Admin Order Management
// GET: list all orders with customer + payment info
// POST: action=update_status
// ============================================================
require_once '../db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query(
        "SELECT o.*, 
                CONCAT(cu.first_name, ' ', cu.last_name) AS customer_name,
                cu.email AS customer_email,
                p.method AS payment_method,
                p.status AS payment_status
         FROM Orders o
         JOIN Customer cu ON o.customer_id = cu.customer_id
         LEFT JOIN Payment p ON o.order_id = p.order_id
         ORDER BY o.order_date DESC"
    );
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    jsonResponse($orders);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $order_id = intval($_POST['order_id']);
        $status   = $_POST['status'] ?? '';

        $valid = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $valid)) {
            jsonResponse(['error' => 'Invalid status'], 400);
        }

        // If cancelling, restore stock
        if ($status === 'cancelled') {
            $conn->begin_transaction();
            try {
                // Restore stock
                $stmt = $conn->prepare(
                    "UPDATE Product p JOIN OrderItem oi ON p.product_id = oi.product_id
                     SET p.stock_qty = p.stock_qty + oi.quantity
                     WHERE oi.order_id = ?"
                );
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $stmt->close();

                // Update order status
                $stmt = $conn->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
                $stmt->bind_param("si", $status, $order_id);
                $stmt->execute();
                $stmt->close();

                // Update payment status
                $stmt = $conn->prepare("UPDATE Payment SET status = 'refunded' WHERE order_id = ?");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                jsonResponse(['success' => true, 'message' => 'Order cancelled and stock restored']);
            } catch (Exception $e) {
                $conn->rollback();
                jsonResponse(['error' => $e->getMessage()], 500);
            }
        } else {
            $stmt = $conn->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $status, $order_id);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Order status updated']);
        }
    }

    // Get order details with items
    if ($action === 'details') {
        $order_id = intval($_POST['order_id']);
        $stmt = $conn->prepare(
            "SELECT oi.*, p.name AS product_name
             FROM OrderItem oi
             JOIN Product p ON oi.product_id = p.product_id
             WHERE oi.order_id = ?"
        );
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        jsonResponse($items);
    }
}

$conn->close();
?>
