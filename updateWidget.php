<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/headers.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$secretKey = "bf27dc79d09b01ad34c7b33b7dbf0e259b7d7f3b778bc0d8da7b42627c8b5fa9"; // same key as in login.php

// Validate and decode token
$headers = getallheaders();
$authHeader = $headers["Authorization"] ?? "";
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(["success" => false, "error" => "Token not provided"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    $userId = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Invalid or expired token"]);
    exit;
}

// Get JSON input
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

// Extract values
$widgetId = intval($input['widgetId']);
$folderId = intval($input['folderId']);
$widgetName = $input['widgetName'];
$fontStyle = $input['fontStyle'];
$textAlign = $input['textAlign'];
$addBorder = $input['addBorder'] ? 1 : 0;
$borderColor = $input['borderColor'];
$layout = $input['layout'];

// Connect to DB
require_once __DIR__ . '/config/db.php';

// 🛡 Verify that the widget belongs to the logged-in user
$checkStmt = $conn->prepare("SELECT id FROM widgets WHERE id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $widgetId, $userId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Unauthorized or widget not found"]);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// ✅ Proceed with update
$sql = "UPDATE widgets SET 
            folder_id = ?, 
            widget_name = ?, 
            font_style = ?, 
            text_align = ?, 
            add_border = ?, 
            border_color = ?, 
            layout = ?
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssissii", $folderId, $widgetName, $fontStyle, $textAlign, $addBorder, $borderColor, $layout, $widgetId, $userId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated successfully"]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to update widget: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
