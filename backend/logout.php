<?php
session_start();
require '../db/db_connect.php';
include_once '../_base.php';

// Remove session
session_destroy();

// Remove token from database if "Remember Me" was used
if (isset($_COOKIE["remember_me"])) {
    $stmt = $conn->prepare("DELETE FROM token WHERE token_id = ?");
    $stmt->bind_param("s", $_COOKIE["remember_me"]);
    $stmt->execute();

    // Expire the cookie
    setcookie("remember_me", "", time() - 3600, "/");
}

// Redirect to login page
redirect("../pages/admin/admin_login.php");
?>

