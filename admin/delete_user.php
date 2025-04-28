<?php
include 'db.php';
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the ID is received
if (isset($_POST['id']) && !empty($_POST['id'])) {
  $itemIdToDelete = $_POST['id'];

  try {

    // Prepare the SQL DELETE statement
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $itemIdToDelete, PDO::PARAM_INT);

    // Execute the query
    if ($stmt->execute()) {
      $response = array('success' => true, 'message' => 'Item deleted successfully.');
    } else {
      $response = array('success' => false, 'message' => 'Error deleting item.');
    }

    // Close the database connection
    $conn = null;

  } catch(PDOException $e) {
    $response = array('success' => false, 'message' => 'Database error: ' . $e->getMessage());
  }

  // Send the JSON response back to jQuery
  header('Content-Type: application/json');
  echo json_encode($response);

} else {
  // If ID is not received
  $response = array('success' => false, 'message' => 'Invalid item ID.');
  header('Content-Type: application/json');
  echo json_encode($response);
}
?>