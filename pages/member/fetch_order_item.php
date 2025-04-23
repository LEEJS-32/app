<?php
require '../../database.php';

if (!isset($_GET['order_id'])) {
    die("Invalid request.");
}

$order_id = $_GET['order_id'];

// Fetch order items
$sql = "SELECT p.name, oi.quantity, oi.subtotal 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . $row['quantity'] . "</td>
                <td>" . number_format($row['subtotal'], 2) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>No items found.</td></tr>";
}

$stmt->close();
?>
