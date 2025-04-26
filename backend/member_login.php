<?php
require_once '../_base.php';
require_once '../db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $remember = isset($_POST["remember"]);

    // Server-side validation
    $errors = [];

    // Email validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    // Password validation
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)';
    }

    if (empty($errors)) {
        try {
            // Hash the password
            $hashed_password = sha1($password);

            // Check member credentials
            $stm = $_db->prepare("SELECT * FROM users WHERE email = :email AND password = :password AND role = 'member' AND is_active = 1");
            $stm->execute([
                ':email' => $email,
                ':password' => $hashed_password
            ]);
            $user = $stm->fetch(PDO::FETCH_OBJ);

            if ($user) {
                $_SESSION["user"] = $user;

                // Merge guest cart into user's cart if it exists
                if (isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
                    foreach ($_SESSION["cart"] as $product_id => $guest_quantity) {
                        // Check if the product already exists in the user's cart
                        $stm = $_db->prepare("SELECT quantity FROM shopping_cart WHERE user_id = :user_id AND product_id = :product_id");
                        $stm->execute([
                            ':user_id' => $user->user_id,
                            ':product_id' => $product_id
                        ]);
                        $result = $stm->fetch(PDO::FETCH_OBJ);

                        if ($result) {
                            // Product exists in cart, update quantity
                            $new_quantity = $result->quantity + $guest_quantity;
                            $stm = $_db->prepare("UPDATE shopping_cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                            $stm->execute([
                                ':quantity' => $new_quantity,
                                ':user_id' => $user->user_id,
                                ':product_id' => $product_id
                            ]);
                        } else {
                            // Product not in cart, insert new row
                            $stm = $_db->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                            $stm->execute([
                                ':user_id' => $user->user_id,
                                ':product_id' => $product_id,
                                ':quantity' => $guest_quantity
                            ]);
                        }
                    }

                    // Clear guest session cart after merging
                    unset($_SESSION["cart"]);
                }

                // Handle "Remember Me" functionality
                if ($remember) {
                    // Generate a unique token
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+30 days')); // Token expires in 30 days

                    // Store token in database
                    $stm = $_db->prepare("INSERT INTO token (token_id, user_id, expire) VALUES (:token, :user_id, :expire)");
                    $stm->execute([
                        ':token' => $token,
                        ':user_id' => $user->user_id,
                        ':expire' => $expiry
                    ]);

                    // Set cookie
                    setcookie("remember_me", $token, time() + (30 * 24 * 60 * 60), "/"); // 30 days
                }

                // Redirect to member profile
                redirect("../pages/member/member_profile.php");
            } else {
                // Check if email exists
                $stm = $_db->prepare("SELECT * FROM users WHERE email = :email AND role = 'member'");
                $stm->execute([':email' => $email]);
                $result = $stm->fetch(PDO::FETCH_OBJ);

                if ($result) {
                    $errors['password'] = 'Incorrect password';
                } else {
                    $errors['email'] = 'Email not found';
                }
            }
        } catch (PDOException $e) {
            $errors['general'] = "Login failed. Please try again.";
        }
    }

    // Store errors in session
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    redirect("../pages/signup_login.php");
}
?> 