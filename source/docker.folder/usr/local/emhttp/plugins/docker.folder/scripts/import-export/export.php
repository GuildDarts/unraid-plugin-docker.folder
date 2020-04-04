<?php
$selection = json_decode($_GET["selection"]);
//$selection = ['random_web'];


$foldersRaw = file_get_contents("/boot/config/plugins/docker.folder/folders.json");
$folders = json_decode($foldersRaw, true);

// save import foldersVersion and remove it from obj
$importFoldersVersion = $folders['foldersVersion'];

// remove all not in selection - remove id from selection
foreach ($folders as $folderKey => &$folder) {
    if (!in_array($folderKey, $selection)) {
        unset($folders[$folderKey]);
    } else {
        unset($folder['id']);
    }
}

// re-add foldersVersion
$folders['foldersVersion'] = $importFoldersVersion;

echo json_encode($folders, JSON_PRETTY_PRINT);
?>