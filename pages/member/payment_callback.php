<?php
require '../../database.php';

// Enable error reporting
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
file_put_contents($logFile, "POST data:\n" . print_r($post_data, true) . "\n", FILE_APPEND);

// Extract required fields from POST data
$ref_no = $_POST['refno'] ?? null;  
$status = $_POST['status'] ?? null; 
$bill_code = $_POST['billcode'] ?? null;
$order_id = $_POST['order_id'] ?? null; 
$amount = $_POST['amount'] ?? null;
$transaction_time = $_POST['transaction_time'] ?? null;
$payment_method = "ToyyibPay"; // Adjust if necessary

// Validate required fields
if (!$ref_no || !$bill_code || !$order_id || !$amount || !$transaction_time) {
    file_put_contents($logFile, "Error: Missing required fields\n", FILE_APPEND);
    http_response_code(400);
    die(json_encode(["error" => "Missing required payment details"]));
}

// Retrieve `user_id` from the `orders` table
$stmt = $conn->prepare("SELECT user_id FROM orders WHERE order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    file_put_contents($logFile, "Error: Order ID not found in orders table\n", FILE_APPEND);
    http_response_code(400);
    die(json_encode(["error" => "Invalid order ID"]));
}

$user_id = $user['user_id']; // Now we have `user_id`

// Determine payment status
$payment_status = match ($status) {
    "1" => "Completed",
    "2" => "Pending",
    default => "Failed"
};

// Start transaction
$conn->begin_transaction();

try {
    // Check if a payment record already exists for this order
    $stmt = $conn->prepare("SELECT payment_id FROM payments WHERE order_id = ? AND bill_code = ?");
    $stmt->bind_param("ss", $order_id, $bill_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_payment = $result->fetch_assoc();

    if ($existing_payment) {
        // Update existing payment record
        $stmt = $conn->prepare("
            UPDATE payments 
            SET transaction_id = ?, payment_status = ?, amount = ?, transaction_date = ?
            WHERE order_id = ? AND bill_code = ?
        ");
        $stmt->bind_param("ssdsss", $ref_no, $payment_status, $amount, $transaction_time, $order_id, $bill_code);
        $stmt->execute();
        
        file_put_contents($logFile, "✅ Updated existing payment record: Order ID $order_id, Status: $payment_status\n", FILE_APPEND);
    } else {
        // Insert new payment record
        $stmt = $conn->prepare("
            INSERT INTO payments (user_id, order_id, bill_code, transaction_id, payment_status, amount, transaction_date, payment_method) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssds", $user_id, $order_id, $bill_code, $ref_no, $payment_status, $amount, $transaction_time, $payment_method);
        $stmt->execute();
        
        file_put_contents($logFile, "✅ Inserted new payment record: Order ID $order_id, Status: $payment_status\n", FILE_APPEND);
    }

    // Update order status
    $order_status = ($payment_status === "Completed") ? 'processing' : 'pending';
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("ss", $order_status, $order_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        file_put_contents($logFile, "✅ Order status updated: Order ID $order_id, Status: $order_status\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "⚠️ No changes made to order status for Order ID: $order_id\n", FILE_APPEND);
    }

    // Commit transaction
    $conn->commit();

    // Respond with success
    http_response_code(200);
    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    file_put_contents($logFile, "❌ Database error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    die(json_encode(["error" => "Database error"]));
}
?>
