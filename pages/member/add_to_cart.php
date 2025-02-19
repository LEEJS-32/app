<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "TESTING1";

// Connect to database
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example user and product (replace with actual values)
$user_email = 'test@example.com';  // Replace with logged-in user email
$product_id = 1; // Replace with actual product_id from `products` table
$quantity = 2; // Example quantity

// Check if the product is already in the cart
$sql_check = "SELECT * FROM shopping_cart WHERE user_email='$user_email' AND product_id='$product_id'";
$result = $conn->query($sql_check);

if ($result->num_rows > 0) {
    // Update quantity if product exists in cart
    $sql_update = "UPDATE shopping_cart SET quantity = quantity + $quantity WHERE user_email='$user_email' AND product_id='$product_id'";
    if ($conn->query($sql_update) === TRUE) {
        echo "Cart updated successfully!";
    } else {
        echo "Error updating cart: " . $conn->error;
    }
} else {
    // Insert new item into cart
    $sql_insert = "INSERT INTO shopping_cart (user_email, product_id, quantity) VALUES ('$user_email', '$product_id', '$quantity')";
    if ($conn->query($sql_insert) === TRUE) {
        echo "Product added to cart successfully!";
    } else {
        echo "Error adding to cart: " . $conn->error;
    }
}

$conn->close();
?>
