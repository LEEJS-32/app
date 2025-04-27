<?php
include_once '../../_base.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    die("Error: User not logged in.");
}

$user = $_SESSION['user'];
$user_id = $user->user_id;

try {
    // Fetch user details
    $stm = $_db->prepare("SELECT name, email, phonenum FROM users WHERE user_id = :user_id");
    $stm->execute([':user_id' => $user_id]);
    $user = $stm->fetch(PDO::FETCH_OBJ);

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

        echo "Discount amount: $discount_amount\n";
        echo "Voucher code: $voucher_code\n";

        // Use the final total price after discount
        $final_total_price = isset($_POST['final_total_price']) ? floatval($_POST['final_total_price']) : $total_price;

        // Start transaction
        $_db->beginTransaction();

        try {
            // Insert order into the database
            $sql_order = "INSERT INTO orders (order_id, user_id, total_price, status, 
                            shipping_address_line1, shipping_address_line2, 
                            shipping_city, shipping_state, shipping_postal_code, 
                            shipping_country, comment, voucher_code) 
                    VALUES (:order_id, :user_id, :total_price, 'pending', 
                            :address_line1, :address_line2, :city, :state, 
                            :postal_code, :country, :comment, :voucher_code)";
            
            $stm_order = $_db->prepare($sql_order);
            $stm_order->execute([
                ':order_id' => $order_id,
                ':user_id' => $user_id,
                ':total_price' => $final_total_price,
                ':address_line1' => $address_line1,
                ':address_line2' => $address_line2,
                ':city' => $city,
                ':state' => $state,
                ':postal_code' => $postal_code,
                ':country' => $country,
                ':comment' => $comment,
                ':voucher_code' => $voucher_code
            ]);

            // Reduce voucher quantity if used
            if ($voucher_code) {
                $stm_voucher = $_db->prepare("UPDATE vouchers SET quantity = quantity - 1 WHERE code = :code");
                $stm_voucher->execute([':code' => $voucher_code]);
            }

            // Fetch cart total amount
            $stm_cart = $_db->prepare("
                SELECT SUM(p.discounted_price * sc.quantity) AS total_amount 
                FROM shopping_cart sc
                JOIN products p ON sc.product_id = p.product_id
                WHERE sc.user_id = :user_id");
            $stm_cart->execute([':user_id' => $user_id]);
            $cart = $stm_cart->fetch(PDO::FETCH_OBJ);

            // Ensure total amount is valid
            $total_amount = $cart->total_amount ?? 0;
            if ($total_amount <= 0) {
                throw new Exception("Your cart is empty. Add items before proceeding to payment.");
            }

            // Check if voucher code is passed and valid
            $voucher_discount = 0;
            file_put_contents("payment_log.txt", "Voucher code received: $voucher_code\n", FILE_APPEND);

            if ($voucher_code) {
                // Query voucher details
                $stm_voucher = $_db->prepare("
                    SELECT type, value, quantity 
                    FROM vouchers 
                    WHERE code = :code 
                    AND quantity > 0 
                    AND NOW() BETWEEN start_date AND end_date");
                $stm_voucher->execute([':code' => $voucher_code]);
                $voucher = $stm_voucher->fetch(PDO::FETCH_OBJ);
                
                if ($voucher) {
                    $discount_type = $voucher->type; // 'RM' or 'percent'
                    $discount_value = $voucher->value; // e.g., 10 for RM10 or 10 for 10%

                    // Apply discount based on type
                    if ($discount_type === 'rm') {
                        $voucher_discount = $discount_value;
                    } elseif ($discount_type === 'percent') {
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

            // Insert order items
            $stm_cart_items = $_db->prepare("
                SELECT sc.product_id, sc.quantity, (sc.quantity * p.discounted_price) AS subtotal
                FROM shopping_cart sc
                JOIN products p ON sc.product_id = p.product_id
                WHERE sc.user_id = :user_id");
            $stm_cart_items->execute([':user_id' => $user_id]);
            $cart_items = $stm_cart_items->fetchAll(PDO::FETCH_OBJ);

            foreach ($cart_items as $item) {
                // Check if the item already exists for the order
                $stm_check = $_db->prepare("
                    SELECT COUNT(*) 
                    FROM order_items 
                    WHERE order_id = :order_id 
                    AND product_id = :product_id");
                $stm_check->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $item->product_id
                ]);
                
                if ($stm_check->fetchColumn() === 0) {
                    $stm_insert = $_db->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
                        VALUES (:order_id, :product_id, :quantity, :subtotal)");
                    $stm_insert->execute([
                        ':order_id' => $order_id,
                        ':product_id' => $item->product_id,
                        ':quantity' => $item->quantity,
                        ':subtotal' => $item->subtotal
                    ]);
                }
            }

            // Convert total amount after discount to cents (RM290.00 → 29000)
            $bill_amount = intval($total_amount_after_discount * 100);

            // ToyyibPay API details
            $api_key = "srlq0i5d-gfgf-ok6k-gy8j-92f18h7rhbw1"; // Replace with actual API key
            $category_code = "vsofdz4y"; // Replace with actual category code

            // Ensure callback URL is correct (HTTPS required for production)
            $callback_url = "https://relations-reality-karaoke-experiment.trycloudflare.com/pages/member/payment_callback.php"; 
            $return_url = "http://localhost:8000/pages/member/order_history.php";

            // Payment details
            $data = [
                'userSecretKey' => $api_key,
                'categoryCode' => $category_code,
                'billName' => "Order Payment to Furniture",
                'billDescription' => "Payment for order $order_id",
                'billAmount' => $bill_amount,
                'billReturnUrl' => $return_url,
                'billCallbackUrl' => $callback_url,
                'billExternalReferenceNo' => $order_id,
                'billTo' => $user->name,
                'billEmail' => $user->email,
                'billPhone' => $user->phonenum,
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
                $_db->rollBack(); // Rollback transaction if payment creation fails
                die("Error: Unable to create payment.");
            }

            $bill_code = $response_data[0]['BillCode'];
            $payment_url = "https://dev.toyyibpay.com/" . $bill_code;

            // Insert payment record as pending
            $insert_payment = $_db->prepare("
                INSERT INTO payments (order_id, user_id, amount, payment_status, bill_code, transaction_id) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $transaction_id = uniqid("txn_");
            $payment_status = 'Pending';
            $insert_payment->execute([$order_id, $user_id, $total_amount_after_discount, $payment_status, $bill_code, $transaction_id]);

            // Commit transaction if everything is successful
            $_db->commit();

            // Clear the shopping cart for the user
            $stm = $_db->prepare("DELETE FROM shopping_cart WHERE user_id = :user_id");
            $stm->execute([':user_id' => $user_id]);

            // Redirect to payment page
            header("Location: " . $payment_url);
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $_db->rollBack();
            die("Error: " . $e->getMessage());
        }
    } else {
        die("Invalid request method.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
