<?php
include_once '../_base.php';
require '../db/db_connect.php';

//member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;

$recaptcha_secret = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    if (empty($recaptcha_response)) {
        temp('info', 'Please complete the CAPTCHA verification.');
        redirect("../pages/admin/admin_reset_password.php");
        exit;
    }

    // Validate reCAPTCHA
    $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response";
    $response = file_get_contents($verify_url);
    $response_data = json_decode($response);

    if (!$response_data->success) {
        temp('info', 'CAPTCHA verification failed. Please try again.');
        redirect("../pages/admin/admin_reset_password.php");
        exit;
    }

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        redirect("../pages/admin/admin_reset_password.php");
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match.";
        redirect("../pages/admin/admin_reset_password.php");
        exit;
    }

    try {
        // Fetch stored SHA-1 hashed password from database
        $stm = $_db->prepare("SELECT password FROM users WHERE user_id = :user_id AND role = 'admin'");
        $stm->execute([':user_id' => $user_id]);
        $stored_hash = $stm->fetchColumn();

        // Verify old password
        if (sha1($old_password) !== $stored_hash) {
            $_SESSION['error'] = "Old password is incorrect.";
            redirect("../pages/admin/admin_reset_password.php");
            exit;
        }

        // Hash the new password securely (bcrypt)
        $new_hashed_password = sha1($new_password);

        // Update password in database
        $update_stm = $_db->prepare("UPDATE users SET password = :password WHERE user_id = :user_id AND role = 'admin'");
        $update_stm->execute([
            ':password' => $new_hashed_password,
            ':user_id' => $user_id
        ]);

        temp('info', 'Password updated successfully!');
        redirect("../pages/admin/admin_profile.php");
        exit();
    } catch (PDOException $e) {
        temp('info', 'Error updating password');
        redirect("../pages/admin/admin_reset_password.php");
    }
}
?> 