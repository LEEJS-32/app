<?php
require '../_base.php';
require '../database.php';

$email = $_SESSION['reset_email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate passwords match
if ($password !== $confirm_password) {
    header("Location: ../pages/fail_reset_password.php?error=password_mismatch");
    exit();
}

if ($email) {
    try {
        $hash_password = sha1($password);
        $stm = $_db->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stm->execute([
            ':password' => $hash_password,
            ':email' => $email
        ]);

        if ($stm->rowCount() > 0) {
            unset($_SESSION['reset_email']);
            header("Location: ../pages/success_reset_password.php");
            exit();
        } else {
            header("Location: ../pages/fail_reset_password.php?error=update_failed");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../pages/fail_reset_password.php?error=database_error");
        exit();
    }
} else {
    header("Location: ../pages/fail_reset_password.php?error=session_expired");
    exit();
}
?>
