<?php
require_once '../_base.php';

auth_user();
auth('member');

$user_id = $_SESSION['user']->user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $address_id = $_POST['address_id'] ?? null;
        $address_line1 = $_POST['address-line1'] ?? '';
        $address_line2 = $_POST['address-line2'] ?? '';
        $city = $_POST['city'] ?? '';
        $country = $_POST['country'] ?? '';
        $postal_code = $_POST['postcode'] ?? '';

        // Validate input
        if (empty($address_line1) || empty($city) || empty($country) || empty($postal_code)) {
            throw new Exception("All required fields must be filled.");
        }

        // Validate postal code format
        if (!preg_match('/^[0-9]{5}$/', $postal_code)) {
            throw new Exception("Invalid postal code format. Must be 5 digits.");
        }

        // Start transaction
        $_db->beginTransaction();

        // Update the address
        if ($address_id) {
            // Update existing address
            $stmt = $_db->prepare("
                UPDATE address 
                SET address_line1 = ?, address_line2 = ?, city = ?, country = ?, postal_code = ?
                WHERE address_id = ? AND user_id = ?
            ");
            $stmt->execute([
                $address_line1,
                $address_line2,
                $city,
                $country,
                $postal_code,
                $address_id,
                $user_id
            ]);
        } else {
            // Insert new address
            $stmt = $_db->prepare("
                INSERT INTO address (user_id, address_line1, address_line2, city, country, postal_code)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $address_line1,
                $address_line2,
                $city,
                $country,
                $postal_code
            ]);
        }

        // Commit transaction
        $_db->commit();
        
        temp('info', 'Address updated successfully.');
        header('Location: ../pages/member/member_address.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($_db->inTransaction()) {
            $_db->rollBack();
        }
        temp('error', $e->getMessage());
        header('Location: ../pages/member/member_address.php');
        exit;
    }
} else {
    // If not POST request, redirect back
    header('Location: ../pages/member/member_address.php');
    exit;
} 