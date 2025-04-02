<?php
session_start();
require_once '../_base.php';
require '../database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $hash_password = password_hash($password, PASSWORD_BCRYPT);
    $avatar = "../../img/avatar/avatar.jpg";

    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result_check_exist = $stmt->get_result();

    if ($result_check_exist->num_rows > 0) {
        $_SESSION['error_exist'] = "Record exists.";
        header("Location: ../pages/signup_login.php");
        exit();
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, avatar) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hash_password, $avatar);
        
        if ($stmt->execute()) {
            echo "Signup successful<br>";

            // Fetch the user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $_SESSION["user"] = $user;
                print_r($user);
            }
            header("Location: ../pages/extra_info.php");
            exit();
        }
    }
}
?>
</html>