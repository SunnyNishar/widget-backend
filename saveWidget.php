<?php
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

// Prepare your values
$widgetName = $data->widgetName;
$folderId = $data->folderId;
$layout = $data->layout;
$fontStyle = $data->fontStyle;
$textAlign = $data->textAlign;
$addBorder = $data->addBorder;
$borderColor = $data->borderColor;

// Prepare SQL with widget_name and layout included
$stmt = $conn->prepare("INSERT INTO widgets (folder_id, widget_name, font_style, text_align, add_border, border_color, layout) VALUES (?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("issssss", $folderId, $widgetName, $fontStyle, $textAlign, $addBorder, $borderColor, $layout);

if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
