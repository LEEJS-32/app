<?php
include_once '../_base.php';
require '../database.php';
require '../db/db_connect.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid activation link!");
}

// Fetch user from active_token table
$stmt = $conn->prepare("SELECT user_id, expire FROM active_token WHERE token_id = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $current_time = date('Y-m-d H:i:s');

    if ($row['expire'] < $current_time) {
        echo "Activation link expired!";
    } else {
        // Activate user
        $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $row['user_id']);
        $stmt->execute();

        // Delete the used token
        $stmt = $conn->prepare("DELETE FROM active_token WHERE token_id = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo "Account activated successfully! Redirecting...";
        redirect("../pages/member/activated.php");
        exit();
    }
} else {
    echo "Invalid token!";
}
?>