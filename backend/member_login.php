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
        // Hash the password
        $hashed_password = sha1($password);

        // Check member credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ? AND role = 'member' AND is_active = 1");
        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION["user"] = $user;

            // Merge guest cart into user's cart if it exists
            if (isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
                foreach ($_SESSION["cart"] as $product_id => $guest_quantity) {
                    // Check if the product already exists in the user's cart
                    $stmt = $conn->prepare("SELECT quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $user['user_id'], $product_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        // Product exists in cart, update quantity
                        $row = $result->fetch_assoc();
                        $new_quantity = $row['quantity'] + $guest_quantity;
                        $stmt = $conn->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                        $stmt->bind_param("iii", $new_quantity, $user['user_id'], $product_id);
                    } else {
                        // Product not in cart, insert new row
                        $stmt = $conn->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->bind_param("iii", $user['user_id'], $product_id, $guest_quantity);
                    }
                    $stmt->execute();
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
                $stmt = $conn->prepare("INSERT INTO token (token_id, user_id, expire) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $token, $user['user_id'], $expiry);
                $stmt->execute();

                // Set cookie
                setcookie("remember_me", $token, time() + (30 * 24 * 60 * 60), "/"); // 30 days
            }

            // Redirect to member profile
            redirect("../pages/member/member_profile.php");
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'member'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors['password'] = 'Incorrect password';
            } else {
                $errors['email'] = 'Email not found';
            }
        }
    }

    // Store errors in session
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    redirect("../pages/signup_login.php");
}
?> 