<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "TESTING1";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql_db = "CREATE DATABASE IF NOT EXISTS $database";
if (!$conn->query($sql_db)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Create users table
$sql_create_users_table = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    preference TEXT NULL,
    gender ENUM('male', 'female') NULL,
    phonenum VARCHAR(20) NULL,
    dob DATE NULL,
    occupation VARCHAR(255),  
    avatar VARCHAR(255) NULL,  
    reward_pt FLOAT DEFAULT 0, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql_create_users_table);


// Check if the 'is_active' column exists in the 'users' table
$column_check_query = "SHOW COLUMNS FROM users LIKE 'is_active'";
$column_check_result = $conn->query($column_check_query);

if ($column_check_result && $column_check_result->num_rows == 0) {
    // Add the 'is_active' column if it does not exist
    $sql_add_is_active_column = "ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1";
    if ($conn->query($sql_add_is_active_column) === TRUE) {
    } else {
        $conn->error;
    }
} else {
}

// Create token table
$sql_create_token_table = "CREATE TABLE IF NOT EXISTS token (
    token_id VARCHAR(100) PRIMARY KEY, 
    expire DATETIME NOT NULL, 
    user_id INT NOT NULL, 
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
$conn->query($sql_create_token_table);

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
    -- weight DECIMAL(10, 2),
    -- length DECIMAL(10, 2),
    -- width DECIMAL(10, 2),
    -- height DECIMAL(10, 2),
    brand VARCHAR(50),
    color VARCHAR(50),
    rating DECIMAL(3, 2) DEFAULT 0.00,
    reviews_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql_create_products_table);

// Create active_token table
$sql_create_active_token_table = "CREATE TABLE IF NOT EXISTS active_token (
    token_id VARCHAR(64) PRIMARY KEY, 
    expire DATETIME NOT NULL, 
    user_id INT NOT NULL, 
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
$conn->query($sql_create_active_token_table);

//forget/reset password_otp table
$sql_create_verify_otp_table = "CREATE TABLE IF NOT EXISTS verify_otp (
    verify_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    expire_at DATETIME NOT NULL
)";
$conn->query($sql_create_verify_otp_table);

// Remember to delete !!!!
// Check if the products table exists
$table_check_query = "SHOW TABLES LIKE 'products'";
$table_check_result = $conn->query($table_check_query);

if ($table_check_result && $table_check_result->num_rows > 0) {
    // Drop columns if the table exists
    $sql_drop_columns = "ALTER TABLE products 
        DROP COLUMN IF EXISTS weight,
        DROP COLUMN IF EXISTS length,
        DROP COLUMN IF EXISTS width,
        DROP COLUMN IF EXISTS height";

    if ($conn->query($sql_drop_columns) === TRUE) {
    } else {
        echo "Error dropping columns: " . $conn->error;
    }
} else {
    echo "Table 'products' does not exist.";
}

// Add 'video_url' column if it does not exist
$column_check_query = "SHOW COLUMNS FROM products LIKE 'video_url'";
$column_check_result = $conn->query($column_check_query);

if ($column_check_result && $column_check_result->num_rows == 0) {
    $sql_add_video_url_column = "ALTER TABLE products ADD COLUMN video_url VARCHAR(255) NULL";
    if ($conn->query($sql_add_video_url_column) === TRUE) {
    } else {
        echo "Error adding column 'video_url': " . $conn->error;
    }
} else {
}

// Create shopping_cart table
$sql_create_cart_table = "CREATE TABLE IF NOT EXISTS shopping_cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)";
$conn->query($sql_create_cart_table);

// Create vouchers table
$sql_create_vouchers_table = "CREATE TABLE IF NOT EXISTS vouchers (
    voucher_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,  -- NULL = public voucher, or FK if assigned to a specific user
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('rm', 'percent') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";
$conn->query($sql_create_vouchers_table);


// Create user address table
$sql_create_address_table = "CREATE TABLE IF NOT EXISTS address (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    address_line1 VARCHAR(255) NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
$conn->query($sql_create_address_table);

// Create orders table
$sql_create_orders_table = "CREATE TABLE IF NOT EXISTS orders (
    order_id VARCHAR(50) PRIMARY KEY,  -- Changed from INT to VARCHAR(50)
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',

    
    -- Store the shipping address used for this order (not just a reference ID)
    shipping_address_line1 VARCHAR(255) NOT NULL,
    shipping_address_line2 VARCHAR(255) NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100) NOT NULL,
    shipping_postal_code VARCHAR(20) NOT NULL,
    shipping_country VARCHAR(100) NOT NULL,
    comment TEXT NULL,
    voucher_code VARCHAR(50) NULL,


    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
$conn->query($sql_create_orders_table);

// Create order_items table
$sql_create_order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id VARCHAR(50) NOT NULL,  -- Changed from INT to VARCHAR(50)
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)";
$conn->query($sql_create_order_items_table);

// Create payments table
$sql_create_payments_table = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id VARCHAR(50) NOT NULL,  -- Changed from INT to VARCHAR(50)
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Credit Card', 'PayPal', 'Bank Transfer', 'ToyyibPay') NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed','Refunded') DEFAULT 'Pending',
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    bill_code VARCHAR(100) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
$conn->query($sql_create_payments_table);


// Keep connection open for other scripts to use
?>
