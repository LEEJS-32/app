<?php 
include_once '../../_base.php';
require '../../db/db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user['user_id'] : 0;

$cart_items = isset($_SESSION['cart_items']) ? $_SESSION['cart_items'] : [];
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['discounted_price'] * $item['quantity'];
}

if ($user_id > 0) {
    $sql_user = "SELECT name, phonenum, email FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_info = $stmt_user->get_result()->fetch_assoc();

    $sql_address = "SELECT address_line1, address_line2, city, state, postal_code, country
                    FROM shippingaddress WHERE user_id = ? AND is_default = 1";
    $stmt_address = $conn->prepare($sql_address);
    $stmt_address->bind_param("i", $user_id);
    $stmt_address->execute();
    $address_info = $stmt_address->get_result()->fetch_assoc();

    $vouchers = [];
    $sql_vouchers = "SELECT * FROM vouchers 
                     WHERE user_id = ? 
                     AND quantity > 0 
                     AND start_date <= CURDATE() 
                     AND end_date >= CURDATE()";
    $stmt_voucher = $conn->prepare($sql_vouchers);
    $stmt_voucher->bind_param("i", $user_id);
    $stmt_voucher->execute();
    $result_vouchers = $stmt_voucher->get_result();
    $vouchers = $result_vouchers->fetch_all(MYSQLI_ASSOC);
} else {
    $user_info = null;
    $address_info = null;
    $vouchers = [];
}
?>

<h2>Checkout</h2>

<?php if ($user_id > 0 && $user_info): ?>
    <p><strong>Name:</strong> <?= htmlspecialchars($user_info['name']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($user_info['phonenum']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
<?php else: ?>
    <p><strong>Guest Checkout</strong></p>
<?php endif; ?>

<h3>Shopping Cart</h3>
<?php if (!empty($cart_items)): ?>
    <table border='1'>
        <tr><th>Product</th><th>Discounted Price</th><th>Quantity</th><th>Total</th></tr>
        <?php foreach ($cart_items as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?><br><img src='<?= htmlspecialchars($row['image_url']) ?>' width='50'></td>
                <td><?= number_format($row['discounted_price'], 2) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= number_format($row['discounted_price'] * $row['quantity'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p><strong>Shipping Fee:</strong> RM 0.00</p>
    <p><strong>Total Price:</strong> RM <?= number_format($total_price, 2) ?></p>
<?php else: ?>
    <p>Your cart is empty!</p>
<?php endif; ?>

<h3>Shipping Information</h3>
<!-- ✅ CHANGED action to create_payment.php -->
<form action="create_payment.php" method="POST">
    <label>Address Line 1:</label><br>
    <input type="text" name="address_line1" value="<?= htmlspecialchars($address_info['address_line1'] ?? '') ?>" required><br>

    <label>Address Line 2:</label><br>
    <input type="text" name="address_line2" value="<?= htmlspecialchars($address_info['address_line2'] ?? '') ?>"><br>

    <label>City:</label><br>
    <input type="text" name="city" value="<?= htmlspecialchars($address_info['city'] ?? '') ?>" required><br>

    <label>State:</label><br>
    <input type="text" name="state" value="<?= htmlspecialchars($address_info['state'] ?? '') ?>" required><br>

    <label>Postal Code:</label><br>
    <input type="text" name="postal_code" value="<?= htmlspecialchars($address_info['postal_code'] ?? '') ?>" required><br>

    <label>Country:</label><br>
    <input type="text" name="country" value="<?= htmlspecialchars($address_info['country'] ?? '') ?>" required><br>

    <label>Comment to Seller:</label><br>
    <textarea name="comment"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea><br>

    <label>Select Voucher:</label><br>
    <select name="voucher_code" id="voucher_code" onchange="applyVoucher()">
        <option value="" data-type="" data-value="0">-- No Voucher --</option>
        <?php foreach ($vouchers as $voucher): ?>
            <option value="<?= $voucher['code'] ?>" 
                    data-type="<?= $voucher['type'] ?>" 
                    data-value="<?= $voucher['value'] ?>">
                <?= $voucher['description'] ?> (<?= $voucher['type'] === 'rm' ? 'RM' : '%' ?><?= $voucher['value'] ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <input type="hidden" name="discount_amount" id="discount_amount" value="0">
    <input type="hidden" name="final_total_price" id="final_total_price" value="<?= $total_price ?>">

    <p><strong id="discount_info"></strong></p>
    <p><strong>Total After Discount:</strong> RM <span id="final_price"><?= number_format($total_price, 2) ?></span></p>

    <button type="submit" name="place_order">Checkout</button>
</form>

<script>
    const originalTotal = <?= $total_price ?>;

    function applyVoucher() {
        const select = document.getElementById('voucher_code');
        const type = select.options[select.selectedIndex].getAttribute('data-type');
        const value = parseFloat(select.options[select.selectedIndex].getAttribute('data-value'));

        let discount = 0;
        if (type === 'rm') {
            discount = value;
        } else if (type === 'percent') {
            discount = (originalTotal * value) / 100;
        }

        const final = Math.max(originalTotal - discount, 0);
        document.getElementById('final_price').innerText = final.toFixed(2);
        document.getElementById('discount_amount').value = discount.toFixed(2);
        document.getElementById('final_total_price').value = final.toFixed(2);
        document.getElementById('discount_info').innerText = discount > 0 
            ? `Discount Applied: RM ${discount.toFixed(2)}` 
            : '';
    }
</script>


