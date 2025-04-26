<?php
include_once '../../_base.php';

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

try {
    // Retrieve `user_id` from the `orders` table
    $stm = $_db->prepare("SELECT user_id FROM orders WHERE order_id = :order_id");
    $stm->execute([':order_id' => $order_id]);
    $user = $stm->fetch(PDO::FETCH_OBJ);

    if (!$user) {
        file_put_contents($logFile, "Error: Order ID not found in orders table\n", FILE_APPEND);
        http_response_code(400);
        die(json_encode(["error" => "Invalid order ID"]));
    }

    $user_id = $user->user_id; // Now we have `user_id`

    // Determine payment status
    $payment_status = match ($status) {
        "1" => "Completed",
        "2" => "Pending",
        default => "Failed"
    };

    // Start transaction
    $_db->beginTransaction();

    // Check if a payment record already exists for this order
    $stm = $_db->prepare("SELECT payment_id FROM payments WHERE order_id = :order_id AND bill_code = :bill_code");
    $stm->execute([
        ':order_id' => $order_id,
        ':bill_code' => $bill_code
    ]);
    $existing_payment = $stm->fetch(PDO::FETCH_OBJ);

    if ($existing_payment) {
        // Update existing payment record
        $stm = $_db->prepare("
            UPDATE payments 
            SET transaction_id = :ref_no, payment_status = :payment_status, 
                amount = :amount, transaction_date = :transaction_time
            WHERE order_id = :order_id AND bill_code = :bill_code
        ");
        $stm->execute([
            ':ref_no' => $ref_no,
            ':payment_status' => $payment_status,
            ':amount' => $amount,
            ':transaction_time' => $transaction_time,
            ':order_id' => $order_id,
            ':bill_code' => $bill_code
        ]);
        
        file_put_contents($logFile, "✅ Updated existing payment record: Order ID $order_id, Status: $payment_status\n", FILE_APPEND);
    } else {
        // Insert new payment record
        $stm = $_db->prepare("
            INSERT INTO payments (user_id, order_id, bill_code, transaction_id, payment_status, amount, transaction_date, payment_method) 
            VALUES (:user_id, :order_id, :bill_code, :ref_no, :payment_status, :amount, :transaction_time, :payment_method)
        ");
        $stm->execute([
            ':user_id' => $user_id,
            ':order_id' => $order_id,
            ':bill_code' => $bill_code,
            ':ref_no' => $ref_no,
            ':payment_status' => $payment_status,
            ':amount' => $amount,
            ':transaction_time' => $transaction_time,
            ':payment_method' => $payment_method
        ]);
        
        file_put_contents($logFile, "✅ Inserted new payment record: Order ID $order_id, Status: $payment_status\n", FILE_APPEND);
    }

    // Update order status
    $order_status = ($payment_status === "Completed") ? 'processing' : 'cancelled';
    $stm = $_db->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
    $stm->execute([
        ':status' => $order_status,
        ':order_id' => $order_id
    ]);

    if ($stm->rowCount() > 0) {
        file_put_contents($logFile, "✅ Order status updated: Order ID $order_id, Status: $order_status\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "⚠️ No changes made to order status for Order ID: $order_id\n", FILE_APPEND);
    }

    // **Reduce stock for each product in the order**
    if ($order_status !== 'cancelled') {
        $stm = $_db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :order_id");
        $stm->execute([':order_id' => $order_id]);
        $order_items = $stm->fetchAll(PDO::FETCH_OBJ);

        foreach ($order_items as $item) {
            $product_id = $item->product_id;
            $quantity = $item->quantity;

            // Reduce the quantity in the products table
            $stm = $_db->prepare("UPDATE products SET stock = stock - :quantity WHERE product_id = :product_id");
            $stm->execute([
                ':quantity' => $quantity,
                ':product_id' => $product_id
            ]);

            // Log stock reduction
            file_put_contents($logFile, "✅ Reduced stock for Product ID $product_id, Quantity: $quantity\n", FILE_APPEND);
        }
    } else {
        file_put_contents($logFile, "⚠️ Stock reduction skipped as order status is 'cancelled' for Order ID: $order_id\n", FILE_APPEND);
    }

    // Commit transaction
    $_db->commit();

    // Respond with success
    http_response_code(200);
    echo json_encode(["status" => "success"]);

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($_db->inTransaction()) {
        $_db->rollBack();
    }
    file_put_contents($logFile, "❌ Database error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    die(json_encode(["error" => "Database error"]));
}
?>
