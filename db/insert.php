<?php
require_once 'config/database.php';

try {
    // Initialize Users table
    $users = [
        [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => sha1('Admin@123'),
            'role' => 'admin',
            'status' => 'active',
            'avatar' => '../../img/avatar/avatar.jpg'
        ],
        [
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => sha1('User@123'),
            'role' => 'member',
            'status' => 'active',
            'avatar' => '../../img/avatar/avatar.jpg'
        ]
    ];

    foreach ($users as $user) {
        // Check if user exists
        $check_stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->execute([$user['email']]);
        
        if ($check_stmt->rowCount() == 0) {
            // User doesn't exist, insert new user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, avatar) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['name'],
                $user['email'],
                $user['password'],
                $user['role'],
                $user['status'],
                $user['avatar']
            ]);
        }
    }

    // Initialize Categories table
    $categories = [
        ['name' => 'Sofas & armchairs'],
        ['name' => 'Tables & chairs'],
        ['name' => 'Storage & organisation'],
        ['name' => 'Office furniture'],
        ['name' => 'Beds & mattresses'],
        ['name' => 'Textiles'],
        ['name' => 'Rugs & mats & flooring'],
        ['name' => 'Home decoration'],
        ['name' => 'Lightning'],
        ['name' => 'gardening']
    ];

    foreach ($categories as $category) {
        // Check if category exists
        $check_stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check_stmt->execute([$category['name']]);
        
        if ($check_stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$category['name']]);
        }
    }

    // Initialize Products table with actual products from the database
    $products = [
        [
            'name' => '130.3 Oversized Cloud Sofa Couch Set for Living Room',
            'description' => '130.3 Oversized Cloud Sofa Couch Set for Living Room, Modern Convertible U Shaped Sectional Couch Large 7 Seater Chenille Corner Sofa with Ottoman for Apartment Office Spacious Space Beige\r\n1540 Pounds (698.53 kg)\r\n250.36 Pounds (113.56 kg)\r\n101.1"D x 130.3"W x 23.6"H (256.8 x 331 x 59.9 cm)',
            'price' => 6777.00,
            'stock' => 33,
            'image_url' => '["../../img/img_6807edb795c651.07013808.jpg","img/sofa1_2.jpg","img/sofa1_3.jpg"]',
            'status' => 'inactive',
            'discount' => 20.00,
            'discounted_price' => 5421.60,
            'brand' => 'Tmsan',
            'color' => 'Beige',
            'category_id' => 1,
            'video_url' => 'videos/video_68093710785031.82569274.mp4'
        ],
        [
            'name' => 'VECELO Convertible Sectional Sofa',
            'description' => 'VECELO Convertible Sectional Sofa, Modern Linen Fabric L-Shaped Couch with Reversible Chaise for Living Room/Apartment/Office, Grey\r\n680 Pounds (308.44 kg)\r\n44.81 Pounds (20.33 kg)\r\n25.19"D x 73.82"W x 29.53"H (64 x 187.5 x 75 cm)',
            'price' => 2400.00,
            'stock' => 6,
            'image_url' => '["../../img/img_6807ee1fae4b71.54256582.jpg"]',
            'status' => 'active',
            'discount' => 18.00,
            'discounted_price' => 1968.00,
            'brand' => 'VECELO',
            'color' => 'grey',
            'category_id' => 1
        ],
        [
            'name' => 'Modern Convertible Folding Futon Sofa Bed with Cupholders - Black',
            'description' => 'Modern Convertible Folding Futon Sofa Bed with Cupholders - Black\r\n30.5"D x 65.25"W x 31"H (77.5 x 165.7 x 78.7 cm)\r\nBlack\r\n63 Pounds (28.58 kg)',
            'price' => 1300.00,
            'stock' => 4,
            'image_url' => '["../../img/img_6807eea17984e4.37826667.jpg"]',
            'status' => 'active',
            'discount' => 37.00,
            'discounted_price' => 819.00,
            'brand' => 'BestChoiceProducts',
            'color' => 'black',
            'category_id' => 1
        ]
    ];

    foreach ($products as $product) {
        // Check if product exists
        $check_stmt = $pdo->prepare("SELECT product_id FROM products WHERE name = ?");
        $check_stmt->execute([$product['name']]);
        
        if ($check_stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url, status, discount, discounted_price, brand, color, category_id, video_url) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $product['name'],
                $product['description'],
                $product['price'],
                $product['stock'],
                $product['image_url'],
                $product['status'],
                $product['discount'],
                $product['discounted_price'],
                $product['brand'],
                $product['color'],
                $product['category_id'],
                $product['video_url'] ?? null
            ]);
        }
    }

    // Initialize Shopping Cart table
    $cart_items = [
        [
            'user_id' => 2,
            'product_id' => 1,
            'quantity' => 1
        ]
    ];

    foreach ($cart_items as $item) {
        // Check if cart item exists
        $check_stmt = $pdo->prepare("SELECT cart_id FROM shopping_cart WHERE user_id = ? AND product_id = ?");
        $check_stmt->execute([$item['user_id'], $item['product_id']]);
        
        if ($check_stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([
                $item['user_id'],
                $item['product_id'],
                $item['quantity']
            ]);
        }
    }

    echo "Initialization data inserted successfully!";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 