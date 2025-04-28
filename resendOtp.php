<?php
session_start();
include 'config.php';
include 'mail.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $advertisement_number = trim($_POST['advertisement_number']);

    $otp = rand(100000, 999999);
    date_default_timezone_set('Asia/Kolkata');
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    $mailSent = sendOTP($email, $otp); 

    if ($mailSent) {
        $stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE JSON_EXTRACT(job_data, '$.advertisement_number')= ? AND email = ?");
        $stmt->bind_param("ssss", $otp, $otp_expiry, $advertisement_number, $email);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "New OTP sent! Check your email."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update OTP. Try again."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to send OTP email."]);
    }
}
?>
