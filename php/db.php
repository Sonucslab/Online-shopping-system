<?php
// ============================================================
// db.php — Database Connection (MySQLi)
// XAMPP default: host=localhost, user=root, password=""
// ============================================================

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'nexus_shop';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: send JSON response
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper: check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.html?error=Please+login+first');
        exit;
    }
}

// Helper: check if user is admin
function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../login.html?error=Admin+access+required');
        exit;
    }
}
?>
