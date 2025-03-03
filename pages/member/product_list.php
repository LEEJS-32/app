<?php
session_start();
include '../../database.php'; // Adjust the path based on your folder structure

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    die("You are not logged in. <a href='../signup_login.php'>Login here</a>");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Check if database connection is successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$sql = "SELECT * FROM products WHERE status = 'active'";
$result = $conn->query($sql);

// Debugging: Check if query executes successfully
if (!$result) {
    die("Error fetching products: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <style>
        .header {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            text-align: center;
        }
        .product {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px;
            width: 220px;
            display: inline-block;
            text-align: center;
            border-radius: 8px;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
        }
        img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <!-- Show logged-in user info -->
    <div class="header">
        <h2>Product List</h2>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
        <p><strong>User Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
    </div>

    <?php
    // Debugging: Check number of products found
    echo "<p>Number of products found: " . $result->num_rows . "</p>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='product'>";
            echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
            echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
            echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
            echo "<p>Brand: " . htmlspecialchars($row['brand']) . "</p>";
            echo "<p>Color: " . htmlspecialchars($row['color']) . "</p>";
            echo "<form action='add_to_cart.php' method='POST'>";
            echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>";
            echo "<label>Quantity: <input type='number' name='quantity' value='1' min='1'></label>";
            echo "<button type='submit'>Add to Cart</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<p>No products available.</p>";
    }
    ?>

</body>
</html>

<?php
$conn->close();
?>
