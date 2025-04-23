<?php
require '../_base.php';
require '../database.php';

$email = $_SESSION['reset_email'] ?? '';
$password = $_POST['password'] ?? '';
$hash_password = sha1($password);

if ($email) {
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hash_password, $email);
    $stmt->execute();

    unset($_SESSION['reset_email']);
    echo "Password reset successful. <a href='../pages/signup_login.php'>Login</a>";
} else {
    echo "Session expired.";
}
?>
