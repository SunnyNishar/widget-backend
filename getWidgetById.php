<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Widget ID is required"]);
    exit;
}

$widgetId = $_GET['id'];

// Replace with your actual database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "widget_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$sql = "SELECT * FROM widgets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $widgetId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Widget not found"]);
    $conn->close();
    exit;
}

$widget = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "widget" => $widget
]);

$stmt->close();
$conn->close();
?>