<?php
include_once '../_base.php';
require '../db/db_connect.php';

//member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $avatar = $_FILES['avatar'] ?? null;

    // Validate input
    if (empty($name) || empty($email)) {
        temp('info', 'All fields are required.');
        redirect("../pages/admin/admin_edit_profile.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        temp('info', 'Please enter a valid email address.');
        redirect("../pages/admin/admin_edit_profile.php");
        exit;
    }

    try {
        // Check if email is already taken by another user
        $stm = $_db->prepare("SELECT user_id FROM users WHERE email = :email AND user_id != :user_id");
        $stm->execute([
            ':email' => $email,
            ':user_id' => $user_id
        ]);
        
        if ($stm->fetch()) {
            temp('info', 'This email is already taken by another user.');
            redirect("../pages/admin/admin_edit_profile.php");
            exit;
        }

        // Handle avatar upload
        $avatar_path = null;
        if ($avatar && $avatar['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($avatar['type'], $allowed_types)) {
                temp('info', 'Invalid file type. Please upload a JPEG, PNG, or GIF image.');
                redirect("../pages/admin/admin_edit_profile.php");
                exit;
            }

            if ($avatar['size'] > $max_size) {
                temp('info', 'File is too large. Maximum size is 5MB.');
                redirect("../pages/admin/admin_edit_profile.php");
                exit;
            }

            // Generate unique filename
            $extension = pathinfo($avatar['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $upload_dir = __DIR__ . '/../img/avatar/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $avatar_path = 'img/avatar/' . $filename;
            $target_path = $upload_dir . $filename;

            if (!move_uploaded_file($avatar['tmp_name'], $target_path)) {
                temp('info', 'Error uploading avatar.');
                redirect("../pages/admin/admin_edit_profile.php");
                exit;
            }

            // Update avatar in database
            $stm = $_db->prepare("UPDATE users SET avatar = :avatar WHERE user_id = :user_id");
            $stm->execute([
                ':avatar' => $avatar_path,
                ':user_id' => $user_id
            ]);
        }

        // Update name and email
        $stm = $_db->prepare("UPDATE users SET name = :name, email = :email WHERE user_id = :user_id");
        $stm->execute([
            ':name' => $name,
            ':email' => $email,
            ':user_id' => $user_id
        ]);

        // Update session
        $_SESSION['user']->name = $name;
        $_SESSION['user']->email = $email;
        if ($avatar_path) {
            $_SESSION['user']->avatar = $avatar_path;
        }

        temp('info', 'Profile updated successfully!');
        redirect("../pages/admin/admin_profile.php");
        exit();
    } catch (PDOException $e) {
        temp('info', 'Error updating profile. Please try again.');
        redirect("../pages/admin/admin_edit_profile.php");
    }
}
?> 