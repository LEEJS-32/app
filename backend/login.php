<?php
require '../db/db_connect.php';
include_once '../_base.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = sha1($_POST["password"]);
    $remember = isset($_POST["remember"]); // Check if "Remember Me" is ticked

    // Check user credentials and activation status
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ? AND is_active = 1");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user"] = $user; // Store in session
        $user_id = $user["user_id"];

        // Merge guest cart into user's cart
        if (isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
            foreach ($_SESSION["cart"] as $product_id => $guest_quantity) {
                // Check if the product already exists in the user's cart
                $stmt = $conn->prepare("SELECT quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Product exists in cart, update quantity
                    $row = $result->fetch_assoc();
                    $new_quantity = $row['quantity'] + $guest_quantity;
                    $stmt = $conn->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
                } else {
                    // Product not in cart, insert new row
                    $stmt = $conn->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->bind_param("iii", $user_id, $product_id, $guest_quantity);
                }
                $stmt->execute();
            }

            // Clear guest session cart after merging
            unset($_SESSION["cart"]);
        }

        if ($remember) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+7 days")); // Valid for 7 days

            // Store token in database
            $stmt = $conn->prepare("INSERT INTO token (user_id, token_id, expire) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $token, $expiry);
            $stmt->execute();

            // Store token in cookie
            setcookie("remember_me", $token, time() + (86400 * 7), "/", "", false, true);
        }

        // Redirect based on role
        $redirect_url = ($user['role'] == "admin") ? "../pages/admin/admin_profile.php" : "../pages/member/member_profile.php";
        redirect($redirect_url);
    } else {
        $_SESSION["error"] = "Invalid email, password, or account not activated.";
        echo "Invalid email, password, or account not activated.";
    }
}
?>
