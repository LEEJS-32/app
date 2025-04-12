<?php

?>

<?php
include_once '../../_base.php';
require '../../database.php'; // Include database connection

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    die("Error: User not logged in.");
}

$user = $_SESSION['user'];
$user_id = $user['user_id'];

// Fetch user details
$query = $conn->prepare("SELECT name, email, phonenum FROM users WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Error: User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = 'ORD' . uniqid();
    $address_line1 = $_POST['address_line1'];
    $address_line2 = !empty($_POST['address_line2']) ? $_POST['address_line2'] : NULL;
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    $comment = !empty($_POST['comment']) ? $_POST['comment'] : NULL;
    $voucher_code = !empty($_POST['voucher_code']) ? $_POST['voucher_code'] : NULL;
    $discount_amount = !empty($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;

    // Use the final total price after discount
    $final_total_price = isset($_POST['final_total_price']) ? floatval($_POST['final_total_price']) : $total_price;

    // Insert order into the database
    $sql_order = "INSERT INTO orders (order_id, user_id, total_price, status, 
                        shipping_address_line1, shipping_address_line2, 
                        shipping_city, shipping_state, shipping_postal_code, 
                        shipping_country, comment, voucher_code) 
                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("sisssssssss", 
        $order_id, $user_id, $final_total_price, 
        $address_line1, $address_line2, $city, 
        $state, $postal_code, $country, $comment, $voucher_code);
    
    if ($stmt_order->execute()) {
        // Reduce voucher quantity if used
        if ($voucher_code) {
            $sql_voucher_update = "UPDATE vouchers SET quantity = quantity - 1 WHERE code = ?";
            $stmt_voucher_update = $conn->prepare($sql_voucher_update);
            $stmt_voucher_update->bind_param("s", $voucher_code);
            $stmt_voucher_update->execute();
        }

        // Redirect to payment page
        header("Location: create_payment.php?order_id=$order_id&voucher_code=$voucher_code");
        exit();
    } else {
        echo "Error: " . $stmt_order->error;
    }
}


