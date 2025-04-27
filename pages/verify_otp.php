<!-- verify_otp.php -->
<?php
session_start();
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit();
}
$email = $_SESSION['otp_email'];
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Furniture.os</title>
    <link rel="stylesheet" href="../css/password.css">
</head>
<body>
    <div class="password-container">
        <h2>Verify OTP</h2>
        <form method="post" action="../backend/verify_otp_process.php" class="password-form">
            <div class="form-group">
                <label for="otp">Enter the 6-digit OTP sent to your email</label>
                <input type="text" name="otp" id="otp" class="otp-input" maxlength="6" required 
                       pattern="[0-9]{6}" title="Please enter a 6-digit number">
            </div>
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <button type="submit">Verify OTP</button>
            <?php if ($message): ?>
                <div class="message error-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
        </form>
        <div class="back-link">
            <a href="forgot_password.php">Back to Forgot Password</a>
        </div>
    </div>
</body>
</html>