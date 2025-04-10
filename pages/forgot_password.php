<?php
include_once '_base.php';

// ----------------------------------------------------------------------------
// Check session/cookie
auth_user();

// ----------------------------------------------------------------------------
?>


<?php

require 'db_connection.php';

// Your SMS API settings
$SMS_API_URL = "https://api.example.com/send-sms"; 
$SMS_API_KEY = "your_sms_api_key";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['phone'])) {
        // Step 1: User enters phone number
        $phone = $_POST['phone'];

        // Check if phone exists in database
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            die("❌ Phone number not found.");
        }

        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['phone'] = $phone;
        $_SESSION['user_id'] = $user_id;

        // Send OTP via SMS
        $message = "Your OTP code is: $otp";
        $sms_data = [
            'api_key' => $SMS_API_KEY,
            'to' => $phone,
            'message' => $message
        ];

        // Send request to SMS API
        $ch = curl_init($SMS_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sms_data));
        $response = curl_exec($ch);
        curl_close($ch);

        echo "✅ OTP sent! Check your SMS.";
    } elseif (isset($_POST['otp'])) {
        // Step 2: User enters OTP
        $entered_otp = $_POST['otp'];

        if (!isset($_SESSION['otp']) || $entered_otp != $_SESSION['otp']) {
            die("❌ Invalid OTP. Try again.");
        }

        echo "✅ OTP Verified! <a href='reset_password.html'>Reset Password</a>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    
    <?php if (!isset($_SESSION['otp'])) { ?>
        <!-- Step 1: User enters phone number -->
        <form method="post">
            <label for="phone">Enter your phone number:</label><br>
            <input type="text" name="phone" required placeholder="Enter your phone number">
            <button type="submit">Send OTP</button>
        </form>
    <?php } else { ?>
        <!-- Step 2: User enters OTP -->
        <form method="post">
            <label for="otp">Enter OTP:</label><br>
            <input type="text" name="otp" required placeholder="Enter OTP">
            <button type="submit">Verify OTP</button>
        </form>
    <?php } ?>
</body>
</html>

