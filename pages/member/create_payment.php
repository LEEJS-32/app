<?php
session_start();
require '../../database.php'; // Include database connection

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
$bill_amount = intval($total_amount * 100); // Ensure it's an integer

// ToyyibPay API details (Replace with actual API key & category code)
$api_key = trim("srlq0i5d-gfgf-ok6k-gy8j-92f18h7rhbw1"); // Replace with actual API key
$category_code = trim("vsofdz4y"); // Replace with actual category code

// Validate API Key and Category Code
if (empty($api_key) || empty($category_code)) {
    die("Error: API Key or Category Code is missing.");
}

// Generate a unique order reference
$order_ref = 'ORD' . uniqid();

// Payment details
$data = [
    'userSecretKey' => $api_key,
    'categoryCode' => $category_code,
    'billName' => "Order Payment",
    'billDescription' => "Process payment for your order",
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $bill_amount, // Total price of cart in cents
    'billReturnUrl' => "http://localhost:8000/pages/member/payment_callback.php",
    'billCallbackUrl' => "http://localhost:8000/pages/member/payment_callback.php",
    'billExternalReferenceNo' => $order_ref,
    'billTo' => $user['name'],
    'billEmail' => $user['email'],
    'billPhone' => $user['phonenum'],
    'billSuccessButtonText' => 'Proceed', 
    'billFailedButtonText' => 'Cancel'
];

// Initialize cURL request to ToyyibPay
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://dev.toyyibpay.com/index.php/api/createBill');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Convert array to URL-encoded format
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']); // Correct content type

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Debugging: Print response if there's an error
if (!$response || $http_code != 200) {
    die("cURL Error: " . $curl_error . "<br>Response: " . $response);
}

// Decode response
$response_data = json_decode($response, true);

// Check if response contains BillCode
if (is_array($response_data) && isset($response_data[0]['BillCode'])) {
    $bill_code = $response_data[0]['BillCode'];
    $payment_url = "https://dev.toyyibpay.com/" . $bill_code;

    // Redirect to payment page
    header("Location: " . $payment_url);
    exit();
} else {
    echo "Error creating payment link. Response from ToyyibPay: " . print_r($response, true);
}
?>
