<?php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'otp_register';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Query ID not provided']);
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM contact_submissions WHERE id = ?");
$stmt->execute([$id]);
$query = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$query) {
    echo json_encode(['success' => false, 'message' => 'Query not found']);
    exit;
}

echo json_encode(['success' => true, 'query' => $query]);
?>