<?php
require_once __DIR__ . "/../_base.php";

// If session is already set, no need to check the cookie
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    try {
        // Validate token in database
        $stm = $_db->prepare("SELECT users.* FROM users 
                INNER JOIN token ON users.user_id = token.user_id 
                WHERE token.token_id = :token AND token.expire > NOW()");
        $stm->execute([':token' => $token]);
        $user = $stm->fetch(PDO::FETCH_OBJ);

        if ($user) {
            // Restore session
            $_SESSION['user'] = $user;
        } else {
            // Invalid token, clear cookie
            setcookie("remember_me", "", time() - 3600, "/");
        }
    } catch (PDOException $e) {
        // Log error and clear cookie
        error_log("Auth check error: " . $e->getMessage());
        setcookie("remember_me", "", time() - 3600, "/");
    }
}

// If still no session, redirect to login
if (!isset($_SESSION['user'])) {
    echo "not authorized";
    temp('info', 'Login required!');

    exit();
}
?>