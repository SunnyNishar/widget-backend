<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/config/headers.php';

$secretKey = "bf27dc79d09b01ad34c7b33b7dbf0e259b7d7f3b778bc0d8da7b42627c8b5fa9"; // same as in login.php

// Step 1: Extract JWT from Authorization header
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(["success" => false, "error" => "Missing or invalid Authorization header"]);
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

// Step 2: Connect to database
require_once __DIR__ . '/config/db.php';

// Step 3: Fetch widgets for authenticated user
$stmt = $conn->prepare("SELECT id, widget_name FROM widgets WHERE user_id = ?");
$stmt->bind_param("i", $userId);
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
