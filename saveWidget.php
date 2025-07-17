<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/secret.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(["success" => false, "error" => "Token not provided"]);
    exit();
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    $userId = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Invalid or expired token"]);
    exit();
}

// FIX: Decode as associative array instead of object
$data = json_decode(file_get_contents("php://input"), true);

require_once __DIR__ . '/config/db.php';

// Validate required fields
if (!isset($data['widgetName']) ||
    !isset($data['layout']) ||
    !isset($data['customSettings'])
) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$widgetName = $data['widgetName'];
$folderId = isset($data['folderId']) ? intval($data['folderId']) : null;
$rssUrl = isset($data['rssUrl']) ? trim($data['rssUrl']) : null;
$layout = $data['layout'];

// FIX: Handle settings properly as array
$settings = $data['customSettings'];
$settingsJson = json_encode($settings);

// Validate: at least one of folderId or rssUrl must be present
if (!$folderId && !$rssUrl) {
    echo json_encode(["success" => false, "error" => "Folder ID or RSS URL must be provided"]);
    exit();
}

// Check for duplicate name for same user
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

// Insert widget
$stmt = $conn->prepare("INSERT INTO widgets (user_id, folder_id, rss_url, widget_name, layout, settings) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissss", $userId, $folderId, $rssUrl, $widgetName, $layout, $settingsJson);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
file_put_contents("debug_payload.txt", print_r($data, true));

$stmt->close();
$conn->close();
?>