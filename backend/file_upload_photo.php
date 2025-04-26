<?php
require '../_base.php';
require '../db/db_connect.php';

$user_id = $_SESSION['user']->user_id ?? null;

if (!$user_id) {
    $_SESSION['upload_error'] = "Unauthorized access.";
    header("Location: ../pages/member/member_profile.php");
    exit();
}

if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
    $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array(strtolower($ext), $allowed_ext)) {
        $_SESSION['upload_error'] = "Invalid image type.";
        header("Location: ../pages/member/member_profile.php");
        exit();
    }

    $newName = md5(uniqid() . time()) . '.' . strtolower($ext);
    $targetDir = __DIR__ . "/../img/avatar/"; 
    $targetFile = $targetDir . $newName;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!is_writable($targetDir)) {
        die("Upload folder is not writable.");
    }

    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
        try {
            $stm = $_db->prepare("UPDATE users SET avatar = :avatar WHERE user_id = :user_id");
            $stm->execute([
                ':avatar' => $newName,
                ':user_id' => $user_id
            ]);

            $_SESSION['user']->avatar = $newName;
            $_SESSION['upload_success'] = "Profile photo updated successfully.";
        } catch (PDOException $e) {
            $_SESSION['upload_error'] = "Failed to update database.";
        }
    } else {
        $_SESSION['upload_error'] = "Failed to save file.";
    }
} else {
    $_SESSION['upload_error'] = "No file uploaded.";
}

header("Location: ../pages/member/member_profile.php");
exit();
