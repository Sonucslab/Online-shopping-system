<?php
// ============================================================
// php/admin/categories_crud.php — Admin Category CRUD
// ============================================================
require_once '../db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT c.*, COUNT(p.product_id) AS product_count
                            FROM Category c
                            LEFT JOIN Product p ON c.category_id = p.category_id
                            GROUP BY c.category_id
                            ORDER BY c.name");
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    jsonResponse($categories);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = trim($_POST['name']);
            $desc = trim($_POST['description'] ?? '');
            $stmt = $conn->prepare("INSERT INTO Category (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $desc);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Category added', 'id' => $conn->insert_id]);
            break;

        case 'edit':
            $id   = intval($_POST['category_id']);
            $name = trim($_POST['name']);
            $desc = trim($_POST['description'] ?? '');
            $stmt = $conn->prepare("UPDATE Category SET name=?, description=? WHERE category_id=?");
            $stmt->bind_param("ssi", $name, $desc, $id);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Category updated']);
            break;

        case 'delete':
            $id = intval($_POST['category_id']);
            // Check if category has products
            $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM Product WHERE category_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();

            if ($cnt > 0) {
                jsonResponse(['error' => 'Cannot delete — category has ' . $cnt . ' products'], 400);
            }

            $stmt = $conn->prepare("DELETE FROM Category WHERE category_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            jsonResponse(['success' => true, 'message' => 'Category deleted']);
            break;

        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
}

$conn->close();
?>
