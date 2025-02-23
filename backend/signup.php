<!DOCTYPE html>
<html>

<?php
    require_once '../_base.php';
    require '../database.php';

    session_start();
    if (is_post()) {
        $name = post('name');
        $email = post('email');
        $password = post('password');
        

        $sql_check_exist = "SELECT email from users where email = '$email'";
        $result_check_exist = $conn->query($sql_check_exist);
        if ($result_check_exist->num_rows > 0) {
            $_SESSION['error_exist'] = "Record exists.";
            header("Location: ../pages/signup_login.php");
            exit();
        } 
        else {
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
            $result_insert = $conn->query($sql);
            if ($result_insert){
                echo "Signup successful<br>";
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $name;
                header("Location: ../pages/extra_info.php");
            }
        }
    }

?>

</html>