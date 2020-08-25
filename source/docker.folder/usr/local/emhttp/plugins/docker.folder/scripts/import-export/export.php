<?php
$folderFile = $_POST['folderFile'];
$selection = json_decode($_POST['selection']);

$dockerFoldersRaw = file_get_contents("/boot/config/plugins/docker.folder/$folderFile.json");
$dockerFolders = json_decode($dockerFoldersRaw);

unset($dockerFolders->settings);

foreach ($dockerFolders->folders as $folderKey => &$folder) {
    if (!in_array($folderKey, $selection)) {
        unset($dockerFolders->folders->$folderKey);
    }
}

echo json_encode($dockerFolders, JSON_PRETTY_PRINT);
?>