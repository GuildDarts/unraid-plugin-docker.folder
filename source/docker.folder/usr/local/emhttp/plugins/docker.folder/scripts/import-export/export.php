<?php
$selection = json_decode($_GET["selection"]);

$dockerFoldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
$dockerFolders = json_decode($dockerFoldersRaw);

unset($dockerFolders->settings);

foreach ($dockerFolders->folders as $folderKey => &$folder) {
    if (!in_array($folderKey, $selection)) {
        unset($dockerFolders->folders->$folderKey);
    }
}

echo json_encode($dockerFolders, JSON_PRETTY_PRINT);
?>