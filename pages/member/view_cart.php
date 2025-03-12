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

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
    die("Error: User not logged in.");
}

$user_email = $_SESSION['email']; // Get logged-in user email

// Retrieve user details (user_id and name)
$sql_user = "SELECT user_id, name FROM users WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Error: User not found.");
}

$user = $result_user->fetch_assoc();
$user_id = $user['user_id'];
$user_name = $user['name'];

// Handle quantity update or item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'], $_POST['action'])) {
    $cart_id = $_POST['cart_id'];
    $action = $_POST['action'];

    if ($action === 'increase') {
        $sql_update = "UPDATE shopping_cart SET quantity = quantity + 1 WHERE cart_id = ? AND user_id = ?";
    } elseif ($action === 'decrease') {
        // Check current quantity before decreasing
        $sql_check_quantity = "SELECT quantity FROM shopping_cart WHERE cart_id = ? AND user_id = ?";
        $stmt_check = $conn->prepare($sql_check_quantity);
        $stmt_check->bind_param("ii", $cart_id, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row = $result_check->fetch_assoc();
        $current_quantity = $row['quantity'];
        $stmt_check->close();

        if ($current_quantity > 1) {
            $sql_update = "UPDATE shopping_cart SET quantity = quantity - 1 WHERE cart_id = ? AND user_id = ?";
        } else {
            // If quantity reaches 0, delete the item
            $sql_update = "DELETE FROM shopping_cart WHERE cart_id = ? AND user_id = ?";
        }
    } elseif ($action === 'remove') {
        // Remove the product from cart completely
        $sql_update = "DELETE FROM shopping_cart WHERE cart_id = ? AND user_id = ?";
    }

    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $cart_id, $user_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Refresh the page after updating
    header("Location: view_cart.php");
    exit();
}

// Retrieve cart items
$sql_cart = "SELECT shopping_cart.cart_id, shopping_cart.quantity, 
                    products.name, products.price, products.image_url
             FROM shopping_cart
             JOIN products ON shopping_cart.product_id = products.product_id
             WHERE shopping_cart.user_id = ?";

$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();

$total_cart_price = 0;

// Display user info
echo "<h2>Shopping Cart</h2>";
echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>User Name:</strong> $user_name</p>";

if ($result_cart->num_rows > 0) {
    echo "<table border='1'>
            <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Actions</th></tr>";

    while ($row = $result_cart->fetch_assoc()) {
        $total_price = $row['price'] * $row['quantity'];
        $total_cart_price += $total_price;

        echo "<tr>
                <td>{$row['name']}<br><img src='{$row['image_url']}' width='50'></td>
                <td>\${$row['price']}</td>
                <td>{$row['quantity']}</td>
                <td>\${$total_price}</td>
                <td>
                    <form method='POST' action='view_cart.php' style='display:inline;'>
                        <input type='hidden' name='cart_id' value='{$row['cart_id']}'>
                        <button type='submit' name='action' value='increase'>+</button>
                    </form>
                    <form method='POST' action='view_cart.php' style='display:inline;'>
                        <input type='hidden' name='cart_id' value='{$row['cart_id']}'>
                        <button type='submit' name='action' value='decrease'>-</button>
                    </form>
                    <form method='POST' action='view_cart.php' style='display:inline;'>
                        <input type='hidden' name='cart_id' value='{$row['cart_id']}'>
                        <button type='submit' name='action' value='remove'>Remove</button>
                    </form>
                </td>
              </tr>";
    }

    echo "<tr>
            <td colspan='3' align='right'><strong>Total Amount:</strong></td>
            <td><strong>\$$total_cart_price</strong></td>
            <td></td>
          </tr>";

    echo "</table>";

    // Checkout button
    echo "<br>
          <form action='create_payment.php' method='POST'>
              <input type='hidden' name='user_id' value='$user_id'>
              <input type='hidden' name='user_name' value='$user_name'>
              <input type='hidden' name='total_amount' value='$total_cart_price'>
              <button type='submit'>Checkout</button>
          </form>";
} else {
    echo "<p>Your cart is empty!</p>";
}

$stmt_user->close();
$stmt_cart->close();
$conn->close();
?>
