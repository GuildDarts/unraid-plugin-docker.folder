<?php
$import = json_decode("${_POST["import"]}",true);
//$importRaw = file_get_contents('/boot/config/plugins/docker.folder/import.json');
//$import = json_decode($importRaw, true);

$currentFoldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
$currentFolders = json_decode($currentFoldersRaw, true);

$folderIds = new stdClass();

// save import foldersVersion and remove it from obj
$importFoldersVersion = $import['foldersVersion'];
unset($import['foldersVersion']);



// check if folder exist already. save there id if they do - remove id if import has one
foreach ($currentFolders as $cFolderKey => &$cFolder) {
    foreach ($import as $iFolderKey => &$iFolder) {
        if ($iFolder['id'] !== null) {
            unset($iFolder['id']);
        }

        if ($cFolderKey == $iFolderKey) {
            $folderIds->$cFolderKey = $cFolder['id'];
        }
    }
}

// re-add the id to imports - check if id is null. if null create new container
if (count((array) $folderIds) > 0) {
    $ids = get_object_vars($folderIds);
    foreach ($import as $folderKey => &$folder) {
        $folder['id'] = $ids[$folderKey];

        if ($folder['id'] == null) {
            exec("docker create --name='$folderKey-folder' --net='none' 'tianon/true:latest' ");
            exec("docker ps -a --filter 'name=$folderKey-folder' --format '{{.ID}}' ", $newId);
            $folder['id'] = implode("",$newId);
        }
    }
}



$obj_merged = (object) array_merge((array) $currentFolders, (array) $import);

$jsonData = json_encode($obj_merged, JSON_PRETTY_PRINT);
file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);
