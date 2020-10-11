<?php
$type = $_POST['type'];

if ($type !== 'vm') {
    $file = 'folders';
} else {
    $file = 'folders-vm';
}

$foldersRaw = file_get_contents("/boot/config/plugins/docker.folder/$file.json");
$dockerFolders = json_decode($foldersRaw);
$folders = $dockerFolders->folders;

$folderId = $_POST['folderId'];

unset($folders->$folderId);

$dockerFolders->folders = $folders;
$jsonData = json_encode($dockerFolders, JSON_PRETTY_PRINT);
file_put_contents("/boot/config/plugins/docker.folder/$file.json", $jsonData);

?>