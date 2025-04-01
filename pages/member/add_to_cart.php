<?php

include_once '../../_base.php';
require_once '../../db/db_connect.php'; // Ensure correct path

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0 || $quantity <= 0) {
    $_SESSION['cart_message'] = "Error: Invalid product or quantity.";
    header("Location: product_list.php");
    exit();
}

// Check if product exists and fetch stock
$sql_check_product = "SELECT name, stock FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql_check_product);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['cart_message'] = "Error: Product not found.";
    header("Location: product_list.php");
    exit();
}

$row = $result->fetch_assoc();
$available_stock = intval($row['stock']);
$product_name = $row['name'];

if ($quantity > $available_stock) {
    $_SESSION['cart_message'] = "Error: Not enough stock available!";
    header("Location: product_list.php");
    exit();
}

// Check if user is logged in
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $user_id = $user['user_id'];

    // Merge guest cart if exists
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $guest_product_id => $guest_quantity) {
            $sql_check_cart = "SELECT quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?";
            $stmt = $conn->prepare($sql_check_cart);
            $stmt->bind_param("ii", $user_id, $guest_product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $new_quantity = min($row['quantity'] + $guest_quantity, $available_stock);
                $sql_update = "UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = $conn->prepare($sql_update);
                $stmt->bind_param("iii", $new_quantity, $user_id, $guest_product_id);
            } else {
                $sql_insert = "INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql_insert);
                $stmt->bind_param("iii", $user_id, $guest_product_id, $guest_quantity);
            }
            $stmt->execute();
        }
        unset($_SESSION['cart']); // Clear guest cart after merging
    }

    // Check if product is already in the user's cart
    $sql_check_cart = "SELECT quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql_check_cart);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_quantity = min($row['quantity'] + $quantity, $available_stock);
        $sql_update = "UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
    } else {
        $sql_insert = "INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    }
    $stmt->execute();
} else {
    // Guest user, store in session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

$_SESSION['cart_message'] = "✅ '$product_name' added to cart successfully!";
header("Location: product_list.php");
exit();

$stmt->close();
$conn->close();
?>
