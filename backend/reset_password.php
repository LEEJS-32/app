<?php
include_once '../_base.php';
require '../db/db_connect.php'; // Include database connection

//member role
auth_user();
auth('member');

$new_user = false;
$_SESSION["new_user"] = $new_user;

$user = $_SESSION['user'];
$user_id = $user->user_id;
echo ($user_id);

$recaptcha_secret = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Validate reCAPTCHA
    $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response";
    $response = file_get_contents($verify_url);
    $response_data = json_decode($response);

    if (!$response_data->success) {
        $_SESSION['error'] = "CAPTCHA verification failed. Please try again.";
        redirect("../pages/member/reset_password.php");
        exit;
    }

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        redirect("../pages/member/reset_password.php");
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match.";
        redirect("../pages/member/reset_password.php");
        exit;
    }

    try {
        // Fetch stored SHA-1 hashed password from database
        $stm = $_db->prepare("SELECT password FROM users WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);
        $stored_hash = $stm->fetchColumn();

        // Verify old password
        if (sha1($old_password) !== $stored_hash) {
            $_SESSION['error'] = "Old password is incorrect.";
            redirect("../pages/member/reset_password.php");
            exit;
        }

        // Hash the new password securely (bcrypt)
        $new_hashed_password = sha1($new_password);

        // Update password in database
        $update_stm = $_db->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
        $update_stm->execute([
            ':password' => $new_hashed_password,
            ':user_id' => $user_id
        ]);

        $_SESSION['success'] = "Password reset successful!";
        redirect("../pages/member/reset_password.php");
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating password.";
        redirect("../pages/member/reset_password.php");
    }
}
?>
