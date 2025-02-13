<?php

$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else {
    echo "Server Connected Successfully<br>";
}

#database
$database = "TESTING1";
$sql_db = "CREATE DATABASE if not exists TESTING1";
$result_db = $conn->query($sql_db);
if ($result_db == TRUE) {
    echo "Database created successfully<br>";
}
else {
    echo "Error creating database: " . $conn->error;
}

#table:users
$sql_table = "CREATE TABLE if not exists USERS (
name VARCHAR(100) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL PRIMARY KEY,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn = new mysqli($servername, $username, $password, $database);
$result_table = $conn->query($sql_table);
if ($result_table == TRUE) {
    echo "Table created successfully<br>";
}