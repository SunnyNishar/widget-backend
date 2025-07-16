<?php
require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/db.php';

$categories = [];

// Fetch all widget categories
$result = $conn->query("SELECT * FROM widget_categories");

while ($row = $result->fetch_assoc()) {
    $row['image'] = "http://localhost/backend/" . $row['image'];
    $categories[] = $row;
}

$conn->close();

echo json_encode($categories);
?>
