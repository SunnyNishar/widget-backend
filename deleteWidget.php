<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);
$widgetId = $data["id"];

$conn = new mysqli("localhost", "root", "", "widget_db");
if ($conn->connect_error) {
  die(json_encode(["success" => false, "error" => "Connection failed"]));
}

$sql = "DELETE FROM widgets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $widgetId);

if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "error" => "Delete failed"]);
}

$stmt->close();
$conn->close();
