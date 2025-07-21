<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");
header("X-Frame-Options: ALLOWALL");
header("Content-Security-Policy: frame-ancestors *");
?>
