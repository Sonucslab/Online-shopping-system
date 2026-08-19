<?php
// ============================================================
// login.php — User Authentication
// POST: email, password
// Sets session: user_id, first_name, role
// Redirects: Admin → admin/dashboard.php | Customer → index.html
// ============================================================
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header('Location: ../login.html?error=Email+and+password+are+required');
    exit;
}

// Look up user
$stmt = $conn->prepare(
    "SELECT customer_id, first_name, last_name, password_hash, role FROM Customer WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../login.html?error=Invalid+email+or+password');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    header('Location: ../login.html?error=Invalid+email+or+password');
    exit;
}

// Set session
$_SESSION['user_id']    = $user['customer_id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['last_name']  = $user['last_name'];
$_SESSION['role']       = $user['role'];

// Redirect based on role
if ($user['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
} else {
    header('Location: ../index.html');
}
exit;
?>
