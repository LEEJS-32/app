<?php
require_once '../_base.php';
require_once '../db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = sha1($_POST["password"]);
    $remember = isset($_POST["remember"]); // Check if "Remember Me" is ticked

    // First check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Email not found
        $_SESSION['error'] = "Email not found";
        redirect("../pages/admin/admin_login.php");
        exit;
    }

    // Then check if password is correct
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ? AND role = 'admin'");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user"] = $user;

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

        // Redirect to admin profile
        redirect("../pages/admin/admin_profile.php");
    } else {
        // Invalid credentials
        $_SESSION['error'] = "Invalid email or password";
        redirect("../pages/admin/admin_login.php");
    }
}
?> 