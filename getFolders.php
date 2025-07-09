<?php
require_once __DIR__ . '/config/headers.php';

require_once __DIR__ . '/config/db.php';

$sql = "SELECT * FROM folders";
$result = mysqli_query($conn,$sql);

$folders = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $folders[] = $row;
    }
}

mysqli_close($conn);

echo json_encode($folders);
?>
