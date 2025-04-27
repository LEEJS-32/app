<?php
require '../../_base.php';
auth_user();
auth('admin');

$user_id = $_GET['id'] ?? 0;

if (!$user_id) {
    die('Invalid user ID');
}

try {
    // Fetch user details
    $stm = $_db->prepare("
        SELECT u.*, a.* 
        FROM users u 
        LEFT JOIN address a ON u.user_id = a.user_id 
        WHERE u.user_id = :user_id
    ");
    $stm->execute([':user_id' => $user_id]);
    $user = $stm->fetch(PDO::FETCH_OBJ);

    if (!$user) {
        die('User not found');
    }

    // Fetch user's orders
    $stm = $_db->prepare("
        SELECT o.*, p.payment_status, p.payment_method, p.amount 
        FROM orders o 
        LEFT JOIN payments p ON o.order_id = p.order_id 
        WHERE o.user_id = :user_id 
        ORDER BY o.order_date DESC
    ");
    $stm->execute([':user_id' => $user_id]);
    $orders = $stm->fetchAll(PDO::FETCH_OBJ);

    // Fetch user's vouchers
    $stm = $_db->prepare("
        SELECT * FROM vouchers 
        WHERE user_id = :user_id 
        ORDER BY start_date DESC
    ");
    $stm->execute([':user_id' => $user_id]);
    $vouchers = $stm->fetchAll(PDO::FETCH_OBJ);

    

} catch (PDOException $e) {
    error_log("Error fetching user details: " . $e->getMessage());
    die('Error loading user details');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Details - <?= htmlspecialchars($user->name) ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/style2.css">
    <link rel="stylesheet" href="../../css/admin/member_detail.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <?php include __DIR__ . '/../../_header.php'; ?>
    </header>

    <main>
        <div class="container">
            <div class="left">
                <div class="profile">
                    <img src="../../img/avatar/<?= htmlspecialchars($user->avatar) ?>" alt="Profile" class="profile-avatar" />
                    <div class="profile-text">
                        <h3><?= htmlspecialchars($user->name) ?></h3>
                        <p><?= htmlspecialchars($user->role) ?></p>
                    </div>
                </div>

                <ul class="nav">
                    <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
                    <li><a href="admin_members.php" class="active"><i class='bx bxs-user-account'></i>Members</a></li>
                    <li><a href="adminProduct.php"><i class='bx bx-chair'></i>Products</a></li>
                    <li><a href="adminOrder.php"><i class='bx bx-food-menu'></i>Orders</a></li>
                    <hr>
                    <li><a href="admin_edit_profile.php"><i class='bx bxs-user-detail'></i>Edit Profile</a></li>
                    <li><a href="admin_reset_password.php"><i class='bx bx-lock-alt'></i>Password</a></li>
                </ul>
            </div>

            <div class="divider"></div>

            <div class="right">
                <h1>Member Details</h1>

                <div class="user-info">
                    <h2>Basic Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>User ID:</label>
                            <span><?= htmlspecialchars($user->user_id) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Name:</label>
                            <span><?= htmlspecialchars($user->name) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email:</label>
                            <span><?= htmlspecialchars($user->email) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Phone:</label>
                            <span><?= htmlspecialchars($user->phonenum) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Role:</label>
                            <span><?= htmlspecialchars($user->role) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Status:</label>
                            <span><?= ucfirst(htmlspecialchars($user->status)) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Created At:</label>
                            <span><?= htmlspecialchars($user->created_at) ?></span>
                        </div>
                    </div>
                </div>

                <div class="address-info">
                    <h2>Address Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Address Line 1:</label>
                            <span><?= htmlspecialchars($user->address_line1) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Address Line 2:</label>
                            <span><?= htmlspecialchars($user->address_line2) ?></span>
                        </div>
                        <div class="info-item">
                            <label>City:</label>
                            <span><?= htmlspecialchars($user->city) ?></span>
                        </div>
                        <div class="info-item">
                            <label>State:</label>
                            <span><?= htmlspecialchars($user->state) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Postal Code:</label>
                            <span><?= htmlspecialchars($user->postal_code) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Country:</label>
                            <span><?= htmlspecialchars($user->country) ?></span>
                        </div>
                    </div>
                </div>

                <div class="orders-info">
                    <h2>Order History</h2>
                    <?php if ($orders): ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Payment Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order->order_id) ?></td>
                                        <td><?= htmlspecialchars($order->order_date) ?></td>
                                        <td>RM <?= number_format($order->total_price, 2) ?></td>
                                        <td><?= htmlspecialchars($order->status) ?></td>
                                        <td><?= htmlspecialchars($order->payment_status) ?></td>
                                        <td><?= htmlspecialchars($order->payment_method) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No orders found.</p>
                    <?php endif; ?>
                </div>

                <div class="vouchers-info">
                    <h2>Vouchers</h2>
                    <?php if ($vouchers): ?>
                        <table class="vouchers-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vouchers as $voucher): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($voucher->code) ?></td>
                                        <td><?= htmlspecialchars($voucher->type) ?></td>
                                        <td><?= htmlspecialchars($voucher->value) ?></td>
                                        <td><?= htmlspecialchars($voucher->start_date) ?></td>
                                        <td><?= htmlspecialchars($voucher->end_date) ?></td>
                                        <td><?= htmlspecialchars($voucher->status) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No vouchers found.</p>
                    <?php endif; ?>
                </div>


            </div>
        </div>
    </main>

    <footer>
        <?php include __DIR__ . '/../../_footer.php'; ?>
    </footer>
</body>
</html>