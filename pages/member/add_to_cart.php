<?php
include_once '../../_base.php';
require '../../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
    $user_id = $user ? $user['user_id'] : 0;
    $product_name = ''; // Initialize for later use

    // Get product details
    $sql_product = "SELECT name FROM products WHERE product_id = ?";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    if ($result_product->num_rows > 0) {
        $product_row = $result_product->fetch_assoc();
        $product_name = $product_row['name'];
    }

    // Add product to guest cart (session)
    if ($user_id == 0) {
        // Save to session cart for guests
        $_SESSION['cart'][$product_id] = isset($_SESSION['cart'][$product_id]) 
            ? $_SESSION['cart'][$product_id] + $quantity 
            : $quantity;
        
        // Set success message for guest
        $_SESSION['cart_message'] = "✅ '$product_name' added to cart successfully!";
        
    } else {
        // Add to user's cart in database
        $sql_check_cart = "SELECT quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?";
        $stmt_check = $conn->prepare($sql_check_cart);
        $stmt_check->bind_param("ii", $user_id, $product_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Update quantity if the product exists in the database cart
            $row = $result_check->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;
            $sql_update = "UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("iii", $new_quantity, $user_id, $product_id);
        } else {
            // Insert a new entry if the product is not in the cart
            $sql_insert = "INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt_update = $conn->prepare($sql_insert);
            $stmt_update->bind_param("iii", $user_id, $product_id, $quantity);
        }
        $stmt_update->execute();
        $stmt_update->close();
        
        // Set success message for logged-in user
        $_SESSION['cart_message'] = "✅ '$product_name' added to your cart!";
    }

    // Redirect to product list page after adding to cart
    header("Location: product_list.php");
    exit();
}
?>
