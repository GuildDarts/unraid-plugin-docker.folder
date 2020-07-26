<?php
$foldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
$dockerFolders = json_decode($foldersRaw, true);
$folders = $dockerFolders['folders'];

$folderName = $_POST["folderName"];

unset($folders[$folderName]);

$dockerFolders['folders'] = $folders;
$jsonData = json_encode($dockerFolders, JSON_PRETTY_PRINT);
file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);

?>