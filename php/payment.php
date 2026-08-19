<?php
// ============================================================
// payment.php — Payment Status API
// POST: order_id, status (completed/failed/refunded)
// GET: ?order_id= → fetch payment details
// ============================================================
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $stmt = $conn->prepare(
        "SELECT p.*, o.status AS order_status, o.total_amount
         FROM Payment p
         JOIN Orders o ON p.order_id = o.order_id
         WHERE p.order_id = ?"
    );
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($payment) {
        jsonResponse($payment);
    } else {
        jsonResponse(['error' => 'Payment not found'], 404);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();

    $order_id = intval($_POST['order_id'] ?? 0);
    $status   = $_POST['status'] ?? '';

    $valid_statuses = ['completed', 'failed', 'refunded'];
    if (!in_array($status, $valid_statuses)) {
        jsonResponse(['error' => 'Invalid payment status'], 400);
    }

    // Update payment status
    $stmt = $conn->prepare("UPDATE Payment SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();

    // If completed, update order status to processing
    if ($status === 'completed') {
        $stmt = $conn->prepare("UPDATE Orders SET status = 'processing' WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
    }

    // If failed, restore stock and cancel order
    if ($status === 'failed') {
        // Restore stock
        $stmt = $conn->prepare(
            "UPDATE Product p JOIN OrderItem oi ON p.product_id = oi.product_id
             SET p.stock_qty = p.stock_qty + oi.quantity
             WHERE oi.order_id = ?"
        );
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();

        // Cancel order
        $stmt = $conn->prepare("UPDATE Orders SET status = 'cancelled' WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
    }

    jsonResponse(['success' => true, 'message' => 'Payment updated']);
}

$conn->close();
?>
