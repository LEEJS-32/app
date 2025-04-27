<?php
include_once '../../_base.php';

auth_user();
auth('member');

$new_user = false;
$_SESSION["new_user"] = $new_user;

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;

// Fetch vouchers for the logged-in user
try {
    $stm = $_db->prepare("SELECT code, type, value, quantity, start_date, end_date, description 
                          FROM vouchers 
                          WHERE user_id = :user_id OR user_id IS NULL
                          ORDER BY start_date DESC");
    $stm->execute([':user_id' => $user_id]);
    $vouchers = $stm->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error fetching vouchers: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member - See Vouchers</title>
    <link rel="stylesheet" href="../../css/member/memberVoucher.css">
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
                <li><a href="member_profile.php"><i class='bx bxs-user-detail'></i>My Profile</a></li>
                <li><a href="reset_password.php"><i class='bx bx-lock-alt'></i>Password</a></li>
                <li><a href="member_address.php"><i class='bx bx-home-alt-2'></i>My Address</a></li>
                <li><a href="view_cart.php"><i class='bx bx-shopping-bag'></i>Shopping Cart</a></li>
                <li><a href="#"><i class='bx bx-heart'></i>Wishlist</a></li>
                <li><a href="adminOrder.php"><i class='bx bx-food-menu'></i>My Orders</a></li>
            </ul>
        </div>
        <div class="divider"></div>

        <div class="right">
            <h1>My Vouchers</h1>
            <?php if (!empty($vouchers)): ?>
                <table class="voucher-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Quantity</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vouchers as $voucher): ?>
                            <tr>
                                <td><?= htmlspecialchars($voucher->code); ?></td>
                                <td><?= htmlspecialchars($voucher->type === 'rm' ? 'RM' : '%'); ?></td>
                                <td><?= htmlspecialchars($voucher->value); ?></td>
                                <td><?= htmlspecialchars($voucher->quantity); ?></td>
                                <td><?= htmlspecialchars($voucher->start_date); ?></td>
                                <td><?= htmlspecialchars($voucher->end_date); ?></td>
                                <td><?= htmlspecialchars($voucher->description); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-vouchers">You have no vouchers available.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <?php include '../../_footer.php'; ?>
</footer>

</body>
</html>
