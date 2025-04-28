<?php
session_start();
include '../config.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['reset_user_id'])) {
        echo json_encode(["status" => "danger", "message" => "Session expired! Try again."]);
        exit;
    }

    $user_id = $_SESSION['reset_user_id'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL, is_verified = 1, reset_otp = NULL, reset_otp_expiry = NULL WHERE id = ?");
    $stmt->bind_param("si", $new_password, $user_id);
    
    if ($stmt->execute()) {
        unset($_SESSION['reset_user_id']); // Remove session after successful reset
        echo json_encode(["status" => "success", "message" => "Password reset successfully! Redirecting to login..."]);
    } else {
        echo json_encode(["status" => "danger", "message" => "Password reset failed! Try again."]);
    }
}
?>
