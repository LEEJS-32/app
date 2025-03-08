<?php
session_start();
session_destroy();

include '../_base.php';
// Remove token from database
require __DIR__.'/../db/db_connect.php';
if (isset($_COOKIE['remember_me'])) {
    $stmt = $conn->prepare("DELETE FROM token WHERE token_id = ?");
    $stmt->execute([$_COOKIE['remember_me']]);

    setcookie("remember_me", "", time() - 3600, "/"); // Expire cookie
}

echo "Logged out";
redirect('../index.php');
?>
