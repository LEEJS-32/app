<?php
require '../db/db_connect.php';
include_once '../_base.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = sha1($_POST["password"]);
    $remember = isset($_POST["remember"]); // Check if "Remember Me" is ticked

    try {
        // Check user credentials and activation status
        $stm = $_db->prepare("SELECT * FROM users WHERE email = :email AND password = :password AND is_active = 1");
        $stm->execute([
            ':email' => $email,
            ':password' => $password
        ]);
        $user = $stm->fetch();

        if ($user) {
            $_SESSION["user"] = $user; // Store in session
            $user_id = $user->user_id;

            // Merge guest cart into user's cart
            if (isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
                foreach ($_SESSION["cart"] as $product_id => $guest_quantity) {
                    // Check if the product already exists in the user's cart
                    $stm = $_db->prepare("SELECT quantity FROM shopping_cart WHERE user_id = :user_id AND product_id = :product_id");
                    $stm->execute([
                        ':user_id' => $user_id,
                        ':product_id' => $product_id
                    ]);
                    $result = $stm->fetch();

                    if ($result) {
                        // Product exists in cart, update quantity
                        $new_quantity = $result->quantity + $guest_quantity;
                        $stm = $_db->prepare("UPDATE shopping_cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                        $stm->execute([
                            ':quantity' => $new_quantity,
                            ':user_id' => $user_id,
                            ':product_id' => $product_id
                        ]);
                    } else {
                        // Product not in cart, insert new row
                        $stm = $_db->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                        $stm->execute([
                            ':user_id' => $user_id,
                            ':product_id' => $product_id,
                            ':quantity' => $guest_quantity
                        ]);
                    }
                }

                // Clear guest session cart after merging
                unset($_SESSION["cart"]);
            }

            if ($remember) {
                // Generate a secure token
                $token = bin2hex(random_bytes(32));
                $expiry = date("Y-m-d H:i:s", strtotime("+7 days")); // Valid for 7 days

                // Store token in database
                $stm = $_db->prepare("INSERT INTO token (user_id, token_id, expire) VALUES (:user_id, :token, :expire)");
                $stm->execute([
                    ':user_id' => $user_id,
                    ':token' => $token,
                    ':expire' => $expiry
                ]);

                // Store token in cookie
                setcookie("remember_me", $token, time() + (86400 * 7), "/", "", false, true);
            }

            // Redirect based on role
            $redirect_url = ($user->role == "admin") ? "../pages/admin/admin_profile.php" : "../pages/member/member_profile.php";
            redirect($redirect_url);
        } else {
            $_SESSION["error"] = "Invalid email, password, or account not activated.";
            echo "Invalid email, password, or account not activated.";
        }
    } catch (PDOException $e) {
        $_SESSION["error"] = "Login failed. Please try again.";
        echo "Login failed. Please try again.";
    }
}
?>
