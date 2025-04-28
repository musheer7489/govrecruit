<?php
session_start();
include 'config.php';
require 'vendor/autoload.php';

use Razorpay\Api\Api;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details and personal_info JSON column
$sql = "SELECT name, email, mobile, personal_info FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || empty($user['personal_info'])) {
    echo json_encode(['status' => 'error', 'message' => 'User personal information not found']);
    exit;
}

// Decode JSON data
$personal_info = json_decode($user['personal_info'], true);

// Extract values
$category = $personal_info['category'] ?? null;
$gender = $personal_info['gender'] ?? null;
$disability = $personal_info['disability'] ?? null;
$exman = $personal_info['exman'] ?? null;

// Validate extracted data
if (!$category || !$gender || !isset($disability)) {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete personal information']);
    exit;
}

// Determine the payment amount
$amount = 100;
if ($gender === 'Female' || $disability === 'Yes' || $category ==='SC' || $category ==='ST' || $exman === 'Yes') {
    $amount = 50;
}
$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

// Create order
$orderData = [
    'receipt' => "ORDER_" . uniqid(),
    'amount' => $amount * 100, // Amount in paise
    'currency' => 'INR',
    'payment_capture' => 1
];
$order = $api->order->create($orderData);
if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create order']);
    exit;
}
// Return order details
echo json_encode([
    'status' => 'success',
    'order_id' => $order['id'],
    'amount' => $amount,
    'name' => $user['name'],
    'email' => $user['email'],
    'mobile' => $user['mobile']
]);
?>
