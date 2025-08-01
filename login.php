<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/secret.php';

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
