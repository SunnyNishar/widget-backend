<?php
require_once __DIR__ . '/config/headers.php';

require_once __DIR__ . '/config/db.php';

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
