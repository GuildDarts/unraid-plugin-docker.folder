<?php
$folderFile = $_POST['folderFile'];
$response = json_decode($_POST['settings']);

$dockerFoldersRaw = file_get_contents("/boot/config/plugins/docker.folder/$folderFile.json");
$dockerFolders = json_decode($dockerFoldersRaw);

$dockerFolders->settings = $response;

$jsonData = json_encode($dockerFolders, JSON_PRETTY_PRINT);
file_put_contents("/boot/config/plugins/docker.folder/$folderFile.json", $jsonData);
?>