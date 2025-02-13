<!DOCTYPE html>
<html>

<?php
    require 'database.php';

    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        

        $sql_check_exist = "SELECT email from users where email = '$email'";
        $result_check_exist = $conn->query($sql_check_exist);
        if ($result_check_exist->num_rows > 0) {
            $_SESSION['error_exist'] = "Record exists.";
            header("Location: testing1.php");
            exit();
        } 
        else {
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
            $result_insert = $conn->query($sql);
            if ($result_insert == TRUE){
                echo "Signup successful<br>";
            }
        }
    }

?>

</html>