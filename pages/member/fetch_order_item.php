<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get sorting parameters
$sort_column = isset($_GET['column']) ? $_GET['column'] : 'product_id';
$sort_order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'asc';

// Validate column name to prevent SQL injection
$valid_columns = ['product_id', 'name', 'description', 'price', 'stock', 'category', 'status', 'discount', 'discounted_price', 'weight', 'length', 'width', 'height', 'brand', 'color', 'rating', 'reviews_count', 'created_at', 'updated_at'];
if (!in_array($sort_column, $valid_columns)) {
    $sort_column = 'product_id';
}

// Fetch sorted products
$query = "SELECT * FROM products ORDER BY $sort_column $sort_order";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
        echo "<td>" . $row['price'] . "</td>";
        echo "<td>" . $row['stock'] . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td>";
        $image_urls = json_decode($row['image_url']);
        if (is_array($image_urls)) {
            foreach ($image_urls as $image_url) {
                echo "<img src='/" . htmlspecialchars($image_url) . "' alt='Product Image' style='max-width: 100px; max-height: 100px; margin: 5px;'>";
            }
        }
        echo "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . $row['discount'] . "</td>";
        echo "<td>" . $row['discounted_price'] . "</td>";
        echo "<td>" . $row['weight'] . "</td>";
        echo "<td>" . $row['length'] . "</td>";
        echo "<td>" . $row['width'] . "</td>";
        echo "<td>" . $row['height'] . "</td>";
        echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
        echo "<td>" . htmlspecialchars($row['color']) . "</td>";
        echo "<td>" . $row['rating'] . "</td>";
        echo "<td>" . $row['reviews_count'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['updated_at'] . "</td>";
        echo "<td>
                <a href='#' class='update' data-id='" . $row['product_id'] . "'>Update</a>
                <a href='#' class='delete' data-id='" . $row['product_id'] . "'>Delete</a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='21'>No products found</td></tr>";
}
$conn->close();
?>