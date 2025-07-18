<?php
require_once __DIR__ . '/config/headers.php';

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

require_once __DIR__ . '/config/db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Fetch widget by ID
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

// Decode JSON settings
$settings = json_decode($widget['settings'], true);
if (isset($settings['addBorder']) && !isset($settings['border'])) {
    $settings['border'] = $settings['addBorder'];
    unset($settings['addBorder']);
}
// Merge settings into the main widget array
if (is_array($settings)) {
    $widget = array_merge($widget, $settings);
}

echo json_encode([
    "success" => true,
    "widget" => $widget
]);

$stmt->close();
$conn->close();
?>
