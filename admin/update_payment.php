<?php
require_once '../config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $userId = $_POST['user_id'] ?? 0;
    $status = $_POST['status'] ?? 'success';
    $amount = $_POST['amount'] ?? 0;
    $transactionId = $_POST['transaction_id'] ?? null;
    $orderId = $_POST['order_id'] ?? null;

    // Validate input
    if (empty($userId) || empty($amount) || empty($transactionId)) {
        throw new Exception('Required fields are missing');
    }

    // Check if payment record exists
    $check = $conn->prepare("SELECT id FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $check->bind_param('i', $userId);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        // Update existing payment
        $stmt = $db->prepare("UPDATE payments SET 
                            payment_status = ?, 
                            amount = ?, 
                            transaction_id = ?, 
                            order_id = ?,
                            created_at = NOW()
                            WHERE id = ?");
        $stmt->bind_param('sdssi', $status, $amount, $transactionId, $orderId, $exists['id']);
    } else {
        // Create new payment record
        $stmt = $db->prepare("INSERT INTO payments 
                            (user_id, payment_status, amount, transaction_id, order_id, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('isdss', $userId, $status, $amount, $transactionId, $orderId);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Payment record updated successfully';
    } else {
        throw new Exception('Database error: ' . $stmt->error);
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);