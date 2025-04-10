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

// Get user and address details if logged in
if ($user_id > 0) {
    $sql_user = "SELECT name, phonenum, email FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_info = $stmt_user->get_result()->fetch_assoc();

    $sql_address = "SELECT address_line1, address_line2, city, state, postal_code, country
                    FROM shippingaddress WHERE user_id = ? AND is_default = 1";
    $stmt_address = $conn->prepare($sql_address);
    $stmt_address->bind_param("i", $user_id);
    $stmt_address->execute();
    $address_info = $stmt_address->get_result()->fetch_assoc();
} else {
    $user_info = null;
    $address_info = null;
}
?>

<h2>Checkout</h2>

<?php if ($user_id > 0 && $user_info): ?>
    <p><strong>Name:</strong> <?= htmlspecialchars($user_info['name']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($user_info['phonenum']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
<?php else: ?>
    <p><strong>Guest Checkout</strong></p>
<?php endif; ?>

<h3>Shopping Cart</h3>
<?php if (!empty($cart_items)): ?>
    <table border='1'>
        <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
        <?php foreach ($cart_items as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?><br><img src='<?= htmlspecialchars($row['image_url']) ?>' width='50'></td>
                <td><?= number_format($row['price'], 2) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= number_format($row['price'] * $row['quantity'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><strong>Total Price:</strong> RM <?= number_format($total_price, 2) ?></p>
<?php else: ?>
    <p>Your cart is empty!</p>
<?php endif; ?>

<h3>Shipping Information</h3>
<form action="checkout.php" method="POST">
    <label for="address_line1">Address Line 1:</label><br>
    <input type="text" name="address_line1" value="<?= htmlspecialchars($address_info['address_line1'] ?? '') ?>" required><br>
    
    <label for="address_line2">Address Line 2:</label><br>
    <input type="text" name="address_line2" value="<?= htmlspecialchars($address_info['address_line2'] ?? '') ?>"><br>
    
    <label for="city">City:</label><br>
    <input type="text" name="city" value="<?= htmlspecialchars($address_info['city'] ?? '') ?>" required><br>
    
    <label for="state">State:</label><br>
    <input type="text" name="state" value="<?= htmlspecialchars($address_info['state'] ?? '') ?>" required><br>
    
    <label for="postal_code">Postal Code:</label><br>
    <input type="text" name="postal_code" value="<?= htmlspecialchars($address_info['postal_code'] ?? '') ?>" required><br>
    
    <label for="country">Country:</label><br>
    <input type="text" name="country" value="<?= htmlspecialchars($address_info['country'] ?? '') ?>" required><br>
    
    <label for="comment">Comment to Seller:</label><br>
    <textarea name="comment"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea><br>
    
    <button type="submit" name="place_order">Place Order</button>
</form>

<?php
if (isset($_POST['place_order'])) {
    $order_id = 'ORD' . uniqid();
    $address_line1 = $_POST['address_line1'];
    $address_line2 = !empty($_POST['address_line2']) ? $_POST['address_line2'] : NULL;
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    $comment = !empty($_POST['comment']) ? $_POST['comment'] : NULL;
    
    $sql_order = "INSERT INTO orders (order_id, user_id, total_price, status, 
                        shipping_address_line1, shipping_address_line2, 
                        shipping_city, shipping_state, shipping_postal_code, 
                        shipping_country, comment) 
                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_order = $conn->prepare($sql_order);
    if (!$stmt_order) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt_order->bind_param("sissssssss", 
        $order_id, $user_id, $total_price, 
        $address_line1, $address_line2, $city, 
        $state, $postal_code, $country, $comment);
    
    if ($stmt_order->execute()) {
        header("Location: create_payment.php?order_id=$order_id");
        exit();
    } else {
        echo "Error: " . $stmt_order->error;
    }
}
?>
