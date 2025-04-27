<?php
session_start();
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit();
}
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Furniture.os</title>
    <link rel="stylesheet" href="../css/password.css">
</head>
<body>
    <div class="password-container">
        <h2>Reset Password</h2>
        <form method="post" action="../backend/forgot_reset_password.php" class="password-form">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" required 
                       minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                       title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Reset Password</button>
            <?php if ($message): ?>
                <div class="message error-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
        </form>
        <div class="back-link">
            <a href="signup_login.php">Back to Login</a>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
