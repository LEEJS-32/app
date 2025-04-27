<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "TESTING1";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername", $username, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    
    // Select the database
    $pdo->exec("USE $database");
    
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
        modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1
    )";
    $pdo->exec($sql_create_users_table);

    // Create token table
    $sql_create_token_table = "CREATE TABLE IF NOT EXISTS token (
        token_id VARCHAR(100) PRIMARY KEY, 
        expire DATETIME NOT NULL, 
        user_id INT NOT NULL, 
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_create_token_table);

    
    // Create categories table
    $sql_create_categories = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )";
    $pdo->exec($sql_create_categories);

    // Create products table
    $sql_create_products_table = "CREATE TABLE IF NOT EXISTS products (
        product_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        stock INT NOT NULL,
        image_url VARCHAR(255),
        status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
        discount DECIMAL(5, 2) DEFAULT 0.00,
        discounted_price DECIMAL(10, 2) DEFAULT 0.00,
        brand VARCHAR(50),
        color VARCHAR(50),
        rating DECIMAL(3, 2) DEFAULT 0.00,
        reviews_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        video_url VARCHAR(255) NULL,
        category_id INT,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql_create_products_table);

    // Create active_token table
    $sql_create_active_token_table = "CREATE TABLE IF NOT EXISTS active_token (
        token_id VARCHAR(64) PRIMARY KEY, 
        expire DATETIME NOT NULL, 
        user_id INT NOT NULL, 
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_create_active_token_table);

    // Create verify_otp table
    $sql_create_verify_otp_table = "CREATE TABLE IF NOT EXISTS verify_otp (
        verify_id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        otp_code VARCHAR(10) NOT NULL,
        expire_at DATETIME NOT NULL
    )";
    $pdo->exec($sql_create_verify_otp_table);

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
    $pdo->exec($sql_create_cart_table);

    // Create vouchers table
    $sql_create_vouchers_table = "CREATE TABLE IF NOT EXISTS vouchers (
        voucher_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('rm', 'percent') NOT NULL,
        value DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        description TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )";
    $pdo->exec($sql_create_vouchers_table);

    // Create address table
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
    $pdo->exec($sql_create_address_table);

    // Create orders table
    $sql_create_orders_table = "CREATE TABLE IF NOT EXISTS orders (
        order_id VARCHAR(50) PRIMARY KEY,
        user_id INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
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
    $pdo->exec($sql_create_orders_table);

    // Create order_items table
    $sql_create_order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
        order_item_id INT PRIMARY KEY AUTO_INCREMENT,
        order_id VARCHAR(50) NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_create_order_items_table);

    // Create payments table
    $sql_create_payments_table = "CREATE TABLE IF NOT EXISTS payments (
        payment_id INT PRIMARY KEY AUTO_INCREMENT,
        order_id VARCHAR(50) NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('ToyyibPay') NOT NULL,
        payment_status ENUM('Pending', 'Completed', 'Failed','Refunded') DEFAULT 'Pending',
        transaction_id VARCHAR(100) NOT NULL UNIQUE,
        bill_code VARCHAR(100) NOT NULL,
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_create_payments_table);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Keep connection open for other scripts to use
?> 