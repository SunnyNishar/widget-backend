<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "widget_db");
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

$folderId = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

$sql = "SELECT feeds.*, folders.name AS folder_name 
        FROM feeds 
        JOIN folders ON feeds.folder_id = folders.id";

if ($folderId > 0) {
    $sql .= " WHERE feeds.folder_id = $folderId";
}


$result = $conn->query($sql);

$feeds = [];
while ($row = $result->fetch_assoc()) {
    $feeds[] = $row;
}

echo json_encode($feeds);
$conn->close();
?>
