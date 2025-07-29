<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/secret.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get JWT from Authorization header
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
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id"])) {
    echo json_encode(["success" => false, "error" => "Missing widget ID"]);
    exit;
}

$widgetId = intval($data["id"]);

// Connect to DB
require_once __DIR__ . '/config/db.php';

// 🛡️ Check if widget belongs to the logged-in user
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

// Proceed to delete
$stmt = $conn->prepare("DELETE FROM widgets WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $widgetId, $userId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Delete failed"]);
}

$stmt->close();
$conn->close();
?>