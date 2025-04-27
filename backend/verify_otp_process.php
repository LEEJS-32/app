<?php
require '../_base.php';
require '../database.php';

$otp = $_POST['otp'] ?? '';
$email = $_SESSION['otp_email'] ?? '';

try {
    $stm = $_db->prepare("SELECT * FROM verify_otp WHERE email = :email AND otp_code = :otp AND expire_at > NOW()");
    $stm->execute([
        ':email' => $email,
        ':otp' => $otp
    ]);
    $result = $stm->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $_SESSION['reset_email'] = $email;
        redirect("../pages/reset_password.php");
    } else {
        temp('info', 'Invalid or expired OTP');
        header('Location: ../pages/verify_otp.php'); // Redirect to another page
        exit();
    }
} catch (PDOException $e) {
    echo "Error verifying OTP. Please try again.";
}
?>
