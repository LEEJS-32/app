<?php
session_start();
include '../../database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    die("You are not logged in. <a href='../signup_login.php'>Login here</a>");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$sql = "SELECT * FROM products WHERE status = 'active'";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching products: " . $conn->error);
}

// Check for alert messages stored in session
$alertMessage = "";
if (isset($_SESSION['cart_message'])) {
    $alertMessage = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']); // Clear the message after showing it
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
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
    <script>
        // Show alert if there is a message from PHP session
        window.onload = function() {
            let message = "<?php echo $alertMessage; ?>";
            if (message) {
                alert(message);
            }
        };
    </script>
</head>
<body>

    <div class="header">
        <h2>Product List</h2>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
        <p><strong>User Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
    </div>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stock = intval($row['stock']);
            echo "<div class='product'>";
            echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
            echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
            echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
            echo "<p>Brand: " . htmlspecialchars($row['brand']) . "</p>";
            echo "<p>Color: " . htmlspecialchars($row['color']) . "</p>";
            echo "<p><strong>Stock: " . $stock . "</strong></p>";
            
            echo "<form action='add_to_cart.php' method='POST'>";
            echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>";
            if ($stock > 0) {
                echo "<label>Quantity: <input type='number' name='quantity' value='1' min='1' max='" . $stock . "'></label>";
                echo "<button type='submit'>Add to Cart</button>";
            } else {
                echo "<button type='button' disabled>Out of Stock</button>";
            }
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
