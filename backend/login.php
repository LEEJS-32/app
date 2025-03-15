<!DOCTYPE html>
<html>
<?php
require '../database.php';
include '../_base.php';
session_start();  // Ensure session is started before using session variables

if (is_post()) {
    $email = post("email");
    $password = post("password");
    $hash_password = sha1($password);
    $remember = isset($_POST['remember']);

    // Check if user exists
    $sql_check_exist = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql_check_exist);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result_check_exist = $stmt->get_result();

    if ($result_check_exist->num_rows == 0) {
        $_SESSION['error_not_exist'] = 'Record not exists.';
        redirect("../pages/signup_login.php");
        exit();
    } else {
        // Check password
        $sql_check_pwd = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($sql_check_pwd);
        $stmt->bind_param("ss", $email, $hash_password);
        $stmt->execute();
        $result_check_pwd = $stmt->get_result();

        if ($result_check_pwd->num_rows > 0) {
            $user = $result_check_pwd->fetch_assoc();

            if ($remember) {
                // Generate a secure token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 days'));

                // Store token in database
                $stmt = $conn->prepare("INSERT INTO token (user_id, token_id, expire) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user['user_id'], $token, $expiry);
                $stmt->execute();

                // Set a cookie with token
                setcookie("remember_me", $token, time() + (86400 * 1), "/", "", false, true);
            }

            // Use the login function to set session and redirect
            echo "OK";
            login($user, ($user['role'] == "member") ? "../pages/member/product_list.php" : "/../pages/member/sample.php");
        } else {
            $_SESSION['error_pwd'] = 'Incorrect Password.';
            redirect("../pages/signup_login.php");
            exit();
        }
    }
}
?>
</html>

// redirect("/../pages/member/profile.php");
// exit();