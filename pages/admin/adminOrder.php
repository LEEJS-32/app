<?php
// filepath: c:\Users\shenl\app\pages\admin\adminOrder.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include '../../_header.php';
ob_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch orders from the database
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Order Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            background-color: blue;
            border-radius: 5px;
        }
        .action-buttons a.delete {
            background-color: red;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle delete action
            $('body').on('click', '.delete', function(e) {
                e.preventDefault();
                var orderId = $(this).data('id');
                if (confirm('Are you sure you want to delete this order?')) {
                    $.ajax({
                        url: 'adminDeleteOrder.php',
                        type: 'GET',
                        data: { order_id: orderId },
                        success: function(response) {
                            alert('Order deleted successfully');
                            location.reload();
                        },
                        error: function() {
                            alert('Error deleting order');
                        }
                    });
                }
            });

            // Handle update action
            $('body').on('click', '.update', function(e) {
                e.preventDefault();
                var orderId = $(this).data('id');
                window.location.href = 'adminUpdateOrder.php?order_id=' + orderId;
            });
        });
    </script>
</head>
<body>
    <h1>Order Management</h1>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Products</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["order_id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["customer_name"]) . "</td>";

                    // Fetch products for this order
                    $order_id = $row["order_id"];
                    $product_sql = "SELECT * FROM order_items WHERE order_id = $order_id";
                    $product_result = $conn->query($product_sql);

                    echo "<td>";
                    if ($product_result->num_rows > 0) {
                        while ($product = $product_result->fetch_assoc()) {
                            echo htmlspecialchars($product["product_name"]) . " (x" . $product["quantity"] . ")<br>";
                        }
                    } else {
                        echo "No products";
                    }
                    echo "</td>";

                    echo "<td>$" . number_format($row["total_price"], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                    echo "<td>" . $row["created_at"] . "</td>";
                    echo "<td class='action-buttons'>";
                    echo "<a href='#' class='update' data-id='" . $row["order_id"] . "'>Update</a>";
                    echo "<a href='#' class='delete' data-id='" . $row["order_id"] . "'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No orders found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>