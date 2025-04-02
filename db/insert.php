<?php
include 'db_connect.php'; // Include the database connection file

$name = "wwww";
$email = "wwww@wwww";
$pwd = "Ww12@1212";
$hash_pwd = sha1($pwd);
$role = "admin";
$avatar = "../../img/avatar/avatar.jpg";

// SQL Insert Query
$sql_insert_admin = "INSERT IGNORE INTO users (name, email, password, role, avatar, is_active) VALUES ('$name', '$email', '$hash_pwd', '$role', '$avatar', 1)";

if ($conn->query($sql_insert_admin) === TRUE) {
    // echo "Admin data inserted successfully";
} else {
    echo "Error: " . $conn->error;
}

$name = "qqqq";
$email = "qqqq@qqqq";
$pwd = "Qq12@1212";
$hash_pwd = sha1($pwd);
$role = "member";
$avatar = "../../img/avatar/avatar.jpg";

$sql_insert_member = "INSERT IGNORE INTO users (name, email, password, role, avatar, is_active) VALUES ('$name', '$email', '$hash_pwd', '$role', '$avatar', 1)";

if ($conn->query($sql_insert_member) === TRUE) {
    // echo "Member data inserted successfully";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>