<?php
// ============================================================
// products.php — Product API
// GET: returns all products (with optional ?category_id= filter)
// Returns JSON array
// ============================================================
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

    if ($category_id > 0) {
        $stmt = $conn->prepare(
            "SELECT p.product_id, p.name, p.description, p.price, p.stock_qty,
                    p.image_url, p.created_at,
                    c.category_id, c.name AS category_name
             FROM Product p
             JOIN Category c ON p.category_id = c.category_id
             WHERE p.category_id = ?
             ORDER BY p.created_at DESC"
        );
        $stmt->bind_param("i", $category_id);
    } else {
        $stmt = $conn->prepare(
            "SELECT p.product_id, p.name, p.description, p.price, p.stock_qty,
                    p.image_url, p.created_at,
                    c.category_id, c.name AS category_name
             FROM Product p
             JOIN Category c ON p.category_id = c.category_id
             ORDER BY p.created_at DESC"
        );
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    jsonResponse($products);
}

// GET categories list
if (isset($_GET['action']) && $_GET['action'] === 'categories') {
    $result = $conn->query("SELECT * FROM Category ORDER BY name");
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    jsonResponse($categories);
}

$conn->close();
?>
