<?php
include_once '../../_base.php';
require '../../db/db_connect.php';

// ----------------------------------------------------------------------------
// Check session/cookie
// auth_user();
// auth(''); // Accept any authenticated user
// ----------------------------------------------------------------------------

$user = $_SESSION['user'] ?? null;
$user_id = $user ? $user['user_id'] : 0;

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $action = $_POST['action'];

    if ($user_id > 0) {
        // Logged-in user: Update DB
        if ($action === 'increase') {
            $sql = "UPDATE shopping_cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        } elseif ($action === 'decrease') {
            $sql = "UPDATE shopping_cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ? AND quantity > 1";
        } elseif ($action === 'remove') {
            $sql = "DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Guest user: Update session
        if (isset($_SESSION['cart'][$product_id])) {
            if ($action === 'increase') {
                $_SESSION['cart'][$product_id]++;
            } elseif ($action === 'decrease' && $_SESSION['cart'][$product_id] > 1) {
                $_SESSION['cart'][$product_id]--;
            } elseif ($action === 'remove') {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }

    header("Location: view_cart.php");
    exit();
}

// Retrieve cart items
$cart_items = [];
if ($user_id > 0) {
    $sql_cart = "SELECT shopping_cart.cart_id, shopping_cart.quantity, 
                        products.product_id, products.name, products.discounted_price, products.image_url
                 FROM shopping_cart
                 JOIN products ON shopping_cart.product_id = products.product_id
                 WHERE shopping_cart.user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();
    $cart_items = $result_cart->num_rows > 0 ? $result_cart->fetch_all(MYSQLI_ASSOC) : [];
} else {
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $sql_product = "SELECT product_id, name, discounted_price, image_url FROM products WHERE product_id = ?";
            $stmt_product = $conn->prepare($sql_product);
            $stmt_product->bind_param("i", $product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();
            if ($result_product->num_rows > 0) {
                $product = $result_product->fetch_assoc();
                $product['quantity'] = $quantity;
                $cart_items[] = $product;
            }
        }
    }
}

// Store total price
$total_cart_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/member/cart.css">
    <title>Shopping Cart</title>
</head>

<body>
    <header>
        <?php include '../../_header.php'; ?>
    </header>

    <main>
        <h2>Shopping Cart</h2>
        <div class="content">
        <div class="cart-list">

            <?php if (!empty($cart_items)): ?>
                <table class="cart-product">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                    <?php 
                    $total_cart_price = 0;
                    $total_item_count = 0;
                    
                    foreach ($cart_items as $row): 
                        $price = $row['discounted_price'];
                        $quantity = $row['quantity'];
                        $total_price = $price * $quantity;
                        $total_cart_price += $total_price;
                        $total_item_count += $quantity;
                    ?>
                    
                    <tr>
                        <td><img src="<?= $row['image_url'] ?>" width="50"><?= htmlspecialchars($row['name']) ?></td>
                        <td>RM <?= number_format($price, 2) ?></td>
                        <form method="POST" action="view_cart.php">
                            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                        <td>
                        <div class="qty">
                            <button type="submit" name="action" value="decrease">−</button>
                            <span class="qty-number"><?= $quantity ?></span>
                            <button type="submit" name="action" value="increase">+</button>
                        </div>
                        </td>
                        <td>
                                RM <?= number_format($total_price, 2) ?>          
                        </td>
                        <td>
                            <button type="submit" name="action" value="remove" class="remove-btn"><i class='bx bx-x'></i></button>
                            </form>
                        </td>
                        
                    </tr>
                    <?php endforeach; ?>
                </table>
        </div>

        <div class="summary">
    <h3>Order Summary</h3>
    
    <div class="summary-row">
        <span>Items</span>
        <span><?= $total_item_count ?></span>
    </div>
    <div class="summary-row">
        <span>Sub Total</span>
        <span>RM <?= number_format($total_cart_price, 2) ?></span>
    </div>
    <div class="summary-row">
        <span>Shipping</span>
        <span>RM 00.00</span>
    </div>
    <div class="summary-row">
        <span>SST</span>
        <span>RM 00.00</span>
    </div>

    <hr>

    <div class="summary-row total">
        <strong>Total</strong>
        <strong>RM <?= number_format($total_cart_price, 2) ?></strong>
    </div>

    <?php
    $_SESSION['cart_items'] = $cart_items;
    $_SESSION['total_price'] = $total_cart_price;
    ?>

    <?php if ($user_id > 0): ?>
        <form action="checkout.php" method="POST">
            <button type="submit" class="checkout-btn">Proceed to Checkout</button>
        </form>
    <?php else: ?>
        <p style="margin-top: 10px;">You must <a href="../signup_login.php">log in</a> to proceed to checkout.</p>
    <?php endif; ?>
    <?php else: ?>
                <p>Your cart is empty!</p>
            <?php endif; ?>
</div>

        </div>
    </main>

    <footer>
        <?php include '../../_footer.php'; ?>
    </footer>
</body>
</html>
