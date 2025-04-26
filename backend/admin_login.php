<?php
require_once '../_base.php';
require_once '../db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = sha1($_POST["password"]);
    $remember = isset($_POST["remember"]); // Check if "Remember Me" is ticked

    try {
        // First check if email exists
        $stm = $_db->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin'");
        $stm->execute([':email' => $email]);
        $result = $stm->fetch(PDO::FETCH_OBJ);

        if (!$result) {
            // Email not found
            $_SESSION['error'] = "Email not found";
            redirect("../pages/admin/admin_login.php");
            exit;
        }

        // Then check if password is correct
        $stm = $_db->prepare("SELECT * FROM users WHERE email = :email AND password = :password AND role = 'admin'");
        $stm->execute([
            ':email' => $email,
            ':password' => $password
        ]);
        $user = $stm->fetch(PDO::FETCH_OBJ);

        if ($user) {
            $_SESSION["user"] = $user;

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

            // Redirect to admin profile
            temp('info', 'Log in successfully.');
            redirect("../pages/admin/admin_profile.php");
        } else {
            // Invalid credentials
            $_SESSION['error'] = "Invalid email or password";
            redirect("../pages/admin/admin_login.php");
        }
    } catch (PDOException $e) {
        temp('info', 'Login failed. Please try again.');
        redirect("../pages/admin/admin_login.php");
    }
}
?> 