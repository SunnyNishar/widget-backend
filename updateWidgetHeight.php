<?php
require_once __DIR__ . '/config/db.php'; // your DB connection
require_once __DIR__ . '/config/headers.php';

$data = json_decode(file_get_contents("php://input"), true);

// Check if JSON decode was successful
if ($data === null) {
  echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
  exit;
}

$widgetId = isset($data['widgetId']) ? $data['widgetId'] : null;
$actualHeight = isset($data['actualHeight']) ? $data['actualHeight'] : null; // Changed from 'calculatedHeight' to 'actualHeight'

if (!$widgetId || !$actualHeight) {
  echo json_encode(['success' => false, 'error' => 'Missing widgetId or actualHeight']);
  exit;
}

// Validate that values are numeric
if (!is_numeric($widgetId) || !is_numeric($actualHeight)) {
  echo json_encode(['success' => false, 'error' => 'Invalid data types']);
  exit;
}

$stmt = $conn->prepare("UPDATE widgets SET actualHeight = ? WHERE id = ?");
$stmt->bind_param("ii", $actualHeight, $widgetId); // Use $actualHeight instead of $calculatedHeight

if ($stmt->execute()) {
  // Check if any rows were actually updated
  if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Height updated successfully']);
  } else {
    echo json_encode(['success' => false, 'error' => 'No widget found with that ID']);
  }
} else {
  echo json_encode(['success' => false, 'error' => 'DB update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>