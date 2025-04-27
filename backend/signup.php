<?php

require_once '../_base.php';
require_once '../db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $errors = [];

    // Name validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters long';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $errors['name'] = 'Name can only contain letters and spaces';
    }

    // Email validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Check if email already exists
        try {
            $stm = $_db->prepare("SELECT email FROM users WHERE email = :email");
            $stm->execute([':email' => $email]);
            if ($stm->fetch()) {
                $errors['email'] = 'Email already exists';
            }
        } catch (PDOException $e) {
            $errors['general'] = "Database error occurred. Please try again.";
        }
    }

    // Password validation
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)';
    }

    if (empty($errors)) {
        try {
            // Hash the password
            $hash_password = sha1($password);
            $avatar = "../../img/avatar/avatar.jpg";
            $activation_token = bin2hex(random_bytes(32)); // Generate unique activation token
            $role = 'member';

            // Insert user into database
            $stm = $_db->prepare("INSERT INTO users (name, email, password, avatar, role, is_active) VALUES (:name, :email, :password, :avatar, :role, :is_active)");
            $stm->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hash_password,
                ':avatar' => $avatar,
                ':role' => $role, 
                ':is_active' => 0
            ]);

            $user_id = $_db->lastInsertId();

            // Generate Activation Token
            $expiry_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $stm = $_db->prepare("INSERT INTO active_token (token_id, user_id, expire) VALUES (:token, :user_id, :expire)");
            $stm->execute([
                ':token' => $activation_token,
                ':user_id' => $user_id,
                ':expire' => $expiry_time
            ]);

            // Send activation email
            $url = base("backend/activate.php?token=$activation_token");
            $m = get_mail();
            $m->addAddress($email);
            $m->Subject = "Account Activation";
            $m->isHTML(true);
            $m->Body = "
                <p>Dear $name,</p>
                <h1>Account Activation</h1>
                <p>Please click the button below to activate your account.</p>
                <a href='$url'><button>Activate your account</button></a>
            ";

            if ($m->send()) {
                temp('info', 'Registration successful! Please check your email to activate your account.');
                redirect("../pages/signup_login.php");
            } else {
                temp('info', 'Failed to send activation email. Please try again.');
                redirect("../pages/signup_login.php");
            }
        } catch (PDOException $e) {
            $errors['general'] = "Registration failed. Please try again.";
        }
    }

    // Store errors and form data in session
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    redirect("../pages/signup_login.php");
}
?>