// Fetch cart total amount
$query = $conn->prepare("
    SELECT SUM(p.discounted_price * sc.quantity) AS total_amount 
    FROM shopping_cart sc
    JOIN products p ON sc.product_id = p.product_id
    WHERE sc.user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$cart = $result->fetch_assoc();

// Ensure total amount is valid
$total_amount = $cart['total_amount'] ?? 0;
if ($total_amount <= 0) {
    die("Error: Your cart is empty. Add items before proceeding to payment.");
}

// Check if voucher code is passed and valid
$voucher_code = isset($_POST['voucher_code']) ? $_POST['voucher_code'] : null;
$voucher_discount = 0;

// Log voucher code if received
file_put_contents("payment_log.txt", "Voucher code received: $voucher_code\n", FILE_APPEND);

if ($voucher_code) {
    // Query voucher details
    $voucher_query = $conn->prepare("SELECT type, value, quantity FROM vouchers WHERE code = ? AND quantity > 0 AND NOW() BETWEEN start_date AND end_date");
    $voucher_query->bind_param("s", $voucher_code);
    $voucher_query->execute();
    $voucher_result = $voucher_query->get_result();
    
    if ($voucher_result->num_rows > 0) {
        $voucher = $voucher_result->fetch_assoc();
        $discount_type = $voucher['type']; // 'RM' or '%'
        $discount_value = $voucher['value']; // e.g., 10 for RM10 or 10 for 10%



        // Apply discount based on type
        if ($discount_type === 'rm') {
            echo ($discount_value);
            $voucher_discount = $discount_value;
        } elseif ($discount_type === '%') {
            $voucher_discount = ($total_amount * $discount_value) / 100;
        }

        // Log voucher discount details
        file_put_contents("payment_log.txt", "Voucher type: $discount_type\n", FILE_APPEND);
        file_put_contents("payment_log.txt", "Voucher discount applied: $voucher_discount\n", FILE_APPEND);
    } else {
        file_put_contents("payment_log.txt", "Voucher code is invalid or expired.\n", FILE_APPEND);
    }
}

// Apply the voucher discount to the total amount
$total_amount_after_discount = $total_amount - $voucher_discount;
$total_amount_after_discount = max(0, $total_amount_after_discount); // Ensure the amount doesn't go negative

// Log the total amount after discount
file_put_contents("payment_log.txt", "Total amount before discount: $total_amount\n", FILE_APPEND);
file_put_contents("payment_log.txt", "Total amount after discount: $total_amount_after_discount\n", FILE_APPEND);

// Generate a unique order ID
$order_id = 'ORD' . uniqid();

// Start transaction to ensure consistency
$conn->begin_transaction();

// try {
    // Check if the order already exists for this user with a pending status
    $existing_order_query = $conn->prepare("SELECT order_id FROM orders WHERE user_id = ? AND status = 'pending'");
    $existing_order_query->bind_param("i", $user_id);
    $existing_order_query->execute();
    $existing_order_result = $existing_order_query->get_result();
    
    // if ($existing_order_result->num_rows > 0) {
    //     $existing_order = $existing_order_result->fetch_assoc();
    //     $order_id = $existing_order['order_id'];
    // } else {
        // Insert new order including voucher code if provided
        $order_insert = $conn->prepare("
            INSERT INTO orders (order_id, user_id, total_price, status, voucher_code) 
            VALUES (?, ?, ?, 'pending', ?)");
        $order_insert->bind_param("sids", $order_id, $user_id, $total_amount_after_discount, $voucher_code);
        $order_insert->execute();
    // }

    // Insert order items only if they don't exist
    $query_cart_items = $conn->prepare("
        SELECT sc.product_id, sc.quantity, (sc.quantity * p.discounted_price) AS subtotal
        FROM shopping_cart sc
        JOIN products p ON sc.product_id = p.product_id
        WHERE sc.user_id = ?");
    $query_cart_items->bind_param("i", $user_id);
    $query_cart_items->execute();
    $result_items = $query_cart_items->get_result();

    while ($row = $result_items->fetch_assoc()) {
        // Check if the item already exists for the order
        $check_item = $conn->prepare("SELECT * FROM order_items WHERE order_id = ? AND product_id = ?");
        $check_item->bind_param("si", $order_id, $row['product_id']);
        $check_item->execute();
        $check_result = $check_item->get_result();
        
        if ($check_result->num_rows === 0) {
            $insert_item = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
                VALUES (?, ?, ?, ?)");
            $insert_item->bind_param("siid", $order_id, $row['product_id'], $row['quantity'], $row['subtotal']);
            $insert_item->execute();
        }
    }

    // Convert total amount after discount to cents (RM290.00 → 29000)
    $bill_amount = intval($total_amount_after_discount * 100);

    // ToyyibPay API details
    $api_key = "srlq0i5d-gfgf-ok6k-gy8j-92f18h7rhbw1"; // Replace with actual API key
    $category_code = "vsofdz4y"; // Replace with actual category code

    // Ensure callback URL is correct (HTTPS required for production)
    $callback_url = "https://opinion-cancellation-persian-contrast.trycloudflare.com/pages/member/payment_callback.php"; 
    $return_url = "http://localhost:8000/pages/member/order_history.php";

    // Payment details
    $data = [
        'userSecretKey' => $api_key,
        'categoryCode' => $category_code,
        'billName' => "Order Payment",
        'billDescription' => "Payment for order $order_id",
        'billAmount' => $bill_amount,
        'billReturnUrl' => $return_url,
        'billCallbackUrl' => $callback_url,
        'billExternalReferenceNo' => $order_id,
        'billTo' => $user['name'],
        'billEmail' => $user['email'],
        'billPhone' => $user['phonenum'],
        'billSuccessButtonText' => 'Proceed',
        'billFailedButtonText' => 'Cancel',
        'billPriceSetting' => "0",  // Fixed pricing mode
        'billPayorInfo' => "1"  // Require payer details
    ];

    // Call ToyyibPay API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://dev.toyyibpay.com/index.php/api/createBill');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Debugging: Log API response
    $logFile = "payment_log.txt";
    file_put_contents($logFile, date("Y-m-d H:i:s") . " - API Response: " . $response . "\n", FILE_APPEND);

    // Decode API response
    $response_data = json_decode($response, true);
    if ($http_code !== 200 || !$response_data || !isset($response_data[0]['BillCode'])) {
        file_put_contents($logFile, "Error: Unable to create payment.\n", FILE_APPEND);
        $conn->rollback(); // Rollback transaction if payment creation fails
        die("Error: Unable to create payment.");
    }

    $bill_code = $response_data[0]['BillCode'];
    $payment_url = "https://dev.toyyibpay.com/" . $bill_code;

    // Insert payment record as pending
    $insert_payment = $conn->prepare("
        INSERT INTO payments (order_id, user_id, amount, payment_status, bill_code, transaction_id) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $transaction_id = uniqid("txn_");
    $payment_status = 'Pending';
    $insert_payment->bind_param("sidsss", $order_id, $user_id, $total_amount_after_discount, $payment_status, $bill_code, $transaction_id);
    $insert_payment->execute();

    // Update voucher quantity only after payment success (during callback)
    if ($voucher_code) {
        $sql_update_voucher = "UPDATE vouchers SET quantity = quantity - 1 WHERE code = ? AND quantity > 0";
        $stmt_update_voucher = $conn->prepare($sql_update_voucher);
        $stmt_update_voucher->bind_param("s", $voucher_code);
        
        if (!$stmt_update_voucher->execute()) {
            file_put_contents($logFile, "Error updating voucher: " . $stmt_update_voucher->error . "\n", FILE_APPEND);
        } else {
            // Log voucher deduction
            file_put_contents($logFile, "Voucher $voucher_code used and quantity reduced.\n", FILE_APPEND);
        }
    }

    // Commit transaction if everything is successful
    $conn->commit();

    // Redirect to payment page
    header("Location: " . $payment_url);
    exit();

// } catch (Exception $e) {
//     // Rollback in case of error
//     $conn->rollback();
//     file_put_contents("payment_log.txt", "Error: " . $e->getMessage() . "\n", FILE_APPEND);
//     die("Error: Something went wrong. Please try again.");
// }
?>



