<?php
session_start();
include 'config.php';

$email = $_POST['email'];
$password = $_POST['password'];
$advertisement_number = $_POST['advertisement_number'];

$stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR mobile = ?) AND JSON_EXTRACT(job_data, '$.advertisement_number')= ? AND is_verified = 1");
$stmt->bind_param("sss", $email, $email, $advertisement_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        echo "<div class='alert alert-danger'>Account locked. Try again later.</div>";
        exit;
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $conn->query("UPDATE users SET failed_attempts = 0 WHERE id = " . $user['id']);
        echo "<script>window.location='dashboard.php';</script>";
    } else {
        $failed_attempts = $user['failed_attempts'] + 1;
        $lock_time = ($failed_attempts >= 5) ? ", locked_until = NOW() + INTERVAL 10 MINUTE" : "";
        $conn->query("UPDATE users SET failed_attempts = $failed_attempts $lock_time WHERE id = " . $user['id']);
        echo "<div class='alert alert-danger'>Incorrect password. " . (5 - $failed_attempts) . " attempts left.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Email/Mobile not registered or not verified. If the email is not verified then go to Forgot Password and verify it again</div>";
}
?>
