<!DOCTYPE html>
<html>
<?php
    require '../database.php';
    include '../_base.php';
    session_start();
    $role = $_SESSION['role'];
    echo "$role";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = post("email");
        $password = post("password");
        $hash_password = sha1($password);
        $remember = isset($_POST['remember']);
        echo "$remember";

        // Check if user exists
        $sql_check_exist = "SELECT * FROM users WHERE email = '$email' AND role = '$role'";
        $result_check_exist = $conn->query($sql_check_exist);

<<<<<<< Updated upstream
        if ($result_check_exist->num_rows == 0) {
            $_SESSION['error_not_exist'] = 'Record not exists.';
            if ($role == "member") {
                redirect("../pages/signup_login.php");
                exit();
            } else {
                echo "Record not exists";
            }
        } else {
            // Check password
            $sql_check_pwd = "SELECT * FROM users WHERE email = '$email' AND password = '$hash_password' AND role = '$role'";
            $result_check_pwd = $conn->query($sql_check_pwd);
=======
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user"] = $user; // Store in session

        if ($remember) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+7 days")); // Valid for 7 days

            // Store token in database
            $stmt = $conn->prepare("INSERT INTO token (user_id, token_id, expire) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $token, $expiry);
            $stmt->execute();
>>>>>>> Stashed changes

                // Redirect to product_list.php after login
                if ($role == "member") {
                    redirect("../pages/member/product_list.php");
                    exit();
                } else {
                    redirect("/../pages/member/profile.php");
                    exit();
                }
            } else {
                echo "Incorrect password.";
                $_SESSION['error_pwd'] = 'Incorrect Password.';
                if ($role == "member") {
                    redirect("../pages/signup_login.php");
                    exit();
                } else {
                    echo "Incorrect password.";
                }
            }
        }
<<<<<<< Updated upstream
=======

        // Redirect based on role
        $redirect_url = ($user['role'] == "admin") ? "../pages/admin/admin_profile.php" : "../pages/member/member_profile.php";
        redirect($redirect_url);
    } else {
        $_SESSION["error"] = "Invalid email or password.";
        echo "Invalid email or password.";
>>>>>>> Stashed changes
    }
?>
</html>
