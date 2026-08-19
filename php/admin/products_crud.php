<?php
// ============================================================
// php/admin/products_crud.php — Admin Product CRUD
// GET:  list all products
// POST: action=add/edit/delete
// ============================================================
require_once '../db.php';
requireAdmin();

// GET — List all products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query(
        "SELECT p.*, c.name AS category_name
         FROM Product p
         JOIN Category c ON p.category_id = c.category_id
         ORDER BY p.product_id DESC"
    );
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    jsonResponse($products);
}

// POST — Add / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $stmt = $conn->prepare(
                "INSERT INTO Product (category_id, name, description, price, stock_qty, image_url)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $cat_id  = intval($_POST['category_id']);
            $name    = trim($_POST['name']);
            $desc    = trim($_POST['description'] ?? '');
            $price   = floatval($_POST['price']);
            $stock   = intval($_POST['stock_qty']);
            $img     = trim($_POST['image_url'] ?? '');
            $stmt->bind_param("issdis", $cat_id, $name, $desc, $price, $stock, $img);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Product added', 'id' => $conn->insert_id]);
            break;

        case 'edit':
            $stmt = $conn->prepare(
                "UPDATE Product SET category_id=?, name=?, description=?, price=?, stock_qty=?, image_url=?
                 WHERE product_id=?"
            );
            $pid     = intval($_POST['product_id']);
            $cat_id  = intval($_POST['category_id']);
            $name    = trim($_POST['name']);
            $desc    = trim($_POST['description'] ?? '');
            $price   = floatval($_POST['price']);
            $stock   = intval($_POST['stock_qty']);
            $img     = trim($_POST['image_url'] ?? '');
            $stmt->bind_param("issdisi", $cat_id, $name, $desc, $price, $stock, $img, $pid);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Product updated']);
            break;

        case 'delete':
            $pid = intval($_POST['product_id']);
            // Check if product has any order items
            $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM OrderItem WHERE product_id = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();

            if ($cnt > 0) {
                jsonResponse(['error' => 'Cannot delete — product has existing orders'], 400);
            }

            $stmt = $conn->prepare("DELETE FROM Product WHERE product_id = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Product deleted']);
            break;

        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
}

$conn->close();
?>
