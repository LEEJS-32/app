<?php
require '../_base.php';
require '../database.php';

$email = $_SESSION['reset_email'] ?? '';
$password = $_POST['password'] ?? '';
$hash_password = sha1($password);

if ($email) {
    try {
        $stm = $_db->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stm->execute([
            ':password' => $hash_password,
            ':email' => $email
        ]);

        unset($_SESSION['reset_email']);
        echo "Password reset successful. <a href='../pages/signup_login.php'>Login</a>";
    } catch (PDOException $e) {
        echo "Error resetting password. Please try again.";
    }
} else {
    echo "Session expired.";
}
?>
