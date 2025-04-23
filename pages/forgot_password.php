<?php
require '../_base.php';
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
            header("Location: verify_otp.php"); // redirect to OTP verification page
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
    <title>Forgot Password</title>
    <style>
        body { font-family: Arial; display: flex; flex-direction: column; align-items: center; margin-top: 50px; }
        form { max-width: 400px; width: 100%; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input[type="email"], button { width: 100%; padding: 10px; margin-top: 10px; font-size: 16px; }
        .message { color: red; margin-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="post">
        <label for="email">Enter your email:</label>
        <input type="email" name="email" required>
        <button type="submit">Send OTP</button>
        <?php if ($message): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
    </form>
</body>
</html>
