<?php
include 'db_connect.php'; // Include the database connection file

$name = "wwww";
$email = "wwww@wwww";
$pwd = "Ww12@1212";
$role = "admin";
$avatar = "../../img/avatar/avatar.jpg";

// SQL Insert Query
$sql_insert_admin = "INSERT IGNORE INTO users (name, email, password, role, avatar) VALUES ('$name', '$email', '$pwd', '$role', '$avatar')";

if ($conn->query($sql_insert_admin) === TRUE) {
    echo "Admin data inserted successfully";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>