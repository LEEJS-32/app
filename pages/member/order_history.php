<?php
include_once '../../_base.php';
require '../../database.php';

$user = $_SESSION['user'];
$user_id = $user['user_id'];
$user_role = $user['role'];
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

<head>
    <title>Member Profile</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/member/order_history.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="../../js/webcam.js"></script>
</head>

<body>
    <header>
        <?php 
            include __DIR__ . '/../../_header.php'; 
        ?>
    </header>

    <main>
    <?php
        require '../../db/db_connect.php';

        // Fetch avatar from database
        $sql = "SELECT avatar FROM users WHERE user_id = '$user_id'";
        $result = $conn->query($sql);
        $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
        // If avatar exists, update the image URL
            if (!empty($row["avatar"])) {
                $imageUrl = $row["avatar"];
            }
        }
    ?>

    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?php echo ($user_name); ?></h3>
                    <p><?php echo ($user_role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="member_profile.php"><i class='bx bxs-user-detail'></i>My Profile</a></li>
                <li><a href="reset_password.php"><i class='bx bx-lock-alt'></i>Password </a></li>
                <li><a href="member_address.php"><i class='bx bx-home-alt-2'></i>My Address</a></li>
                <li><a href="view_cart.php"><i class='bx bx-shopping-bag'></i>Shopping Cart</a></li>
                <li><a href="#"><i class='bx bx-heart'></i>Wishlist</a></li>
                <li><a href="" class="active"><i class='bx bx-food-menu'></i>My Orders</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>Orders History</h1>

            <div class="user-info">
                <p><strong >User ID:</strong> <?= $user_id; ?></p>
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
                        <tr class="order-items" id="items-<?= $order['order_id']; ?>" style="display: none;">
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
                                        <!-- Order items will be loaded here -->
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>  
    </div>

    <script>
    $(document).ready(function() {
        $(".order-row").click(function() {
            let orderId = $(this).data("order-id");
            let itemsRow = $("#items-" + orderId);
            
            if (itemsRow.is(":visible")) {
                itemsRow.hide();  // Hide the row if it's already visible
                $(this).removeClass("expanded");
                $(this).find(".toggle-arrow").text("▼");  // Change arrow back to down
            } else {
                // Fetch order items via AJAX
                $.ajax({
                    url: "fetch_order_item.php",
                    type: "GET",
                    data: { order_id: orderId },
                    success: function(data) {
                        $("#items-list-" + orderId).html(data);  // Populate order items
                        itemsRow.show();  // Show the order items row
                        $(".order-row[data-order-id='" + orderId + "']").addClass("expanded");
                        $(this).find(".toggle-arrow").text("▲");  // Change arrow to up
                    },
                    error: function() {
                        alert("Error loading order details.");
                    }
                });
            }
        });
    });
    </script>

    </main>

    <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</body>
</html>
