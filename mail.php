<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'config.php';

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $companyName = COMPANY_NAME;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use your SMTP provider
        $mail->SMTPAuth = true;
        $mail->Username = 'rajpoot8445@gmail.com'; 
        $mail->Password = 'nqbslisopcmvzmqn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('noreply@gmail.com', COMPANY_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = <<<EOT
        <!DOCTYPE html>
        <html>
        <head>
            <title>OTP Verification</title>
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
                    color: #007bff;
                    margin-bottom: 20px;
                }
                .otp-details {
                    margin-top: 25px;
                    padding-top: 15px;
                    border-top: 1px solid #e0e0e0;
                }
                .otp-value {
                    font-size: 1.5em;
                    font-weight: bold;
                    color: #28a745;
                    margin-bottom: 10px;
                    text-align: center;
                }
                .validity {
                    font-size: 0.9em;
                    color: #6c757d;
                    text-align: center;
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
                <h1>OTP Verification</h1>
                <p>One-Time Password (OTP) to verify your account:</p>
                <div class="otp-details">
                    <p class="otp-value">$otp</p>
                    <p class="validity">This OTP is valid for 5 minutes.</p>
                </div>
                <p>For your security, please do not share this OTP with anyone.</p>
                <p>If you did not request this OTP, please contact us immediately.</p>
                <div class="footer">
                    <p>Sincerely,</p>
                    <p>Team $companyName</p>
                </div>
            </div>
        </body>
        </html>
        EOT;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
