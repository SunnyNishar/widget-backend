<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/secret.php';

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

require_once __DIR__ . '/config/db.php';

$stmt = $conn->prepare("
  SELECT w.id, w.widget_name, w.rss_url, w.folder_id, w.settings, f.name AS folder_name
  FROM widgets w
  LEFT JOIN folders f ON w.folder_id = f.id
  WHERE w.user_id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$widgets = [];

while ($row = $result->fetch_assoc()) {
    $settings = json_decode($row['settings'], true);

    $widgets[] = [
        'id' => $row['id'],
        'widget_name' => $row['widget_name'],
        'rss_url' => $row['rss_url'],
        'folder_id' => $row['folder_id'],
        'folder_name' => $row['folder_name'],
        'layout' => $settings['layout'] ?? null,
        'font_style' => $settings['fontStyle'] ?? null,
        'text_align' => $settings['textAlign'] ?? null,
        'add_border' => $settings['addBorder'] ?? null,
        'border_color' => $settings['borderColor'] ?? null,
        'widthType' => $settings['widthType'] ?? 'responsive',
    'widthPixels' => $settings['widthPixels'] ?? 350,
    'heightType' => $settings['heightType'] ?? 'pixels',
    'heightPixels' => $settings['heightPixels'] ?? 400,
    'heightPosts' => $settings['heightPosts'] ?? 3,
    ];
}

echo json_encode($widgets);

$stmt->close();
$conn->close();
?>
