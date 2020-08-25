<?php
$folderFile = $_POST['folderFile'];
$import = json_decode($_POST['importFolder']);
// $folderFile = 'folders-vm';
// $importRaw = file_get_contents('/boot/config/plugins/docker.folder/import.json');
// $import = json_decode($importRaw);

$currentFoldersRaw = file_get_contents("/boot/config/plugins/docker.folder/$folderFile.json");
$currentFolders = json_decode($currentFoldersRaw);

unset($import->foldersVersion);

if (isset($import->folders)) {
    $currentFolders->folders = (object) array_merge((array) $currentFolders->folders, (array) $import->folders);
} else {
    $currentFolders->folders = (object) array_merge((array) $currentFolders->folders, (array) $import);
}

$jsonData = json_encode($currentFolders, JSON_PRETTY_PRINT);
file_put_contents("/boot/config/plugins/docker.folder/$folderFile.json", $jsonData);
