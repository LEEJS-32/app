<!-- verify_otp.php -->
 <?php
session_start();
$email = $_SESSION['otp_email'];
?>
 
<form method="post" action="../backend/verify_otp_process.php">
    <h2>Verify OTP</h2>
    <label>Enter OTP sent to your email:</label>
    <input type="text" name="otp" required>
    <input type="hidden" name="email" value="$email">
    <button type="submit">Verify</button>
</form>