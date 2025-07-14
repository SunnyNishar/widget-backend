<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/secret.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

// Required fields
$requiredFields = ['widgetId', 'widgetName', 'fontStyle', 'textAlign', 'addBorder', 'borderColor', 'layout'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required field: $field"]);
        exit;
    }
}

// Optional fields: folderId or rssUrl — at least one required
$folderId = isset($input['folderId']) ? intval($input['folderId']) : null;
$rssUrl = isset($input['rssUrl']) ? trim($input['rssUrl']) : null;

if (!$folderId && !$rssUrl) {
    echo json_encode(["success" => false, "error" => "Either folderId or rssUrl must be provided"]);
    exit;
}

// Extract and sanitize values
$widgetId    = intval($input['widgetId']);
$widgetName  = trim($input['widgetName']);
$fontStyle   = trim($input['fontStyle']);
$textAlign   = trim($input['textAlign']);
$addBorder   = $input['addBorder'] ? 1 : 0;
$borderColor = trim($input['borderColor']);
$layout      = trim($input['layout']);

// DB connection
require_once __DIR__ . '/config/db.php';

// Check ownership
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

// Update query
$sql = "UPDATE widgets SET 
            folder_id = ?, 
            rss_url = ?, 
            widget_name = ?, 
            font_style = ?, 
            text_align = ?, 
            add_border = ?, 
            border_color = ?, 
            layout = ?
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssssssii",
    $folderId,
    $rssUrl,
    $widgetName,
    $fontStyle,
    $textAlign,
    $addBorder,
    $borderColor,
    $layout,
    $widgetId,
    $userId
);

// Execute and return result
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated successfully"]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to update widget: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
