<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Mark profile as finally submitted
    $stmt = $conn->prepare("UPDATE users SET is_final_submitted = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo 'You have successfully Final Submitted !';
    } else {
        echo "Something went wrong. Try again!";
    }
}
?>
