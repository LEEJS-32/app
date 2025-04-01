<?php
require '../db/db_connect.php';
include_once '../_base.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = sha1($_POST["password"]);
    $remember = isset($_POST["remember"]); // Check if "Remember Me" is ticked

    // Check user credentials
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user"] = $user; // Store in session

        if ($remember) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+7 days")); // Valid for 7 days

            // Store token in database
            $stmt = $conn->prepare("INSERT INTO token (user_id, token_id, expire) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user["user_id"], $token, $expiry);
            $stmt->execute();

            // Store token in cookie
            setcookie("remember_me", $token, time() + (86400 * 7), "/", "", false, true);
        }

        // Redirect based on role
        $redirect_url = ($user['role'] == "admin") ? "../pages/admin/admin_profile.php" : "../pages/member/member_profile.php";
        redirect($redirect_url);
    } else {
        $_SESSION["error"] = "Invalid email or password.";
        // redirect("../pages/signup_login.php");
        echo "Invalid email or password.";
    }
}
?>
