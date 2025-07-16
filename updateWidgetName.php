<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/secret.php';

// JWT check
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(["success" => false, "error" => "Missing token"]);
    exit();
}
$jwt = $matches[1];
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    $userId = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$widgetId = $data['id'] ?? null;
$newName = $data['widget_name'] ?? '';

if (!$widgetId || !$newName) {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit();
}

// DB
require_once __DIR__ . '/config/db.php';
$stmt = $conn->prepare("UPDATE widgets SET widget_name = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $newName, $widgetId, $userId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "DB error"]);
}

$stmt->close();
$conn->close();
?>
