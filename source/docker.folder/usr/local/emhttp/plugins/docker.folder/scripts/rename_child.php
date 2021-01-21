<?php
$type = $_POST['type'];
$nameOld = $_POST['nameOld'];
$nameNew = $_POST['nameNew'];


if ($type !== 'vm') {
    $file = 'folders';
} else {
    $file = 'folders-vm';
}

$foldersRaw = file_get_contents("/boot/config/plugins/docker.folder/$file.json");
$dockerFolders = json_decode($foldersRaw);

foreach ($dockerFolders->folders as $folderKey => &$folder) {
    foreach ($folder->children as &$child) {
        if ($child === $nameOld) {
            $child = $nameNew;
            break 2;
        }
    }
}

$jsonData = json_encode($dockerFolders, JSON_PRETTY_PRINT);
file_put_contents("/boot/config/plugins/docker.folder/$file.json", $jsonData);

?>