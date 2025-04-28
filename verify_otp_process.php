<?php
session_start();
include 'config.php';
include 'sendEmail.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $advertisement_number = trim($_POST['advertisement_number']);
    $otp = trim($_POST['otp']);

    if (empty($email) || empty($otp)) {
        echo json_encode(["status" => "error", "message" => "Invalid request!"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, name, otp, otp_expiry, is_verified FROM users WHERE email = ? AND JSON_EXTRACT(job_data, '$.advertisement_number')= ?");
    $stmt->bind_param("ss", $email, $advertisement_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows >= 1) {
        $row = $result->fetch_assoc();
        $storedOTP = $row['otp'];
        $otpExpiry = strtotime($row['otp_expiry']);
        $currentTime = time();
        $registerationNumber = $row['id'];
        $name = $row['name'];
        $subject = "Your registration has been successful";
        $companyName = COMPANY_NAME;
        $body = <<<EOT
        <!DOCTYPE html>
        <html>
        <head>
            <title>Registration Successful</title>
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
                .registration-details {
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
                <h1>Registration Successful!</h1>
                <p>Dear <strong>$name,</strong></p>
                <p>Your registration was successful. Here are your registration details:</p>
                <div class="registration-details">
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><strong>:  $email</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registration Number</span>
                        <span class="detail-value"><strong>:  $registerationNumber</strong></span>
                    </div>
                </div>
                <p>If you have any questions, please contact us.</p>
                <div class="footer">
                    <p>Sincerely,</p>
                    <p>Team $companyName</p>
                </div>
            </div>
        </body>
        </html>
        EOT;

        if ($row['is_verified'] == 1) {
            echo json_encode(["status" => "error", "message" => "Your account is already verified."]);
        } elseif ($currentTime > $otpExpiry) {
            echo json_encode(["status" => "error", "message" => "OTP expired! Please request a new one."]);
        } elseif ($otp == $storedOTP) {
            $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, otp = NULL, otp_expiry = NULL WHERE email = ? AND JSON_EXTRACT(job_data, '$.advertisement_number')= ?");
            $update_stmt->bind_param("ss", $email, $advertisement_number);
            if ($update_stmt->execute()) {
                //send Email for Registration confirmation
                sendSimpleEmail($email, $subject, $body);
                echo json_encode(["status" => "success", "message" => "OTP verified successfully! Redirecting..."]);
                $_SESSION['user_email'] = $email;
            } else {
                echo json_encode(["status" => "error", "message" => "Verification failed! Try again."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid OTP! Please try again."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found!"]);
    }
}
?>
