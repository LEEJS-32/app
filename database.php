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

// create connection
$conn = new mysqli($servername, $username, $password, $database);

#table:users
// SQL to create users table
$sql_create_users_table = "CREATE TABLE if not exists USERS (
name VARCHAR(100) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL PRIMARY KEY,
password VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_users_table) === TRUE) {
    echo "Table users created successfully\n";
} else {
    echo "Error creating users table: " . $conn->error . "\n";
}

#table:products
// SQL to create products table
$sql_create_products_table = "CREATE TABLE IF NOT EXISTS products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    category VARCHAR(50),
    image_url VARCHAR(255),
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    discount DECIMAL(5, 2) DEFAULT 0.00,
    brand VARCHAR(50),
    rating DECIMAL(3, 2) DEFAULT 0.00,
    reviews_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_products_table) === TRUE) {
    echo "Table products created successfully\n";
} else {
    echo "Error creating products table: " . $conn->error . "\n";
}


$conn->close();
?>