<?php
$response = json_decode("${_POST["settings"]}",true);

$dockerFoldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
$dockerFolders = json_decode($dockerFoldersRaw);

$dockerFolders->settings = $response;

$jsonData = json_encode($dockerFolders, JSON_PRETTY_PRINT);
file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);
?>