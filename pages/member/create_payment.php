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

// Fetch cart total amount
$query = $conn->prepare("
    SELECT SUM(p.price * sc.quantity) AS total_amount 
    FROM shopping_cart sc
    JOIN products p ON sc.product_id = p.product_id
    WHERE sc.user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$cart = $result->fetch_assoc();
$total_amount = $cart['total_amount'] ?? 0;

if ($total_amount <= 0) {
    die("Error: Your cart is empty. Add items before proceeding to payment.");
}

// Generate a unique order ID
$order_id = 'ORD' . uniqid();

// Insert order into database
$order_insert = $conn->prepare("
    INSERT INTO orders (order_id, user_id, total_price, status) 
    VALUES (?, ?, ?, 'pending')");
$order_insert->bind_param("sid", $order_id, $user_id, $total_amount);
$order_insert->execute();

// Insert order items
$query_cart_items = $conn->prepare("
    SELECT sc.product_id, sc.quantity, (sc.quantity * p.price) AS subtotal
    FROM shopping_cart sc
    JOIN products p ON sc.product_id = p.product_id
    WHERE sc.user_id = ?");
$query_cart_items->bind_param("i", $user_id);
$query_cart_items->execute();
$result_items = $query_cart_items->get_result();

while ($row = $result_items->fetch_assoc()) {
    $insert_item = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
        VALUES (?, ?, ?, ?)");
    $insert_item->bind_param("siid", $order_id, $row['product_id'], $row['quantity'], $row['subtotal']);
    $insert_item->execute();
}

// Convert total amount to cents (RM10.00 → 1000)
$bill_amount = intval($total_amount * 100);

// ToyyibPay API details
$api_key = "srlq0i5d-gfgf-ok6k-gy8j-92f18h7rhbw1"; // Replace with actual API key
$category_code = "vsofdz4y"; // Replace with actual category code

// Ensure callback URL is correct (HTTPS required for production)
$callback_url = "https://motors-least-keywords-architectural.trycloudflare.com/pages/member/payment_callback.php"; 
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
    echo "<h3>Error: Unable to create payment.</h3>";
    echo "<pre>API Response:</pre>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
    exit();
}

$bill_code = $response_data[0]['BillCode'];
$payment_url = "https://dev.toyyibpay.com/" . $bill_code;

// Insert payment record as pending
$insert_payment = $conn->prepare("
    INSERT INTO payments (order_id, user_id, amount, payment_status, bill_code, transaction_id) 
    VALUES (?, ?, ?, ?, ?, ?)");

// Generate a unique transaction ID
$transaction_id = uniqid("txn_");
$payment_status = 'Pending';

$insert_payment->bind_param("sidsss", $order_id, $user_id, $total_amount, $payment_status, $bill_code, $transaction_id);
$insert_payment->execute();

// Redirect to payment page
header("Location: " . $payment_url);
exit();

?>
