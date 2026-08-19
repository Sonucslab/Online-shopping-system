<?php
// ============================================================
// fetch_external_api.php — Import Data from an External API
// This script fetches real product data from FakeStoreAPI
// (https://fakestoreapi.com/products) and inserts it into your DB.
// ============================================================
require_once 'php/db.php';

echo "<h2>Fetching Data from External Website (FakeStoreAPI)</h2>";

// 1. Fetch data from external API
$api_url = "https://fakestoreapi.com/products";
echo "<p>Contacting API: $api_url ...</p>";

$json_data = file_get_contents($api_url);

if ($json_data === FALSE) {
    die("<p style='color:red;'>Error: Could not reach the external API.</p>");
}

$products = json_decode($json_data, true);

if (empty($products)) {
    die("<p style='color:red;'>Error: No data received from API.</p>");
}

echo "<p>Successfully fetched " . count($products) . " products from external source.</p>";

// 2. Insert into Database
$conn->begin_transaction();

try {
    $inserted_cats = 0;
    $inserted_prods = 0;
    
    // First, let's map API categories to our DB
    foreach ($products as $item) {
        $cat_name = $item['category'];
        
        // Check if category exists
        $stmt = $conn->prepare("SELECT category_id FROM Category WHERE name = ?");
        $stmt->bind_param("s", $cat_name);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows == 0) {
            // Insert new category
            $stmt_in = $conn->prepare("INSERT INTO Category (name, description) VALUES (?, 'Imported from external API')");
            $stmt_in->bind_param("s", $cat_name);
            $stmt_in->execute();
            $cat_id = $conn->insert_id;
            $inserted_cats++;
        } else {
            $cat_id = $res->fetch_assoc()['category_id'];
        }
        
        // Check if product already exists (by name) to avoid duplicates
        $stmt_check = $conn->prepare("SELECT product_id FROM Product WHERE name = ?");
        $stmt_check->bind_param("s", $item['title']);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows == 0) {
            // Insert Product
            $desc = substr($item['description'], 0, 250); // Truncate description if too long
            $price = $item['price'];
            $image = $item['image'];
            $stock = rand(10, 100); // Generate random stock
            
            $stmt_prod = $conn->prepare(
                "INSERT INTO Product (category_id, name, description, price, stock_qty, image_url) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt_prod->bind_param("issdis", $cat_id, $item['title'], $desc, $price, $stock, $image);
            $stmt_prod->execute();
            $inserted_prods++;
        }
    }
    
    $conn->commit();
    echo "<p style='color:green;'><b>Success!</b> Inserted $inserted_cats new categories and $inserted_prods new products into the database.</p>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color:red;'><b>Database Error:</b> " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.html'>Return to Home</a></p>";
$conn->close();
?>
