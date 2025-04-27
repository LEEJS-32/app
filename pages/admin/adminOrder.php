<?php
include_once '../../_base.php';

auth_user();
auth('admin');

$new_user = false;
$_SESSION["new_user"] = $new_user;

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;

try {
    // If the form has been submitted to update order status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
        // Update order status
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];

        $stm = $_db->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
        $stm->execute([
            ':status' => $new_status,
            ':order_id' => $order_id
        ]);

        // Redirect to avoid form resubmission and ensure updated data is fetched
        header("Location: adminOrder.php");
        exit();
    }

    // Fetch all orders
    $sql = "SELECT o.order_id, o.status, o.total_price, o.order_date, u.name AS user_name
            FROM orders o
            JOIN users u ON o.user_id = u.user_id";
    $stm = $_db->query($sql);
    $orders = $stm->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Orders</title>
    <link rel="stylesheet" href="../../css/adminOrder.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
</head>

<body>
<div id="info"><?= temp('info') ?></div>
<header>
    <?php include '../../_header.php'; ?>
</header>

<main>
    <div class="container">
    <div class="left">
            <div class="profile">
                <img src="../../img/avatar/<?= htmlspecialchars($imageUrl) ?>" alt="Profile" class="profile-avatar" />
                <div class="profile-text">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p><?php echo htmlspecialchars($role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="member_profile.php"><i class='bx bxs-user-detail' ></i>My Profile</a></li>
                <li><a href="reset_password.php" class="active"><i class='bx bx-lock-alt' ></i>Password </a></li>
                <li><a href="member_address.php"><i class='bx bx-home-alt-2' ></i>My Address</a></li>
                <li><a href="view_cart.php"><i class='bx bx-shopping-bag' ></i>Shopping Cart</a></li>
                <li><a href="#"><i class='bx bx-heart' ></i>Wishlist</a></li>
                <li><a href="adminOrder.php"><i class='bx bx-food-menu'></i>My Orders</a></li>
            </ul>
        </div>
        <div class="divider"></div>

        <div class="right">
            <h2>Manage Orders</h2>

            <!-- Orders Table -->
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User Name</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order->order_id) ?></td>
                            <td><?= htmlspecialchars($order->user_name) ?></td>
                            <td>RM <?= number_format($order->total_price, 2) ?></td>
                            <td><?= htmlspecialchars($order->status) ?></td>
                            <td><?= htmlspecialchars($order->order_date) ?></td>
                            <td>
                                <!-- Form to change order status -->
                                <form action="adminOrder.php" method="POST" style="display:inline;">
                                    <select name="status" class="status-dropdown">
                                        <option value="pending" <?= $order->status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $order->status == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $order->status == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $order->status == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order->status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="order_id" value="<?= $order->order_id ?>">
                                    <button type="submit" class="btn update-btn">Update</button>
                                </form>

                                <!-- View Order Details Link -->
                                <a href="adminOrder.php?view=<?= $order->order_id ?>" class="view-link">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
            // If the admin is viewing an order's details
            if (isset($_GET['view'])) {
                $order_id = $_GET['view'];

                try {
                    // Fetch order details
                    $stm = $_db->prepare("SELECT * FROM orders WHERE order_id = :order_id");
                    $stm->execute([':order_id' => $order_id]);
                    $order = $stm->fetch(PDO::FETCH_OBJ);

                    // Check if order exists
                    if (!$order) {
                        echo "<p>Error: Order not found.</p>";
                        exit;
                    }

                    // Fetch the items in the order
                    $stm = $_db->prepare("SELECT oi.product_id, oi.quantity, oi.subtotal, p.name AS product_name
                                        FROM order_items oi
                                        JOIN products p ON oi.product_id = p.product_id
                                        WHERE oi.order_id = :order_id");
                    $stm->execute([':order_id' => $order_id]);
                    $order_items = $stm->fetchAll(PDO::FETCH_OBJ);

                    // Check if there are order items
                    if (empty($order_items)) {
                        echo "<p>No items found for this order.</p>";
                        exit;
                    }

                    // Fetch payment information
                    $stm = $_db->prepare("SELECT * FROM payments WHERE order_id = :order_id");
                    $stm->execute([':order_id' => $order_id]);
                    $payment = $stm->fetch(PDO::FETCH_OBJ);

                    // Check if payment information is found
                    if (!$payment) {
                        echo "<p>Payment information not found for this order.</p>";
                        exit;
                    }
                    ?>

                    <h2 class="details">Order Details - <?= htmlspecialchars($order->order_id) ?></h2>

                    <div class="order-details">
                        <div class="order-summary">
                            <h4>Order Summary</h4>
                            <p><strong>Status:</strong> <?= htmlspecialchars($order->status) ?></p>
                            <p><strong>Total Price:</strong> RM <?= number_format($order->total_price, 2) ?></p>
                            <p><strong>Shipping Address:</strong></p>
                            <p><?= htmlspecialchars($order->shipping_address_line1) ?><br>
                            <?= htmlspecialchars($order->shipping_address_line2) ?><br>
                            <?= htmlspecialchars($order->shipping_city) ?>, <?= htmlspecialchars($order->shipping_state) ?><br>
                            <?= htmlspecialchars($order->shipping_postal_code) ?>, <?= htmlspecialchars($order->shipping_country) ?></p>
                        </div>

                        <div class="order-items">
                            <h4>Order Items</h4>
                            <table class="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item->product_name) ?></td>
                                            <td><?= htmlspecialchars($item->quantity) ?></td>
                                            <td>RM <?= number_format($item->subtotal, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="payment-info">
                            <h4>Payment Information</h4>
                            <p><strong>Payment Method:</strong> <?= htmlspecialchars($payment->payment_method) ?></p>
                            <p><strong>Payment Status:</strong> <?= htmlspecialchars($payment->payment_status) ?></p>
                            <p><strong>Amount Paid:</strong> RM <?= number_format($payment->amount, 2) ?></p>
                        </div>
                    </div>
                    <!-- Close Order Details Button -->
                    <button class="close-btn" onclick="closeOrderDetails()">Close Details</button>

                <?php } catch (PDOException $e) {
                    die("Error: " . $e->getMessage());
                }
            } ?>
        </div>
    </div>
</main>

<footer>
    <?php include '../../_footer.php'; ?>
</footer>

<script>
    // Function to close the order details view
    function closeOrderDetails() {
        window.location.href = "adminOrder.php"; // Redirect back to the main orders page
    }
</script>

</body>
</html>
