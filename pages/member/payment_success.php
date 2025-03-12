<?php
require 'database.php'; // Database connection

// Check if transaction is successful
if (!isset($_GET['status_id']) || $_GET['status_id'] != "1") {
    die("Payment failed or cancelled.");
}

// Get payment details from URL
$bill_code = $_GET['billcode'];
$order_id = $_GET['order_id'] ?? null;
$amount = $_GET['amount'] / 100; // Convert back to RM

// Verify payment with ToyyibPay API
$api_key = "srlq0i5d-gfgf-ok6k-gy8j-92f18h7rhbw1";
$url = "https://dev.toyyibpay.com/index.php/api/getBillTransactions?billCode=" . $bill_code . "&userSecretKey=" . $api_key;

$response = file_get_contents($url);
$data = json_decode($response, true);

// Check if response is valid
if (!empty($data) && isset($data[0]['transaction_status']) && $data[0]['transaction_status'] == "1") {
    // Payment is successful, update database
    $stmt = $conn->prepare("UPDATE payments SET payment_status='Completed' WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE orders SET status='processing' WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo "Payment successful! Your order is now being processed.";
} else {
    echo "Payment verification failed.";
}
?>
