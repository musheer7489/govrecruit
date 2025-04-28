<?php
session_start();
include 'config.php';
include 'sendEmail.php';
require 'vendor/autoload.php';

use Razorpay\Api\Api;

$data = json_decode(file_get_contents("php://input"), true);
// Validate request data
if (!isset($_SESSION['user_id']) || !isset($data['payment_id']) || !isset($data['order_id']) || !isset($data['amount'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_id = $data['payment_id'];
$order_id = $data['order_id'];
$amount = $data['amount'];

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

// Fetch user's profile completion status
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$RegistrationNumber = $user['id'];
$email = $user['email'];
$name = $user['name'];
$paymentDate = date('d/m/Y');
$companyName = COMPANY_NAME;

$subject = 'Payment Confirmation - ' . '#' .  $payment_id;
$body = <<<EOT
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Confirmation</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f8f9fa;
                    padding: 20px;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
                }
                h1 {
                    color: #28a745; /* Green for success */
                    margin-bottom: 20px;
                }
                .payment-details {
                    margin-top: 25px;
                    padding-top: 15px;
                    border-top: 1px solid #e0e0e0;
                }
                .detail-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }
                .detail-label {
                    font-weight: bold;
                }
                .detail-value {
                    text-align: right;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 0.9em;
                    color: #6c757d;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Payment Successful!</h1>
                <p>Dear <strong>$name,</strong></p>
                <p>Thank you for your payment. We have received your payment successfully. Here are the payment details:</p>
                <div class="payment-details">
                    <div class="detail-row">
                        <span class="detail-label">Registration Number</span>
                        <span class="detail-value">: $RegistrationNumber</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment ID</span>
                        <span class="detail-value">: $payment_id</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Date</span>
                        <span class="detail-value">: $paymentDate</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Amount</span>
                        <span class="detail-value">: ₹$amount</span>
                    </div>
                </div>
                <p>Your transaction has been completed. If you have any questions, please contact us.</p>
                <div class="footer">
                    <p>Sincerely,</p>
                    <p>Team $companyName</p>
                </div>
            </div>
        </body>
        </html>
        EOT;
try {
    // Fetch payment details from Razorpay
    $payment = $api->payment->fetch($payment_id);

    // Check if the payment is successful
    if ($payment->status == "captured") {
        $payment_status = "success";
    } else {
        $payment_status = "failed";
    }

    // Store transaction in the database
    $sql = "INSERT INTO payments (user_id, amount, payment_status, transaction_id, order_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idsss", $user_id, $amount, $payment_status, $payment_id, $order_id);

    if ($stmt->execute()) {
        sendSimpleEmail($email, $subject, $body);
        echo json_encode(['status' => $payment_status, 'message' => 'Payment ' . ucfirst($payment_status)]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Payment verification failed: ' . $e->getMessage()]);
}
?>