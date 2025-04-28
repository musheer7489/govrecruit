<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = $_POST['otp'];

    $stmt = $conn->prepare("SELECT id, reset_otp_expiry FROM users WHERE reset_otp = ?");
    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || strtotime($user['reset_otp_expiry']) < time()) {
        echo json_encode(["status" => "danger", "message" => "Invalid or expired OTP"]);
        exit;
    }

    $_SESSION['reset_user_id'] = $user['id'];
    echo json_encode(["status" => "success", "message" => "OTP verified"]);
}
?>
