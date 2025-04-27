<?php
include_once '../../_base.php';

$user = $_SESSION['user'];
$user_id = $user->user_id;
$user_role = $user->role;
$user_name = htmlspecialchars($user->name);
$user_email = htmlspecialchars($user->email);

try {
    // Fetch orders and payment details, including voucher_code
    $sql = "SELECT o.order_id, o.total_price, o.order_date, o.status, 
                   o.voucher_code, 
                   p.payment_status, p.payment_method, p.amount, p.transaction_date 
            FROM orders o
            LEFT JOIN payments p ON o.order_id = p.order_id
            WHERE o.user_id = :user_id
            ORDER BY o.order_date DESC";

    $stm = $_db->prepare($sql);
    $stm->execute([':user_id' => $user_id]);
    $orders = $stm->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $orders = [];
}
?>

<head>
    <title>Order History</title>
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
        try {
            // Fetch avatar from database
            $stm = $_db->prepare("SELECT avatar FROM users WHERE user_id = :user_id");
            $stm->execute([':user_id' => $user_id]);
            $row = $stm->fetch(PDO::FETCH_OBJ);
            
            $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
            
            // If avatar exists, update the image URL
            if ($row && !empty($row->avatar)) {
                $imageUrl = $row->avatar;
            }
        } catch (PDOException $e) {
            error_log("Error fetching avatar: " . $e->getMessage());
        }
    ?>

    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?php echo htmlspecialchars($user_name); ?></h3>
                    <p><?php echo htmlspecialchars($user_role); ?></p>
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
                <p><strong>User ID:</strong> <?= htmlspecialchars($user_id); ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($user_name); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_email); ?></p>
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
                        <th>Voucher Code</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order) : ?>
                        <tr class="order-row" data-order-id="<?= htmlspecialchars($order->order_id); ?>">
                            <td><?= htmlspecialchars($order->order_id); ?> <span class="toggle-arrow">▼</span></td>
                            <td><?= number_format($order->total_price, 2); ?></td>
                            <td><?= htmlspecialchars($order->order_date); ?></td>
                            <td><?= ucfirst(htmlspecialchars($order->status)); ?></td>
                            <td><?= ucfirst(htmlspecialchars($order->payment_status ?? 'N/A')); ?></td>
                            <td><?= ucfirst(htmlspecialchars($order->payment_method ?? 'N/A')); ?></td>
                            <td><?= number_format($order->amount, 2); ?></td>
                            <td><?= htmlspecialchars($order->transaction_date ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($order->voucher_code ?? 'N/A'); ?></td> 
                        </tr>
                        <tr class="order-items" id="items-<?= htmlspecialchars($order->order_id); ?>" style="display: none;">
                            <td colspan="9">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Subtotal (RM)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-list-<?= htmlspecialchars($order->order_id); ?>">
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
