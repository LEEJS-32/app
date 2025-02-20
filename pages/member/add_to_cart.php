<?php
session_start();
include('../../database.php'); // Ensure correct path to database connection

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    die("Error: User not logged in.");
}

$user_email = $_SESSION['email']; // Correct assignment of session email

// Get product ID and quantity from POST request
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    die("Error: Invalid request.");
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

if ($quantity <= 0) {
    die("Error: Invalid quantity.");
}

// Check if the product exists
$sql_check_product = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql_check_product);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Product not found.");
}

// Check if the product is already in the cart
$sql_check_cart = "SELECT quantity FROM shopping_cart WHERE email = ? AND product_id = ?";
$stmt = $conn->prepare($sql_check_cart);
$stmt->bind_param("si", $user_email, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity if product exists in cart
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;

    $sql_update = "UPDATE shopping_cart SET quantity = ? WHERE email = ? AND product_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("isi", $new_quantity, $user_email, $product_id);
} else {
    // Insert new item into cart
    $sql_insert = "INSERT INTO shopping_cart (email, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sii", $user_email, $product_id, $quantity);
}

// Execute the query
if ($stmt->execute()) {
    echo "Product added to cart successfully!";
} else {
    echo "Error adding to cart: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
