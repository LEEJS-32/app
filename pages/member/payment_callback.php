<?php
require '../../database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log file setup
$logFile = "callback_log.txt";

// Read raw input
$raw_data = file_get_contents("php://input");
$post_data = $_POST;

// Log incoming request data
file_put_contents($logFile, date("Y-m-d H:i:s") . " - Callback received:\n", FILE_APPEND);
file_put_contents($logFile, "Raw data:\n" . htmlspecialchars($raw_data) . "\n", FILE_APPEND);
file_put_contents($logFile, "POST data:\n" . print_r($post_data, true) . "\n", FILE_APPEND);

// Decode JSON or use standard POST data
$data = json_decode($raw_data, true) ?? $post_data;

// Extract required fields
$ref_no = $data['refno'] ?? null;  // Transaction reference ID
$status = $data['status'] ?? null; // Payment status (1 = Success, 2 = Pending, Others = Failed)
$bill_code = $data['billcode'] ?? null;
$order_id = $data['billExternalReferenceNo'] ?? null; // Ensure correct retrieval
$amount = $data['amount'] ?? null;

// Validate required fields
if (!$ref_no || !$bill_code || !$order_id || !$amount) {
    file_put_contents($logFile, "Error: Missing required fields\n", FILE_APPEND);
    http_response_code(400);
    die(json_encode(["error" => "Missing required payment details"]));
}

// Convert amount from cents to RM
$amount = is_numeric($amount) ? (float) $amount / 100 : 0.00;

// Determine payment status
$payment_status = match ($status) {
    "1" => "Completed",
    "2" => "Pending",
    default => "Failed"
};

// Update payment record in the database
$stmt = $conn->prepare("UPDATE payments 
                        SET payment_status = ?, transaction_id = ?, transaction_date = NOW() 
                        WHERE order_id = ? AND bill_code = ?");
$stmt->bind_param("ssss", $payment_status, $ref_no, $order_id, $bill_code);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    file_put_contents($logFile, "Payment updated: Order ID $order_id, Status: $payment_status\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "Error: Payment update failed for Order ID: $order_id\n", FILE_APPEND);
}

// Update order status based on payment status
$order_status = ($payment_status === "Completed") ? 'processing' : 'pending';
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
$stmt->bind_param("ss", $order_status, $order_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    file_put_contents($logFile, "Order status updated: Order ID $order_id, Status: $order_status\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "Error: Failed to update order status for Order ID: $order_id\n", FILE_APPEND);
}

// Respond with success
http_response_code(200);
echo json_encode(["status" => "success"]);

?>
