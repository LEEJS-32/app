<?php
require '../../database.php'; // Include database connection

// Enable debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log full request for debugging
$logFile = "callback_log.txt";
$logData = date("Y-m-d H:i:s") . " - Callback received:\n";

// Capture raw POST data
$raw_data = file_get_contents("php://input");

// Try to parse JSON (if any)
$json_data = json_decode($raw_data, true);

// If JSON is received, use it; otherwise, use $_POST
$data = !empty($json_data) ? $json_data : $_POST;

// Log the received data
$logData .= print_r($data, true) . "\n\n";
file_put_contents($logFile, $logData, FILE_APPEND);

// Check if data is received
if (empty($data)) {
    file_put_contents($logFile, date("Y-m-d H:i:s") . " - No data received!\n\n", FILE_APPEND);
    die("No data received.");
}

// Read POST parameters
$ref_no = $data['refno'] ?? null;
$status = $data['status'] ?? null;
$bill_code = $data['billcode'] ?? null;
$order_id = $data['order_id'] ?? null;
$amount = $data['amount'] ?? null;
$transaction_time = $data['transaction_time'] ?? null;

// Convert amount from cents to RM
$amount = $amount ? ($amount / 100) : 0;

// Validate required fields
if (!$ref_no || !$bill_code || !$order_id || !$amount) {
    file_put_contents($logFile, date("Y-m-d H:i:s") . " - Missing required fields\n\n", FILE_APPEND);
    die("Missing required payment details.");
}

// Check if order exists
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows == 0) {
    file_put_contents($logFile, date("Y-m-d H:i:s") . " - Order not found\n\n", FILE_APPEND);
    die("Order not found.");
}

// Set payment status
$payment_status = ($status == 1) ? 'Completed' : (($status == 2) ? 'Pending' : 'Failed');

// Insert or update payment record
$stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_status, transaction_date) 
                        VALUES (?, ?, ?, NOW()) 
                        ON DUPLICATE KEY UPDATE payment_status = ?, transaction_date = NOW()");
$stmt->bind_param("idss", $order_id, $amount, $payment_status, $payment_status);
$stmt->execute();

// Update order status
$order_update_status = ($status == 1) ? 'processing' : 'pending';
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$stmt->bind_param("si", $order_update_status, $order_id);
$stmt->execute();

// Display success message
echo "<h2>Payment Status: " . ($payment_status == 'Completed' ? "Success" : "Failed") . "</h2>";
echo "<p>Thank you for your order. You will receive an email confirmation shortly.</p>";

?>
