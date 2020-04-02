<?php
$foldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
$folders = json_decode($foldersRaw, true);

$folderName = $_POST["folderName"];

unset($folders[$folderName]);

$jsonData = json_encode($folders, JSON_PRETTY_PRINT);
file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);

?>