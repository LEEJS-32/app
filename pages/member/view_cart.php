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

$user_email = 'test@example.com'; // Replace with logged-in user email

// Retrieve cart items
$sql = "SELECT shopping_cart.cart_id, shopping_cart.quantity, 
               products.name, products.price, products.image_url
        FROM shopping_cart
        JOIN products ON shopping_cart.product_id = products.product_id
        WHERE shopping_cart.user_email = '$user_email'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Shopping Cart</h2>";
    echo "<table border='1'><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>";

    while ($row = $result->fetch_assoc()) {
        $total_price = $row['price'] * $row['quantity'];
        echo "<tr>
                <td>{$row['name']}<br><img src='{$row['image_url']}' width='50'></td>
                <td>\${$row['price']}</td>
                <td>{$row['quantity']}</td>
                <td>\${$total_price}</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "Your cart is empty!";
}

$conn->close();
?>
