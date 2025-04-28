<?php
session_start();
include 'config.php';
include 'mail.php';
// Set headers first
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input'
    ]));
}

// Extract data
$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$mobile = $input['mobile'] ?? '';
$password = $input['password'] ?? '';
$posts = $input['posts'] ?? [];
$advertisement_number = $input['advertisement_number'] ?? '';
// Validate required fields
if (empty($name) || empty($email) || empty($posts)|| empty($mobile)|| empty($password)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]));
}

// Prepare job data (including advertisement number)
$job_data = [
    'advertisement_number' => $advertisement_number,
    'applications' => []
];

foreach ($posts as $post) {
    // Only add if we have all required fields
    if (!empty($post['id']) && !empty($post['title']) && isset($post['qualifications'])) {
        $job_data['applications'][] = [
            'post_id' => $post['id'],
            'post_title' => $post['title'],
            'qualifications' => $post['qualifications']
        ];
    }
}

// Convert to JSON
$job_data_json = json_encode($job_data);

$password = password_hash($password, PASSWORD_BCRYPT);
$otp = rand(100000, 999999);
date_default_timezone_set('Asia/Kolkata');
$otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE (email = ?  || mobile = ?) AND JSON_EXTRACT(job_data, '$.advertisement_number')= ? AND is_verified = 1");
$stmt->bind_param("sss", $email, $mobile, $advertisement_number);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email/Mobile already registered!"]);
    exit();
}

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (job_data, name, email, mobile, password, otp, otp_expiry, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("sssssss", $job_data_json, $name, $email, $mobile, $password, $otp, $otp_expiry);

if ($stmt->execute() && sendOTP($email, $otp)) {
    echo json_encode(["status" => "success", "email" => $email ,"advertisement_number"=>$advertisement_number]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed! Try again.".$stmt -> error]);
}
