<?php 
require '../../_base.php';
auth_user();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user->user_id : 0;

$cart_items = isset($_SESSION['cart_items']) ? $_SESSION['cart_items'] : [];
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item->discounted_price * $item->quantity;
}

try {
    if ($user_id > 0) {
        // Fetch user info
        $stm = $_db->prepare("SELECT name, phonenum, email FROM users WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);
        $user_info = $stm->fetch(PDO::FETCH_OBJ);

        // Fetch address info
        $stm = $_db->prepare("SELECT address_line1, address_line2, city, state, postal_code, country
                             FROM address WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);
        $address_info = $stm->fetch(PDO::FETCH_OBJ);

        // Fetch available vouchers
        $stm = $_db->prepare("SELECT code, description, type, value, quantity 
                              FROM vouchers 
                              WHERE user_id = :user_id 
                              AND quantity > 0 
                              AND start_date <= CURDATE() 
                              AND end_date >= CURDATE()");
        $stm->execute([':user_id' => $user_id]);
        $vouchers = $stm->fetchAll(PDO::FETCH_OBJ);
    } else {
        $user_info = null;
        $address_info = null;
        $vouchers = [];
    }
} catch (PDOException $e) {
    error_log("Error in checkout: " . $e->getMessage());
    die("Error loading checkout information. Please try again later.");
}
?>

<head>
    <link rel="stylesheet" href="../../css/member/checkout.css">
</head>

<body>
<header>
    <?php include '../../_header.php'; ?>
</header>

<main>
    <div class="checkout-container">
        <h2>Checkout</h2>

        <div class="user-info card">
            <?php if ($user_id > 0 && $user_info): ?>
                <p><strong>Name:</strong> <?= htmlspecialchars($user_info->name) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($user_info->phonenum) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_info->email) ?></p>
            <?php else: ?>
                <p><strong>Guest Checkout</strong></p>
            <?php endif; ?>
        </div>

        <div class="cart-section card">
            <h3>Shopping Cart</h3>
            <?php if (!empty($cart_items)): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Discounted Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $row):
                            $image_urls = json_decode($row->image_url);
                            $image_url = $image_urls[0] ?? ''; // Assuming you want the first image URL with fallback
                        ?>
                            <tr>
                                <?php echo "<td>" . htmlspecialchars($row->name) . "<br><img width='200px' height='200px' src='/" . htmlspecialchars($image_url) . "' alt='" . htmlspecialchars($row->name) . "'</td>"; ?>
                                <td>RM <?= number_format($row->discounted_price, 2) ?></td>
                                <td><?= htmlspecialchars($row->quantity) ?></td>
                                <td>RM <?= number_format($row->discounted_price * $row->quantity, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Your cart is empty!</p>
            <?php endif; ?>
        </div>

        <div class="shipping-info card">
            <h3>Shipping Information</h3>
            <form action="create_payment.php" method="POST">
                <label>Address Line 1:</label>
                <input type="text" name="address_line1" value="<?= htmlspecialchars($address_info->address_line1 ?? '') ?>" required>

                <label>Address Line 2:</label>
                <input type="text" name="address_line2" value="<?= htmlspecialchars($address_info->address_line2 ?? '') ?>">

                <label>City:</label>
                <input type="text" name="city" value="<?= htmlspecialchars($address_info->city ?? '') ?>" required>

                <label>State:</label>
                <input type="text" name="state" value="<?= htmlspecialchars($address_info->state ?? '') ?>" required>

                <label>Postal Code:</label>
                <input type="text" name="postal_code" value="<?= htmlspecialchars($address_info->postal_code ?? '') ?>" required>

                <label>Country:</label>
                <input type="text" name="country" value="<?= htmlspecialchars($address_info->country ?? '') ?>" required>

                <label>Comment to Seller:</label>
                <textarea name="comment"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>

                <label>Select Voucher:</label>
                <select name="voucher_code" id="voucher_code" onchange="applyVoucher()">
                    <option value="" data-type="" data-value="0" data-quantity="0">-- No Voucher --</option>
                    <?php foreach ($vouchers as $voucher): ?>
                        <option value="<?= htmlspecialchars($voucher->code) ?>" 
                                data-type="<?= htmlspecialchars($voucher->type) ?>" 
                                data-value="<?= htmlspecialchars($voucher->value) ?>" 
                                data-quantity="<?= htmlspecialchars($voucher->quantity) ?>">
                            <?= htmlspecialchars($voucher->description) ?> 
                            (<?= $voucher->type === 'rm' ? 'RM' : '%' ?><?= htmlspecialchars($voucher->value) ?>, 
                            Quantity: <?= htmlspecialchars($voucher->quantity) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                <input type="hidden" name="final_total_price" id="final_total_price" value="<?= $total_price ?>">

                <div class="price-summary">                    
                    <p><strong>Shipping Fee:</strong> RM 0.00</p>
                    <p><strong>Total Price:</strong> RM <?= number_format($total_price, 2) ?></p>
                    <p><strong id="discount_info"></strong></p>
                    <p><strong>Total After Discount: RM <span id="final_price"><?= number_format($total_price, 2) ?></span></strong></p>
                </div>

                <button type="submit" name="place_order">Checkout</button>
            </form>
        </div>
    </div>

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
                ? `Discount Applied: -RM ${discount.toFixed(2)}` 
                : '';
        }
    </script>
</main>

<footer>
    <?php include '../../_footer.php'; ?>
</footer>
</body>
