<?php
require '../../database.php';

if (!isset($_GET['order_id'])) {
    die("Invalid request.");
}

$order_id = $_GET['order_id'];

// Fetch order items along with product image
$sql = "SELECT p.name, p.image_url, oi.quantity, oi.subtotal 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Decode the JSON image_url to get the image URL
        $image_urls = json_decode($row['image_url']);
        $image_url = $image_urls[0]; // Assuming the first image URL is the one to display
        
        echo "<tr>
                <td>
                    <div>" . htmlspecialchars($row['name']) . "</div> <!-- Product Name -->
                    <div><img src='/" . htmlspecialchars($image_url) . "' alt='" . htmlspecialchars($row['name']) . "' style='width: 50px; height: 50px; object-fit: cover;'></div> <!-- Product Image -->
                </td>
                <td>" . $row['quantity'] . "</td>
                <td>" . number_format($row['subtotal'], 2) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No items found.</td></tr>";
}

$stmt->close();
?>
