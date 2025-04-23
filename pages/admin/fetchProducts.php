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

// Get parameters
$column = isset($_GET['column']) ? $_GET['column'] : 'product_id';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Validate column and order to prevent SQL injection
$allowed_columns = ['product_id', 'name', 'price', 'stock', 'category', 'status', 'discount', 'discounted_price', 'created_at', 'updated_at'];
$allowed_order = ['asc', 'desc'];

if (!in_array($column, $allowed_columns)) {
    $column = 'product_id';
}
if (!in_array($order, $allowed_order)) {
    $order = 'desc';
}

// Initialize variables for prepared statement
$params = [];
$types = ""; // Initialize $types as an empty string

// Build the base SQL query
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

// Add search filter
if (!empty($search)) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

// Add category filter
if ($category > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Add status filter
if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Add sorting
$sql .= " ORDER BY $column $order";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Output the results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["product_id"] . "</td>";
        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
        echo "<td><div class='scrollable-description'>" . htmlspecialchars($row["description"]) . "</div></td>";
        echo "<td>" . $row["price"] . "</td>";
        echo "<td>" . $row["stock"] . "</td>";
        echo "<td>" . htmlspecialchars($row["category_name"]) . "</td>";

        // Decode the JSON-encoded image URLs
        $image_urls = json_decode($row["image_url"]);
        echo "<td>";
        if (is_array($image_urls)) {
            foreach ($image_urls as $image_url) {
                echo "<img src='/" . htmlspecialchars($image_url) . "' alt='Product Image' style='max-width: 100px; max-height: 100px; margin: 5px;'>";
            }
        }
        echo "</td>";

        echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
        echo "<td>" . $row["discount"] . "</td>";
        echo "<td>" . $row["discounted_price"] . "</td>";
        echo "<td>" . htmlspecialchars($row["brand"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["color"]) . "</td>";
        echo "<td>" . $row["rating"] . "</td>";
        echo "<td>" . $row["reviews_count"] . "</td>";
        echo "<td>" . $row["created_at"] . "</td>";
        echo "<td>" . $row["updated_at"] . "</td>";
        echo "<td class='action-buttons'>";
        echo "<a href='#' class='update' data-id='" . $row["product_id"] . "'>Update</a>";
        echo "<a href='#' class='disable' data-id='" . $row["product_id"] . "'>Disable</a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='18'>No products found</td></tr>";
}

$stmt->close();
$conn->close();
?>