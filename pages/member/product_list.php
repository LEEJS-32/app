<?php
include_once '../../_base.php';
require '../../db/db_connect.php';
include '../../_header.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user['user_id'] : 'Guest';
$user_name = $user ? $user['name'] : 'Guest';

// Fetch search query (if any)
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch products from the database based on the search query
$sql = "SELECT * FROM products WHERE status = 'active'";
if (!empty($search_query)) {
    $sql .= " AND name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}

$result = $conn->query($sql);
if (!$result) {
    die("Error fetching products: " . $conn->error);
}

// Get alert message from session if exists
$alertMessage = isset($_SESSION['cart_message']) ? $_SESSION['cart_message'] : "";
unset($_SESSION['cart_message']); // Remove message after displaying it
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="../../css/style.css"> <!-- External CSS -->

    <script>
        window.onload = function () {
            let message = document.getElementById("successMessage");
            if (message) {
                message.style.display = "block"; // Show message
                setTimeout(function () {
                    message.style.display = "none"; // Hide after 2 seconds
                }, 2000);
            }
        };
    </script>
</head>
<body>

    <!-- Success Message (Placed at the very top) -->
    <?php if (!empty($alertMessage)) { ?>
        <div id="successMessage" class="success-message"><?php echo htmlspecialchars($alertMessage); ?></div>
    <?php } ?>

    <div class="header">
        <h2>Product List</h2>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
        <p><strong>User Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <form method="GET" action="product_list.php">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- View Cart Button -->
    <div class="view-cart-container">
        <a href="view_cart.php">
            <button class="view-cart-btn">View Cart</button>
        </a>
    </div>

    <div class="product-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stock = intval($row['stock']);
                ?>
                <div class="product-card">
                    <img class="product-image" src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p>Price: $<?php echo number_format($row['price'], 2); ?></p>
                    <p>Brand: <?php echo htmlspecialchars($row['brand']); ?></p>
                    <p>Color: <?php echo htmlspecialchars($row['color']); ?></p>
                    <p><strong>Stock: <?php echo $stock; ?></strong></p>

                    <!-- Only show Add to Cart button if stock is available -->
                    <form action="add_to_cart.php" method="POST" class="product-form">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                        <?php if ($stock > 0) { ?>
                            <label>Quantity: <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock; ?>"></label>
                            <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                        <?php } else { ?>
                            <button type="button" class="out-of-stock-btn" disabled>Out of Stock</button>
                        <?php } ?>
                    </form>
                </div>
                <?php
            }
        } else {
            echo "<p class='no-products'>No products found.</p>";
        }
        ?>
    </div>

</body>
</html>

<?php $conn->close(); ?>
