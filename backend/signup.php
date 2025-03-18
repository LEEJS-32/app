<!DOCTYPE html>
<html>

<?php
    require_once '../_base.php';
    require '../database.php';

    session_start();
    if (is_post()) {
        $name = post('name');
        $email = post('email');
        $password = post('password');
        $hash_password = sha1($password);

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
?>

</html>