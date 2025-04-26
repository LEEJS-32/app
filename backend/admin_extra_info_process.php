<?php
require_once '../_base.php';
require_once '../db/db_connect.php';

auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;

$name = post('name');
$email = post('email');
$phonenum = post('phonenum');
$dob = post('dob');
$gender = post('gender');

try {
    $stm = $_db->prepare("UPDATE users SET name = :name, email = :email, phonenum = :phonenum, dob = :dob, gender = :gender WHERE user_id = :user_id");
    $stm->execute([
        ':name' => $name,
        ':email' => $email,
        ':phonenum' => $phonenum,
        ':dob' => $dob,
        ':gender' => $gender,
        ':user_id' => $user_id
    ]);

    // Update session
    $_SESSION['user']->name = $name;
    $_SESSION['user']->email = $email;
    $_SESSION['user']->phonenum = $phonenum;
    $_SESSION['user']->dob = $dob;
    $_SESSION['user']->gender = $gender;

    temp('info', 'Profile updated successfully.');
    header("Location: ../pages/admin/admin_edit_profile.php");
    exit();
} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    temp('error', 'Failed to update profile.');
    header("Location: ../pages/admin/admin_edit_profile.php");
    exit();
}
?> 