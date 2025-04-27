<?php
include_once '../../_base.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
    $user_id = $user ? $user->user_id : 0;
    $product_name = ''; // Initialize for later use

    try {
        // Get product details
        $stm_product = $_db->prepare("SELECT name FROM products WHERE product_id = :product_id");
        $stm_product->execute([':product_id' => $product_id]);
        $product = $stm_product->fetch(PDO::FETCH_OBJ);
        
        if ($product) {
            $product_name = $product->name;
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
            $stm_check = $_db->prepare("SELECT quantity FROM shopping_cart WHERE user_id = :user_id AND product_id = :product_id");
            $stm_check->execute([
                ':user_id' => $user_id,
                ':product_id' => $product_id
            ]);
            $cart_item = $stm_check->fetch(PDO::FETCH_OBJ);

            if ($cart_item) {
                // Update quantity if the product exists in the database cart
                $new_quantity = $cart_item->quantity + $quantity;
                $stm_update = $_db->prepare("UPDATE shopping_cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                $stm_update->execute([
                    ':quantity' => $new_quantity,
                    ':user_id' => $user_id,
                    ':product_id' => $product_id
                ]);
            } else {
                // Insert a new entry if the product is not in the cart
                $stm_insert = $_db->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $stm_insert->execute([
                    ':user_id' => $user_id,
                    ':product_id' => $product_id,
                    ':quantity' => $quantity
                ]);
            }
            
            // Set success message for logged-in user
            $_SESSION['cart_message'] = "✅ '$product_name' added to your cart!";
        }

        // Redirect to product list page after adding to cart
        header("Location: product_list.php");
        exit();

    } catch (PDOException $e) {
        error_log("Error in add_to_cart: " . $e->getMessage());
        $_SESSION['cart_message'] = "Error adding product to cart. Please try again.";
        header("Location: product_list.php");
        exit();
    }
}
?>
