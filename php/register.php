<?php
// ============================================================
// register.php — Customer Registration
// POST: first_name, last_name, email, password, phone, address, city, zip_code
// ============================================================
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.html');
    exit;
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';
$phone      = trim($_POST['phone'] ?? '');
$address    = trim($_POST['address'] ?? '');
$city       = trim($_POST['city'] ?? '');
$zip_code   = trim($_POST['zip_code'] ?? '');

// Validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    header('Location: ../register.html?error=All+fields+are+required');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../register.html?error=Invalid+email+format');
    exit;
}

if (strlen($password) < 6) {
    header('Location: ../register.html?error=Password+must+be+at+least+6+characters');
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT customer_id FROM Customer WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header('Location: ../register.html?error=Email+already+registered');
    exit;
}
$stmt->close();

// Hash password and insert
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO Customer (first_name, last_name, email, password_hash, phone, address, city, zip_code, role)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'customer')"
);
$stmt->bind_param("ssssssss", $first_name, $last_name, $email, $password_hash, $phone, $address, $city, $zip_code);

if ($stmt->execute()) {
    header('Location: ../login.html?success=Registration+successful.+Please+login.');
    exit;
} else {
    header('Location: ../register.html?error=Registration+failed.+Try+again.');
    exit;
}
$stmt->close();
$conn->close();
?>
