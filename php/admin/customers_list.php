<?php
// ============================================================
// php/admin/customers_list.php — Admin Customer List
// GET: list all customers with order stats
// POST: action=delete
// ============================================================
require_once '../db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query(
        "SELECT cu.*,
                COUNT(o.order_id) AS order_count,
                COALESCE(SUM(o.total_amount), 0) AS total_spent
         FROM Customer cu
         LEFT JOIN Orders o ON cu.customer_id = o.customer_id AND o.status != 'cancelled'
         WHERE cu.role = 'customer'
         GROUP BY cu.customer_id
         ORDER BY cu.created_at DESC"
    );
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        unset($row['password_hash']); // Don't expose password
        $customers[] = $row;
    }
    jsonResponse($customers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = intval($_POST['customer_id']);

    // Check if customer has orders
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM Orders WHERE customer_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    if ($cnt > 0) {
        jsonResponse(['error' => 'Cannot delete — customer has existing orders'], 400);
    }

    $stmt = $conn->prepare("DELETE FROM Customer WHERE customer_id = ? AND role != 'admin'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    jsonResponse(['success' => true, 'message' => 'Customer deleted']);
}

$conn->close();
?>
