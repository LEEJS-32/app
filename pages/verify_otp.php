<!-- verify_otp.php -->
<?php
include_once '../_base.php';
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit();
}
$email = $_SESSION['otp_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify OTP - Furniture.os</title>
        <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/logo.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/password_reset.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
<div id="info"><?= temp('info')?></div>
    <div class="password-reset-container">
        <h2>Verify OTP</h2>
        <form method="post" action="../backend/verify_otp_process.php" class="password-reset-form">
            <div class="form-group">
                <label for="otp">Enter the 6-digit OTP sent to your email</label>
                <input type="text" name="otp" id="otp" required maxlength="6" pattern="[0-9]{6}" placeholder="000000">
            </div>
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>