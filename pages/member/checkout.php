<?php
include_once '../../_base.php';
require '../../db/db_connect.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user['user_id'] : 0;  // 0 for guest

// Retrieve cart items and total price from session
$cart_items = isset($_SESSION['cart_items']) ? $_SESSION['cart_items'] : [];
$total_price = isset($_SESSION['total_price']) ? $_SESSION['total_price'] : 0;

// Get the user information for logged-in users
if ($user_id > 0) {
    // Fetch user information
    $sql_user = "SELECT name, phonenum, email FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_info = $result_user->fetch_assoc();

    // Fetch the user's shipping address
    $sql_address = "SELECT address_line1, address_line2, city, state, postal_code, country
                    FROM shippingaddress WHERE user_id = ? AND is_default = 1";
    $stmt_address = $conn->prepare($sql_address);
    $stmt_address->bind_param("i", $user_id);
    $stmt_address->execute();
    $result_address = $stmt_address->get_result();
    $address_info = $result_address->fetch_assoc();
} else {
    $user_info = null;
    $address_info = null;
}

// Display user information
echo "<h2>Checkout</h2>";

if ($user_id > 0 && $user_info) {
    echo "<p><strong>User Name:</strong> {$user_info['name']}</p>";
    echo "<p><strong>User Phone:</strong> {$user_info['phonenum']}</p>";
    echo "<p><strong>User Email:</strong> {$user_info['email']}</p>";
} else {
    echo "<p><strong>Guest Checkout</strong></p>";
}

// Display cart items
echo "<h3>Shopping Cart</h3>";
if (!empty($cart_items)) {
    echo "<table border='1'>
            <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>";
    foreach ($cart_items as $row) {
        $total_item_price = $row['price'] * $row['quantity'];
        echo "<tr>
                <td>{$row['name']}<br><img src='{$row['image_url']}' width='50'></td>
                <td>{$row['price']}</td>
                <td>{$row['quantity']}</td>
                <td>{$total_item_price}</td>
              </tr>";
    }
    echo "</table>";
    echo "<p><strong>Total Price:</strong> RM $total_price</p>";
} else {
    echo "<p>Your cart is empty!</p>";
}

// Display shipping information form with pre-filled values
echo "<h3>Shipping Information</h3>";
?>
<form action="checkout.php" method="POST">
    <label for="address_line1">Address Line 1:</label><br>
    <input type="text" id="address_line1" name="address_line1" value="<?= isset($address_info['address_line1']) ? htmlspecialchars($address_info['address_line1']) : '' ?>" required><br>

    <label for="address_line2">Address Line 2:</label><br>
    <input type="text" id="address_line2" name="address_line2" value="<?= isset($address_info['address_line2']) ? htmlspecialchars($address_info['address_line2']) : '' ?>"><br>

    <label for="city">City:</label><br>
    <input type="text" id="city" name="city" value="<?= isset($address_info['city']) ? htmlspecialchars($address_info['city']) : '' ?>" required><br>

    <label for="state">State:</label><br>
    <input type="text" id="state" name="state" value="<?= isset($address_info['state']) ? htmlspecialchars($address_info['state']) : '' ?>" required><br>

    <label for="postal_code">Postal Code:</label><br>
    <input type="text" id="postal_code" name="postal_code" value="<?= isset($address_info['postal_code']) ? htmlspecialchars($address_info['postal_code']) : '' ?>" required><br>

    <label for="country">Country:</label><br>
    <input type="text" id="country" name="country" value="<?= isset($address_info['country']) ? htmlspecialchars($address_info['country']) : '' ?>" required><br>

    <label for="comment">Comment to Seller:</label><br>
    <textarea id="comment" name="comment"><?= isset($address_info['comment']) ? htmlspecialchars($address_info['comment']) : '' ?></textarea><br>

    <button type="submit" name="place_order">Place Order</button>
</form>

<?php
// Handle form submission
if (isset($_POST['place_order'])) {
    // Get the shipping information
    $address_line1 = $_POST['address_line1'];
    $address_line2 = !empty($_POST['address_line2']) ? $_POST['address_line2'] : NULL;
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    $comment = !empty($_POST['comment']) ? $_POST['comment'] : NULL;

    // Generate a unique order ID
    $order_id = 'ORD' . uniqid();

    // Insert order into the orders table
    $order_insert = $conn->prepare("
        INSERT INTO orders (order_id, user_id, total_price, status, 
                            shipping_address_line1, shipping_address_line2, 
                            shipping_city, shipping_state, shipping_postal_code, 
                            shipping_country, comment) 
        VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)
    ");

    // Bind the parameters correctly to the prepared statement
    $order_insert->bind_param("sidssssssss", 
                             $order_id, 
                             $user_id, 
                             $total_price, 
                             $address_line1, 
                             $address_line2, 
                             $city, 
                             $state, 
                             $postal_code, 
                             $country, 
                             $comment);

    // Execute the insert query
    if ($order_insert->execute()) {
        // Redirect to the payment page
        header("Location: create_payment.php?order_id=$order_id");
        exit();
    } else {
        echo "Error: " . $order_insert->error;
    }
}
?>
