<?php
require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/db.php';

$folderId = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;
$feeds = [];

// 1. Get all folders and build a folder map: [folder_id => folder_name]
$folderMap = [];
$resultFolders = $conn->query("SELECT id, name FROM folders");
while ($folder = $resultFolders->fetch_assoc()) {
    $folderMap[$folder['id']] = $folder['name'];
} 

// 2. Get feeds 
if ($folderId > 0) {
    $stmt = $conn->prepare("SELECT * FROM feeds WHERE folder_id = ?");
    $stmt->bind_param("i", $folderId);
} else {
    $stmt = $conn->prepare("SELECT * FROM feeds");
}

$stmt->execute();
$resultFeeds = $stmt->get_result();

while ($row = $resultFeeds->fetch_assoc()) {
    $row['folder_name'] = $folderMap[$row['folder_id']] ?? null;
    $feeds[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($feeds);
?>
