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



        // Check if user already exists
        $sql_check_exist = "SELECT email FROM users WHERE email = '$email'";
        $result_check_exist = $conn->query($sql_check_exist);
        if ($result_check_exist->num_rows > 0) {
            $_SESSION['error_exist'] = "Email already registered.";
            redirect("../pages/signup_login.php");
            exit();
        }

        // Handle profile photo upload
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['upload_error'] = "Please upload a profile photo.";
            redirect("../pages/signup_login.php");
            exit();
        }

        // File upload configuration
        $target_dir = "../uploads/profile_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $new_filename = md5(uniqid() . time()) . '.' . strtolower($file_extension);
        $target_file = $target_dir . $new_filename;

        // Validate image
        $check = getimagesize($_FILES['profile_photo']['tmp_name']);
        if ($check === false) {
            $_SESSION['upload_error'] = "File is not a valid image.";
            redirect("../pages/signup_login.php");
            exit();
        }

        // Check file size (5MB max)
        if ($_FILES['profile_photo']['size'] > 5000000) {
            $_SESSION['upload_error'] = "File size exceeds 5MB limit.";
            redirect("../pages/signup_login.php");
            exit();
        }

        // Allow only specific formats
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            $_SESSION['upload_error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            redirect("../pages/signup_login.php");
            exit();
        }

        // Move uploaded file
        if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
            $_SESSION['upload_error'] = "Error uploading file.";
            redirect("../pages/signup_login.php");
            exit();
        }

        // Insert user data into database
        $sql = "INSERT INTO users (name, email, password, profile_photo) 
                VALUES ('$name', '$email', '$hash_password', '$new_filename')";
        $result_insert = $conn->query($sql);

        if ($result_insert) {
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            $_SESSION['profile_photo'] = $new_filename;
            redirect("../pages/extra_info.php");
        } else {
            // Remove uploaded file if database insert failed
            unlink($target_file);
            $_SESSION['error'] = "Registration failed. Please try again.";
            redirect("../pages/signup_login.php");
        }
    }

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



