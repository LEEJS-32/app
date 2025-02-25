<!DOCTYPE html>
<html>
<?php
    require '../database.php';

    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $sql_check_exist = "SELECT email from users where email = '$email'";
        $result_check_exist = $conn->query($sql_check_exist);
        if ($result_check_exist->num_rows == 0) {
            $_SESSION['error_not_exist'] = 'Record not exists.';
            header("Location: testing1.php");
            exit();
        }
        else {
            $sql_check_pwd = "SELECT * from users where email = '$email' and password = '$password'";
            $result_check_pwd = $conn->query($sql_check_pwd);
            if ($result_check_pwd->num_rows > 0) {
                $_SESSION['email'] = $email;
                echo "{$email}: Logged in";
            }
            else {
                echo "Incorrect password.";
                $_SESSION['error_pwd'] = 'Incorrect Password.';
                header("Location: testing1.php");
                exit();
            }
        }
    }
?>

</html>