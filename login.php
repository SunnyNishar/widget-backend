<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/config/headers.php';

$secretKey = "bf27dc79d09b01ad34c7b33b7dbf0e259b7d7f3b778bc0d8da7b42627c8b5fa9";
$data = json_decode(file_get_contents("php://input"), true);

require_once __DIR__ . '/config/db.php';

$email = $data['email'];
$password = $data['password'];

$stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $payload = [
        "user_id" => $user['id'],
        "email" => $user['email'],
        "exp" => time() + (60*60), 
    ];
    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    echo json_encode(["success" => true, "token" => $jwt]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
}

$stmt->close();
$conn->close();
?>
