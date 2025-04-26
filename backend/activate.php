<?php
include_once '../_base.php';
require '../database.php';
require '../db/db_connect.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid activation link!");
}

try {
    // Fetch user from active_token table
    $stm = $_db->prepare("SELECT user_id, expire FROM active_token WHERE token_id = :token");
    $stm->execute([':token' => $token]);
    $row = $stm->fetch(PDO::FETCH_OBJ);

    if ($row) {
        $current_time = date('Y-m-d H:i:s');

        if ($row->expire < $current_time) {
            echo "Activation link expired!";
        } else {
            // Activate user
            $stm = $_db->prepare("UPDATE users SET is_active = 1 WHERE user_id = :user_id");
            $stm->execute([':user_id' => $row->user_id]);

            // Delete the used token
            $stm = $_db->prepare("DELETE FROM active_token WHERE token_id = :token");
            $stm->execute([':token' => $token]);

            echo "Account activated successfully! Redirecting...";
            redirect("../pages/member/activated.php");
            exit();
        }
    } else {
        echo "Invalid token!";
    }
} catch (PDOException $e) {
    echo "Error activating account. Please try again.";
}
?>