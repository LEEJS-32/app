<?php
session_start();
include '../../database.php'; // Adjust the path based on your folder structure

// Fetch products
$sql = "SELECT * FROM products WHERE status = 'active'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <style>
        .product {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px;
            width: 200px;
            display: inline-block;
            text-align: center;
        }
        img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <h2>Product List</h2>
    
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='product'>";
            echo "<img src='" . $row['image_url'] . "' alt='" . $row['name'] . "'>";
            echo "<h3>" . $row['name'] . "</h3>";
            echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
            echo "<p>Brand: " . $row['brand'] . "</p>";
            echo "<p>Color: " . $row['color'] . "</p>";
            echo "<form action='add_to_cart.php' method='POST'>";
            echo "<input type='hidden' name='product_id' value='" . $row['product_id'] . "'>";
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

<?php $conn->close(); ?>
