<?php
require_once __DIR__ . '/config/headers.php';

$data = json_decode(file_get_contents("php://input"), true);

require_once __DIR__ . '/config/db.php';

$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $password);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Email may already be taken"]);
}
$conn->close();
?>
