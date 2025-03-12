<?php
session_start();
require 'database.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in to make a payment.");
}

$user_id = $_SESSION['user_id'];

// Fetch user details from database
$query = $conn->prepare("SELECT name, email, phonenum FROM users WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Fetch total cart amount for the user
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
    die("Your cart is empty. Add items before proceeding to payment.");
}

// Convert total amount to cents (RM10.00 → 1000)
$bill_amount = $total_amount * 100;

// ToyyibPay API details (Replace with your own)
$api_key = "srlq0i5d-gfgf-ok6k-gy8j-92f18h7rhbw1";
$category_code = "vsofdz4y";

// Payment details
$bill_name = "Order Payment";
$bill_description = "Process payment for your order";
$return_url = "http://yourwebsite.com/payment_success.php";
$callback_url = "http://yourwebsite.com/payment_callback.php";

// Assign user details
$customer_name = $user['name'];
$customer_email = $user['email'];
$customer_phone = $user['phonenum'];

$data = array(
    'userSecretKey' => $api_key,
    'categoryCode' => $category_code,
    'billName' => $bill_name,
    'billDescription' => $bill_description,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $bill_amount, // Total price of cart
    'billReturnUrl' => $return_url,
    'billCallbackUrl' => $callback_url,
    'billExternalReferenceNo' => 'ORD' . uniqid(),
    'billTo' => $customer_name,
    'billEmail' => $customer_email,
    'billPhone' => $customer_phone
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://dev.toyyibpay.com/index.php/api/createBill');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_POST, 1);

$response = curl_exec($ch);
curl_close($ch);

// Decode response and get the payment link
$response_data = json_decode($response, true);
if (isset($response_data[0]['BillCode'])) {
    $bill_code = $response_data[0]['BillCode'];
    $payment_url = "https://dev.toyyibpay.com/" . $bill_code;

    // Redirect to payment page
    header("Location: " . $payment_url);
    exit();
} else {
    echo "Error creating payment link. Please check your API key and category code.";
}
?>
