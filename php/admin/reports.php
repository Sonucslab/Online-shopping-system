<?php
// ============================================================
// php/admin/reports.php — Sales Report (Aggregate Queries)
// GET: returns sales summary data for the admin report page
// ============================================================
require_once '../db.php';
requireAdmin();

$report = [];

// 1. Dashboard Stats
$stats = $conn->query("SELECT
    (SELECT COUNT(*) FROM Orders WHERE status != 'cancelled') AS total_orders,
    (SELECT COUNT(*) FROM Customer WHERE role = 'customer') AS total_customers,
    (SELECT COUNT(*) FROM Product) AS total_products,
    (SELECT COALESCE(SUM(total_amount), 0) FROM Orders WHERE status != 'cancelled') AS total_revenue
")->fetch_assoc();
$report['stats'] = $stats;

// 2. Revenue by Category
$result = $conn->query(
    "SELECT c.name AS category_name,
            SUM(oi.quantity) AS units_sold,
            SUM(oi.quantity * oi.unit_price) AS revenue
     FROM Category c
     JOIN Product p ON c.category_id = p.category_id
     JOIN OrderItem oi ON p.product_id = oi.product_id
     JOIN Orders o ON oi.order_id = o.order_id
     WHERE o.status != 'cancelled'
     GROUP BY c.category_id, c.name
     ORDER BY revenue DESC"
);
$report['revenue_by_category'] = [];
while ($row = $result->fetch_assoc()) {
    $report['revenue_by_category'][] = $row;
}

// 3. Monthly Sales (last 6 months)
$result = $conn->query(
    "SELECT MONTH(order_date) AS month_num,
            MONTHNAME(order_date) AS month_name,
            COUNT(*) AS orders,
            SUM(total_amount) AS revenue
     FROM Orders
     WHERE status != 'cancelled'
       AND order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY MONTH(order_date), MONTHNAME(order_date)
     ORDER BY month_num"
);
$report['monthly_sales'] = [];
while ($row = $result->fetch_assoc()) {
    $report['monthly_sales'][] = $row;
}

// 4. Top 5 Products by Revenue
$result = $conn->query(
    "SELECT p.name AS product_name,
            SUM(oi.quantity) AS units_sold,
            SUM(oi.quantity * oi.unit_price) AS revenue
     FROM Product p
     JOIN OrderItem oi ON p.product_id = oi.product_id
     JOIN Orders o ON oi.order_id = o.order_id
     WHERE o.status != 'cancelled'
     GROUP BY p.product_id, p.name
     ORDER BY revenue DESC
     LIMIT 5"
);
$report['top_products'] = [];
while ($row = $result->fetch_assoc()) {
    $report['top_products'][] = $row;
}

// 5. Payment Method Distribution
$result = $conn->query(
    "SELECT method, COUNT(*) AS count, SUM(amount) AS total
     FROM Payment
     WHERE status IN ('completed', 'pending')
     GROUP BY method
     ORDER BY total DESC"
);
$report['payment_methods'] = [];
while ($row = $result->fetch_assoc()) {
    $report['payment_methods'][] = $row;
}

// 6. Recent Orders (last 10)
$result = $conn->query(
    "SELECT o.order_id, o.order_date, o.status, o.total_amount,
            CONCAT(cu.first_name, ' ', cu.last_name) AS customer_name
     FROM Orders o
     JOIN Customer cu ON o.customer_id = cu.customer_id
     ORDER BY o.order_date DESC
     LIMIT 10"
);
$report['recent_orders'] = [];
while ($row = $result->fetch_assoc()) {
    $report['recent_orders'][] = $row;
}

jsonResponse($report);
$conn->close();
?>
