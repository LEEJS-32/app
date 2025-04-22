<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$column = isset($_GET['column']) ? $_GET['column'] : 'product_id';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM products WHERE name LIKE '%$search%' ORDER BY $column $order";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["product_id"] . "</td>";
        echo "<td>" . $row["name"] . "</td>";
        echo "<td><div class='scrollable-description'>" . htmlspecialchars($row["description"]) . "</div></td>";        echo "<td>" . $row["price"] . "</td>";
        echo "<td>" . $row["stock"] . "</td>";
        echo "<td>" . $row["category"] . "</td>";

        // Decode the JSON-encoded image URLs
        $image_urls = json_decode($row["image_url"]);
        echo "<td>";
        if (is_array($image_urls)) {
            foreach ($image_urls as $image_url) {
                echo "<img src='/" . $image_url . "' alt='Product Image' style='max-width: 100px; max-height: 100px; margin: 5px;'>";
            }
        }
        echo "</td>";

        echo "<td>" . $row["status"] . "</td>";
        echo "<td>" . $row["discount"] . "</td>";
        echo "<td>" . $row["discounted_price"] . "</td>";
        echo "<td>" . $row["brand"] . "</td>";
        echo "<td>" . $row["color"] . "</td>";
        echo "<td>" . $row["rating"] . "</td>";
        echo "<td>" . $row["reviews_count"] . "</td>";
        echo "<td>" . $row["created_at"] . "</td>";
        echo "<td>" . $row["updated_at"] . "</td>";
        echo "<td class='action-buttons'>";
        echo "<a href='#' class='update' data-id='" . $row["product_id"] . "'>Update</a>";
        echo "<a href='#' class='delete' data-id='" . $row["product_id"] . "'>Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='21'>No products found</td></tr>";
}

$conn->close();
?>