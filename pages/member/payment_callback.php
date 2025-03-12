<?php
require 'database.php';

// Get JSON data from ToyyibPay
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    die("Invalid request.");
}

// Extract payment details
$bill_code = $data['billcode'];
$order_id = $data['order_id'];
$status_id = $data['status_id']; // 1 = Success, 2 = Failed

if ($status_id == "1") {
    // Payment Successful
    $stmt = $conn->prepare("UPDATE payments SET payment_status='Completed' WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE orders SET status='processing' WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo "Payment success updated.";
} else {
    // Payment Failed
    $stmt = $conn->prepare("UPDATE payments SET payment_status='Failed' WHERE order_id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo "Payment failed updated.";
}
?>
