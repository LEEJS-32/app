<?php
// If session is already set, no need to check the cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    // Validate token in database
    $sql = "SELECT users.* FROM users 
            INNER JOIN token ON users.user_id = token.user_id 
            WHERE token.token_id = ? AND token.expire > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Restore session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
    } else {
        // Invalid token, clear cookie
        setcookie("remember_me", "", time() - 3600, "/");
    }
}

// If still no session, redirect to login
if (!isset($_SESSION['user_id'])) {
    echo "not authorized";
    exit();
}
?>