<?php
session_start();
require_once __DIR__ . '/../../database.php'; 

if (!isset($_SESSION['email'])) {
    die("Error: User not logged in.");
}

$user_email = $_SESSION['email'];

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    die("Error: Invalid request.");
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

if ($quantity <= 0) {
    $_SESSION['cart_message'] = "Error: Invalid quantity.";
    header("Location: product_list.php");
    exit();
}

// Check if product exists and fetch stock
$sql_check_product = "SELECT stock FROM products WHERE product_id = ?";
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

if ($quantity > $available_stock) {
    $_SESSION['cart_message'] = "Error: Not enough stock available!";
    header("Location: product_list.php");
    exit();
}

// Check if the product is already in the cart
$sql_check_cart = "SELECT quantity FROM shopping_cart WHERE email = ? AND product_id = ?";
$stmt = $conn->prepare($sql_check_cart);
$stmt->bind_param("si", $user_email, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;

    if ($new_quantity > $available_stock) {
        $_SESSION['cart_message'] = "Error: Not enough stock available!";
        header("Location: product_list.php");
        exit();
    }

    $sql_update = "UPDATE shopping_cart SET quantity = ? WHERE email = ? AND product_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("isi", $new_quantity, $user_email, $product_id);
} else {
    $sql_insert = "INSERT INTO shopping_cart (email, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sii", $user_email, $product_id, $quantity);
}

// Execute the query
if ($stmt->execute()) {
    $_SESSION['cart_message'] = "Product added to cart successfully!";
} else {
    $_SESSION['cart_message'] = "Error: Could not add to cart.";
}

header("Location: product_list.php");
exit();

$stmt->close();
$conn->close();
?>
