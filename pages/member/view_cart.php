<?php

include_once '../../_base.php';
require '../../db/db_connect.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user['user_id'] : 0;  // 0 for guest

// Handle cart actions: Increase, Decrease, Remove
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $action = $_POST['action'];

    if ($user_id > 0) {
        // Logged-in user: Modify cart in the database
        if ($action === 'increase') {
            $sql = "UPDATE shopping_cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        } elseif ($action === 'decrease') {
            $sql = "UPDATE shopping_cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ? AND quantity > 1";
        } elseif ($action === 'remove') {
            $sql = "DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Guest user: Modify cart in session
        if (isset($_SESSION['cart'][$product_id])) {
            if ($action === 'increase') {
                $_SESSION['cart'][$product_id]++;
            } elseif ($action === 'decrease' && $_SESSION['cart'][$product_id] > 1) {
                $_SESSION['cart'][$product_id]--;
            } elseif ($action === 'remove') {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }

    // Redirect to avoid form resubmission issues
    header("Location: view_cart.php");
    exit();
}

// Retrieve updated cart items for logged-in users or from session for guest users
if ($user_id > 0) {
    // Logged-in user: Get cart items from the database
    $sql_cart = "SELECT shopping_cart.cart_id, shopping_cart.quantity, 
                        products.product_id, products.name, products.price, products.image_url
                 FROM shopping_cart
                 JOIN products ON shopping_cart.product_id = products.product_id
                 WHERE shopping_cart.user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();
    $cart_items = $result_cart->num_rows > 0 ? $result_cart->fetch_all(MYSQLI_ASSOC) : [];
} else {
    // Guest user: Get cart items from session
    $cart_items = [];
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $sql_product = "SELECT product_id, name, price, image_url FROM products WHERE product_id = ?";
            $stmt_product = $conn->prepare($sql_product);
            $stmt_product->bind_param("i", $product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();
            if ($result_product->num_rows > 0) {
                $product = $result_product->fetch_assoc();
                $product['quantity'] = $quantity;
                $cart_items[] = $product;
            }
        }
    }
}

$total_cart_price = 0;

echo "<h2>Shopping Cart</h2>";
if ($user_id > 0) {
    echo "<p><strong>User ID:</strong> $user_id</p>";
    echo "<p><strong>User Name:</strong> {$user['name']}</p>";
} else {
    echo "<p><strong>Guest Cart</strong></p>";
}

if (!empty($cart_items)) {
    echo "<table border='1'>
            <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Actions</th></tr>";

    foreach ($cart_items as $row) {
        $total_price = $row['price'] * $row['quantity'];
        $total_cart_price += $total_price;

        echo "<tr>
                <td>{$row['name']}<br><img src='{$row['image_url']}' width='50'></td>
                <td>{$row['price']}</td>
                <td>{$row['quantity']}</td>
                <td>{$total_price}</td>
                <td>
                    <form action='view_cart.php' method='POST'>
                        <input type='hidden' name='product_id' value='{$row['product_id']}'>
                        <button type='submit' name='action' value='increase'>+</button>
                        <button type='submit' name='action' value='decrease'>-</button>
                        <button type='submit' name='action' value='remove'>Remove</button>
                    </form>
                </td>
              </tr>";
    }

    echo "</table>";

    echo "<p><strong>Total Price:</strong> RM $total_cart_price</p>";

    // Store cart data in session before proceeding to checkout
    $_SESSION['cart_items'] = $cart_items;
    $_SESSION['total_price'] = $total_cart_price;

    if ($user_id > 0) {
        echo "<form action='checkout.php' method='POST'>
        <button type='submit'>Proceed to Checkout</button>
      </form>";
    } else {
        echo "<p>You must <a href='../signup_login.php'>log in</a> to proceed to checkout.</p>";
    }
} else {
    echo "<p>Your cart is empty!</p>";
}
?>
