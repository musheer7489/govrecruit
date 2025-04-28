<?php
include '../config.php';
include '../mail.php';

$email = $_POST['email'];
$advertisement_number = $_POST['advertisement_number'];
$otp = rand(100000, 999999);
date_default_timezone_set('Asia/Kolkata');
$otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE JSON_EXTRACT(job_data, '$.advertisement_number')= ? AND email = ?");
$stmt->bind_param("ssss", $otp, $otp_expiry, $advertisement_number, $email);
$stmt->execute();
sendOTP($email, $otp);
echo "<div class='alert alert-success'>OTP sent to email. <a href='reset_password'>Reset Password</a></div>";
?>
