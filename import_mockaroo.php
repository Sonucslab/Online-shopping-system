<?php
// ============================================================
// import_mockaroo.php — Import Mockaroo CSV Data
// Run this file in your browser to import CSV data into MySQL.
// Make sure your CSV files are in the 'mockaroo' folder.
// ============================================================
require_once 'php/db.php';

$base_dir = __DIR__ . '/mockaroo/';
$errors = [];
$successes = [];

function import_csv($conn, $filename, $table, $columns, $types) {
    global $base_dir, $errors, $successes;
    $filepath = $base_dir . $filename;
    
    if (!file_exists($filepath)) {
        $errors[] = "File not found: $filename";
        return;
    }
    
    $handle = fopen($filepath, "r");
    if ($handle !== FALSE) {
        $header = fgetcsv($handle); // Skip header row
        
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $col_names = implode(',', $columns);
        $sql = "INSERT INTO $table ($col_names) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
             $errors[] = "SQL Error for $table: " . $conn->error;
             return;
        }

        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Adjust data if needed (e.g. hash passwords)
            if ($table === 'Customer' && in_array('password_hash', $columns)) {
                $pwd_index = array_search('password_hash', $columns);
                if(isset($data[$pwd_index])) {
                    $data[$pwd_index] = password_hash($data[$pwd_index], PASSWORD_DEFAULT);
                }
            }

            $stmt->bind_param($types, ...$data);
            if ($stmt->execute()) {
                $count++;
            } else {
                $errors[] = "Failed to insert row into $table: " . $stmt->error;
            }
        }
        fclose($handle);
        $successes[] = "Imported $count rows into $table.";
    } else {
        $errors[] = "Could not open file: $filename";
    }
}

// Ensure the tables are empty before import to avoid duplicate keys
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE Payment");
$conn->query("TRUNCATE TABLE OrderItem");
$conn->query("TRUNCATE TABLE Orders");
$conn->query("TRUNCATE TABLE CartItem");
$conn->query("TRUNCATE TABLE Cart");
$conn->query("TRUNCATE TABLE Product");
$conn->query("TRUNCATE TABLE Category");
$conn->query("TRUNCATE TABLE Customer");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Define table structures matching your CSVs
// (Make sure the Mockaroo CSV columns match these exactly in order)

echo "<h2>Mockaroo Data Import</h2>";

// 1. Categories
import_csv($conn, 'categories.csv', 'Category', ['name', 'description'], 'ss');

// 2. Products
import_csv($conn, 'products.csv', 'Product', ['category_id', 'name', 'description', 'price', 'stock_qty', 'image_url'], 'issdis');

// 3. Customers
import_csv($conn, 'customers.csv', 'Customer', ['first_name', 'last_name', 'email', 'password_hash', 'phone', 'address', 'city', 'zip_code', 'role'], 'sssssssss');

// 4. Orders
import_csv($conn, 'orders.csv', 'Orders', ['customer_id', 'order_date', 'status', 'shipping_address', 'total_amount'], 'isssd');

// 5. OrderItems
import_csv($conn, 'order_items.csv', 'OrderItem', ['order_id', 'product_id', 'quantity', 'unit_price'], 'iiid');

// 6. Payments
import_csv($conn, 'payments.csv', 'Payment', ['order_id', 'payment_date', 'method', 'amount', 'status'], 'issds');


echo "<h3>Results</h3>";
if (!empty($successes)) {
    echo "<ul style='color: green;'>";
    foreach ($successes as $msg) echo "<li>$msg</li>";
    echo "</ul>";
}

if (!empty($errors)) {
    echo "<h3>Errors</h3>";
    echo "<ul style='color: red;'>";
    foreach ($errors as $msg) echo "<li>$msg</li>";
    echo "</ul>";
}
echo "<p><a href='index.html'>Return to Home</a></p>";
$conn->close();
?>
