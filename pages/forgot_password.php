<?php
require '../_base.php';
auth_user();
require '../database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $name = $row['name'];

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expire = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Store OTP
        $stmt = $conn->prepare("INSERT INTO verify_otp (otp_code, email, expire_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $otp, $email, $expire);
        $stmt->execute();

        // Send OTP email
        $mail = get_mail();
        $mail->addAddress($email);
        $mail->Subject = "Password Reset OTP";
        $mail->isHTML(true);
        $mail->Body = "
            <p>Hello <strong>$name</strong>,</p>
            <p>Your OTP for password reset is:</p>
            <h2>$otp</h2>
            <p>This OTP will expire in 5 minutes.</p>
            <p>Furniture.os</p>";

        if ($mail->send()) {
            $_SESSION['otp_email'] = $email;
            header("Location: verify_otp.php");
            exit();
        } else {
            $message = "Failed to send email. Please try again.";
        }
    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Furniture.os</title>
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
    <div class="password-reset-container">
        <h2>Forgot Password</h2>
        <form method="post" class="password-reset-form">
            <div class="form-group">
                <label for="email">Enter your email address</label>
                <input type="email" name="email" id="email" required placeholder="your@email.com">
            </div>
            <button type="submit">Send OTP</button>
            <?php if ($message): ?>
                <div class="message error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
