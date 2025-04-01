<?php
include_once '../../_base.php';
require '../../database.php';


$user = $_SESSION['user'];
$user_id = $user['user_id'];
$user_name = htmlspecialchars($user['name']);
$user_email = htmlspecialchars($user['email']);

// Fetch orders and payment details
$sql = "SELECT o.order_id, o.total_price, o.order_date, o.status, 
               p.payment_status, p.payment_method, p.amount, p.transaction_date 
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 20px;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .user-info {
            background: #007bff;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .order-row:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
        .order-items {
            display: none;
            background-color: #f9f9f9;
        }
        .toggle-arrow {
            float: right;
            font-weight: bold;
            transition: transform 0.3s;
        }
        .expanded .toggle-arrow {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Order History</h2>

    <div class="user-info">
        <p><strong>User ID:</strong> <?= $user_id; ?></p>
        <p><strong>Name:</strong> <?= $user_name; ?></p>
        <p><strong>Email:</strong> <?= $user_email; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Total Price (RM)</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Payment Method</th>
                <th>Amount Paid(RM)</th>
                <th>Transaction Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order) : ?>
                <tr class="order-row" data-order-id="<?= $order['order_id']; ?>">
                    <td><?= htmlspecialchars($order['order_id']); ?> <span class="toggle-arrow">▼</span></td>
                    <td><?= number_format($order['total_price'], 2); ?></td>
                    <td><?= $order['order_date']; ?></td>
                    <td><?= ucfirst($order['status']); ?></td>
                    <td><?= ucfirst($order['payment_status'] ?? 'N/A'); ?></td>
                    <td><?= ucfirst($order['payment_method'] ?? 'N/A'); ?></td>
                    <td><?= number_format($order['amount'], 2); ?></td>
                    <td><?= $order['transaction_date'] ?? 'N/A'; ?></td>
                </tr>
                <tr class="order-items" id="items-<?= $order['order_id']; ?>">
                    <td colspan="8">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Subtotal (RM)</th>
                                </tr>
                            </thead>
                            <tbody id="items-list-<?= $order['order_id']; ?>">
                                <!-- Items will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $(".order-row").click(function() {
        let orderId = $(this).data("order-id");
        let itemsRow = $("#items-" + orderId);
        
        if (itemsRow.is(":visible")) {
            itemsRow.hide();
            $(this).removeClass("expanded");
        } else {
            $.ajax({
                url: "fetch_order_item.php",
                type: "GET",
                data: { order_id: orderId },
                success: function(data) {
                    $("#items-list-" + orderId).html(data);
                    itemsRow.show();
                    $(".order-row[data-order-id='" + orderId + "']").addClass("expanded");
                }
            });
        }
    });
});
</script>

</body>
</html>
