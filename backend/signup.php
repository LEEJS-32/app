<?php

require_once '../_base.php';
require '../database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $hash_password = sha1($password);
    $avatar = "../../img/avatar/avatar.jpg";
    $activation_token = bin2hex(random_bytes(32)); // Generate unique activation token


    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result_check_exist = $stmt->get_result();

    if ($result_check_exist->num_rows > 0) {
        $_SESSION['error_exist'] = "Record exists.";
        header("Location: ../pages/signup_login.php");
        exit();
    } else {
        if (!$_err) {
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, avatar) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hash_password, $avatar);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                // Generate Activation Token
                $activation_token = bin2hex(random_bytes(32));
                $expiry_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                $stmt = $conn->prepare("INSERT INTO active_token (token_id, user_id, expire) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $activation_token, $user_id, $expiry_time);
                $stmt->execute();

            $url = base("backend/activate.php?token=$activation_token");
            $m = get_mail();
            $m->addAddress($email);
            $m->Subject = "Account Activation";
            $m->Body    = "
                            <p>Dear $name,</p>
                            <h1>Account Activation</h1>
                            <p>Please click the button below to activate your account.</p>
                            <a href='$url'><button>Activate your account</button></a>
                            <br>
                            <p>Regards,<br>
                            Furniture.os
                            </p>";
            $m->isHTML(true);
            $m->send();

            temp('info', 'Email sent');
            redirect('../pages/signup_login.php');
        }
    }
}
?>



