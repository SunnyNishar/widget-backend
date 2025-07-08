<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Allow POST requests from frontend
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Connect to DB
$conn = new mysqli("localhost", "root", "", "widget_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit();
}

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["success" => false, "error" => "Missing user_id"]);
    exit();
}

$user_id = intval($data['user_id']);

// Fetch widgets only for this user
$stmt = $conn->prepare("SELECT id, widget_name FROM widgets WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$widgets = [];

while ($row = $result->fetch_assoc()) {
    $widgets[] = $row;
}

echo json_encode($widgets);

$stmt->close();
$conn->close();
?>
