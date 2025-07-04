<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "widget_db");

if ($conn->connect_error) {
  echo json_encode(["success" => false, "error" => "DB connection failed"]);
  exit();
}

$result = $conn->query("SELECT id, widget_name FROM widgets");

$widgets = [];
while ($row = $result->fetch_assoc()) {
  $widgets[] = $row;
}

echo json_encode($widgets);

$conn->close();
?>
