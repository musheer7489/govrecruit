<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$uploadDir = "uploads/";
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$maxSize = 50 * 1024; // 50kb

$response = [];

// Fetch existing JSON data
$result = $conn->query("SELECT photo_signature FROM users WHERE id = $user_id");
$row = $result->fetch_assoc();
$photo_signature = json_decode($row['photo_signature'], true) ?: [];

function uploadFile($file, $fieldName, $userId) {
    global $uploadDir, $allowedTypes, $maxSize, $conn, $photo_signature;

    if (!isset($_FILES[$fieldName])) {
        return null;
    }

    $fileTmpPath = $_FILES[$fieldName]['tmp_name'];
    $fileName = $_FILES[$fieldName]['name'];
    $fileSize = $_FILES[$fieldName]['size'];
    $fileType = $_FILES[$fieldName]['type'];

    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
        return "Invalid file type for $fieldName. Only JPG, JPEG, PNG allowed.";
    }

    // Validate file size
    if ($fileSize > $maxSize) {
        return "$fieldName exceeds 50kb size limit.";
    }

    // Generate unique file name
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = $userId . "_$fieldName." . $fileExtension;
    $destPath = $uploadDir . $newFileName;

    // Move uploaded file
    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        return "Failed to upload $fieldName.";
    }

    // Update JSON data
    $photo_signature[$fieldName] = $newFileName;
    $photo_signature_json = json_encode($photo_signature);

    // Update database
    $stmt = $conn->prepare("UPDATE users SET photo_signature = ? WHERE id = ?");
    $stmt->bind_param("si", $photo_signature_json, $userId);
    $stmt->execute();

    return null;
}

// Process Photo Upload
$photoError = uploadFile($_FILES, 'photo', $user_id);
$signatureError = uploadFile($_FILES, 'signature', $user_id);

if ($photoError) {
    $response['error'] = $photoError;
} elseif ($signatureError) {
    $response['error'] = $signatureError;
} else {
    $response['success'] = true;
}

echo json_encode($response);
?>
