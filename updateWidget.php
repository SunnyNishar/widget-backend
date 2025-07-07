<?php
ini_set('display_errors', 0); // hide errors from output
ini_set('log_errors', 1);     // log errors to server log
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON input"]);
    exit;
}

$requiredFields = ['widgetId', 'folderId', 'widgetName', 'fontStyle', 'textAlign', 'addBorder', 'borderColor', 'layout'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required field: $field"]);
        exit;
    }
}

$widgetId = $input['widgetId'];
$folderId = $input['folderId'];
$widgetName = $input['widgetName'];
$fontStyle = $input['fontStyle'];
$textAlign = $input['textAlign'];
$addBorder = $input['addBorder'] ? 1 : 0;
$borderColor = $input['borderColor'];
$layout = $input['layout'];

// Replace with your actual database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "widget_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$sql = "UPDATE widgets SET 
            folder_id = ?, 
            widget_name = ?, 
            font_style = ?, 
            text_align = ?, 
            add_border = ?, 
            border_color = ?, 
            layout = ?
            -- updated_at = NOW()
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssissi", $folderId, $widgetName, $fontStyle, $textAlign, $addBorder, $borderColor, $layout, $widgetId);


if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated successfully"]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to update widget: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>