<?php

$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Server Connected Successfully<br>";
}

// Create database
$database = "TESTING1";
$sql_db = "CREATE DATABASE if not exists TESTING1";
$result_db = $conn->query($sql_db);
if ($result_db == TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error;
}

// Create connection to the database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table
$sql_create_users_table = "CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    preference TEXT null,
    gender ENUM('male', 'female') NULL,
    phonenum VARCHAR(20) NULL,
    dob DATE NULL,
    occupation VARCHAR(255),  
    photo VARCHAR(255) NULL,  
    reward_pt FLOAT DEFAULT 0, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
)";

if ($conn->query($sql_create_users_table) === TRUE) {
    echo "Table users created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create token table
$sql_create_token_table = "CREATE TABLE IF NOT EXISTS token (
    token_id VARCHAR(100) PRIMARY KEY, 
    expire DATETIME NOT NULL, 
    user_id INT NOT NULL, 
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);";

if ($conn->query($sql_create_token_table) === TRUE) {
    echo "Table token created successfully<br>";
} else {
    echo "Error creating token table: " . $conn->error . "<br>";
}

// Create products table
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
    discounted_price DECIMAL(10, 2) DEFAULT 0.00,
    weight DECIMAL(10, 2),
    length DECIMAL(10, 2),
    width DECIMAL(10, 2),
    height DECIMAL(10, 2),
    brand VARCHAR(50),
    color VARCHAR(50),
    rating DECIMAL(3, 2) DEFAULT 0.00,
    reviews_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_products_table) === TRUE) {
    echo "Table products created successfully<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

// Add columns if they don't exist
$columns_to_add = [
    'weight DECIMAL(10, 2)',
    'length DECIMAL(10, 2)',
    'width DECIMAL(10, 2)',
    'height DECIMAL(10, 2)',
    'brand VARCHAR(50)',
    'color VARCHAR(50)'
];

foreach ($columns_to_add as $column) {
    $sql_add_column = "ALTER TABLE products ADD COLUMN IF NOT EXISTS $column";
    if ($conn->query($sql_add_column) === TRUE) {
        echo "Column $column added successfully<br>";
    } else {
        echo "Error adding column $column: " . $conn->error . "<br>";
    }
}

# Table: SHOPPING CART
$sql_create_cart_table = "CREATE TABLE IF NOT EXISTS shopping_cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES USERS(email) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)";

if ($conn->query($sql_create_cart_table) === TRUE) {
    echo "Table shopping_cart created successfully<br>";
} else {
    echo "Error creating shopping_cart table: " . $conn->error . "<br>";
}

# Table: ORDERS
$sql_create_orders_table = "CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (email) REFERENCES USERS(email) ON DELETE CASCADE
)";

if ($conn->query($sql_create_orders_table) === TRUE) {
    echo "Table orders created successfully<br>";
} else {
    echo "Error creating orders table: " . $conn->error . "<br>";
}

# Table: ORDER ITEMS
$sql_create_order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)";

if ($conn->query($sql_create_order_items_table) === TRUE) {
    echo "Table order_items created successfully<br>";
} else {
    echo "Error creating order_items table: " . $conn->error . "<br>";
}

# Table: PAYMENTS
$sql_create_payments_table = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Credit Card', 'PayPal', 'Bank Transfer') NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (email) REFERENCES USERS(email) ON DELETE CASCADE
)";

if ($conn->query($sql_create_payments_table) === TRUE) {
    echo "Table payments created successfully<br>";
} else {
    echo "Error creating payments table: " . $conn->error . "<br>";

}

//$conn->close();
?>