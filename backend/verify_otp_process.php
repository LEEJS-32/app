<?php
require '../_base.php';
require '../database.php';

$otp = $_POST['otp'] ?? '';
$email = $_SESSION['otp_email'] ?? '';

$stmt = $conn->prepare("SELECT * FROM verify_otp WHERE email = ? AND otp_code = ? AND expire_at > NOW()");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['reset_email'] = $email;
    redirect("../pages/reset_password.php");
} else {
    echo "Invalid or expired OTP";
}
?>
