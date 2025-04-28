<?php
session_start();
include '../config.php';
require '../vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(["status" => "danger", "message" => "Email not registered"]);
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    date_default_timezone_set('Asia/Kolkata');
    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // Save OTP in DB
    $stmt = $conn->prepare("UPDATE users SET reset_otp = ?, reset_otp_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $otp, $expiry, $email);
    $stmt->execute();

    // Send Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'rajpoot8445@gmail.com'; // Your SMTP email
        $mail->Password = 'nqbslisopcmvzmqn'; // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('Noreply@mail.com', 'Support');
        $mail->addAddress($email);
        $mail->Subject = "Password Reset OTP";
        $mail->Body = "Your OTP for password reset is: $otp. This OTP is valid for 5 minutes.";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "OTP sent to your email"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "danger", "message" => "Email failed to send"]);
    }
}
?>
