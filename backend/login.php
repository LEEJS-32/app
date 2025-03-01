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

        $sql_check_exist = "SELECT email from users where email = '$email' and role = '$role'";
        $result_check_exist = $conn->query($sql_check_exist);
        if ($result_check_exist->num_rows == 0) {
            $_SESSION['error_not_exist'] = 'Record not exists.';
            if ($role == "member") {
                redirect("../pages/signup_login.php");
                exit();
            }
            else {
                echo "Record not exists";
                // redirect("../pages/admin/admin_login.php");
                // exit();
            }
        }
        else {
            $sql_check_pwd = "SELECT * from users where email = '$email' and password = '$password' and role = '$role'";
            $result_check_pwd = $conn->query($sql_check_pwd);
            if ($result_check_pwd->num_rows > 0) {
                $_SESSION['email'] = $email;
                if ($role == "member") {
                    redirect("../pages/member/profile.php");
                    exit();
                }
                else {
                    echo "{$email}: Logged in";
                }
            }
            else {
                echo "Incorrect password.";
                $_SESSION['error_pwd'] = 'Incorrect Password.';
                if ($role == "member") {
                    redirect("../pages/signup_login.php");
                    exit();
                }
                else {
                    echo "Incorrect password.";
                    // redirect("../pages/admin/admin_login.php");
                    // exit();
                }
            }
        }
    }
?>

</html>