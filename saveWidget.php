<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"));

$conn = new mysqli("localhost", "root", "", "widget_db");

if ($conn->connect_error) {
  echo json_encode(["success" => false, "error" => "DB connection failed"]);
  exit();
}

// Validate required fields
if (
  !isset($data->user_id) || !isset($data->widgetName) || !isset($data->folderId) ||
  !isset($data->layout) || !isset($data->fontStyle) || !isset($data->textAlign) ||
  !isset($data->addBorder) || !isset($data->borderColor)
) {
  echo json_encode(["success" => false, "error" => "Missing required fields"]);
  exit();
}

// Extract values
$userId = intval($data->user_id);
$widgetName = $data->widgetName;
$folderId = intval($data->folderId);
$layout = $data->layout;
$fontStyle = $data->fontStyle;
$textAlign = $data->textAlign;
$addBorder = $data->addBorder;
$borderColor = $data->borderColor;
$checkStmt = $conn->prepare("SELECT id FROM widgets WHERE user_id = ? AND widget_name = ?");
$checkStmt->bind_param("is", $userId, $widgetName);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
  echo json_encode(["success" => false, "error" => "Widget name already exists. Please choose a different name."]);
  $checkStmt->close();
  $conn->close();
  exit();
}
$checkStmt->close();

// Insert with user_id
$stmt = $conn->prepare("INSERT INTO widgets (user_id, folder_id, widget_name, font_style, text_align, add_border, border_color, layout) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissssss", $userId, $folderId, $widgetName, $fontStyle, $textAlign, $addBorder, $borderColor, $layout);

if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
