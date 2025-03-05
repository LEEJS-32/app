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

        // Check if user exists
        $sql_check_exist = "SELECT * FROM users WHERE email = '$email' AND role = '$role'";
        $result_check_exist = $conn->query($sql_check_exist);

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
            $sql_check_pwd = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND role = '$role'";
            $result_check_pwd = $conn->query($sql_check_pwd);

            if ($result_check_pwd->num_rows > 0) {
                $user = $result_check_pwd->fetch_assoc();
                $_SESSION['email'] = $email;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];

                // Redirect to product_list.php after login
                if ($role == "member") {
                    redirect("../pages/member/product_list.php");
                    exit();
                } else {
                    echo "{$email}: Logged in";
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
    }
?>
</html>
