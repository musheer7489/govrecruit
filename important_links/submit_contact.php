<?php
header('Content-Type: application/json');

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'otp_register';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Validate and sanitize input
$required = ['firstName', 'lastName', 'email', 'subject', 'message'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }
}

$firstName = htmlspecialchars(trim($_POST['firstName']));
$lastName = htmlspecialchars(trim($_POST['lastName']));
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : null;
$subject = in_array($_POST['subject'], ['application', 'technical', 'eligibility', 'status', 'other']) 
            ? $_POST['subject'] 
            : 'other';
$message = htmlspecialchars(trim($_POST['message']));
$applicationId = isset($_POST['applicationId']) ? htmlspecialchars(trim($_POST['applicationId'])) : null;

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Insert into database
try {
    $stmt = $pdo->prepare("INSERT INTO contact_submissions 
                          (first_name, last_name, email, phone, subject, message, application_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $firstName, 
        $lastName, 
        $email, 
        $phone, 
        $subject, 
        $message, 
        $applicationId
    ]);
    
    // Send email notification (optional)
    // $to = "support@recruitment.gov";
    // $subject = "New Contact Form Submission: " . ucfirst($subject);
    // $body = "You have received a new message from $firstName $lastName ($email)\n\n";
    // $body .= "Subject: " . ucfirst($subject) . "\n";
    // $body .= "Message:\n$message\n\n";
    // $body .= "Application ID: " . ($applicationId ?: 'N/A') . "\n";
    // mail($to, $subject, $body);
    
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been submitted successfully. We will respond within 2 business days.'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while submitting your form. Please try again later.'
    ]);
}
?>