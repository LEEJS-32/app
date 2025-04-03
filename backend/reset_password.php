<?php
include_once '../_base.php';
require '../db/db_connect.php'; // Include database connection

//member role
auth_user();
auth('member');

$new_user = false;
$_SESSION["new_user"] = $new_user;

$user = $_SESSION['user'];
$user_id = $user['user_id'];
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
        die("CAPTCHA verification failed. Please try again.");
    }

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        die("All fields are required.");
    }

    if ($new_password !== $confirm_password) {
        die("New passwords do not match.");
    }

    // Fetch stored SHA-1 hashed password from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($stored_hash);
    $stmt->fetch();
    $stmt->close();

    // Verify old password
    if (sha1($old_password) !== $stored_hash) {
        die("Old password is incorrect.");
    }

    // Hash the new password securely (bcrypt)
    $new_hashed_password = sha1($new_password);

    // Update password in database
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $update_stmt->bind_param("si", $new_hashed_password, $user_id);
    
    if ($update_stmt->execute()) {
        echo "Password reset successful!";
    } else {
        echo "Error updating password.";
    }

    $update_stmt->close();
    $conn->close();
}
?>
