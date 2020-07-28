<?php
$import = json_decode("${_POST["import"]}",true);
//$importRaw = file_get_contents('/boot/config/plugins/docker.folder/import.json');
//$import = json_decode($importRaw, true);

$currentFoldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
$currentFolders = json_decode($currentFoldersRaw);

unset($import['foldersVersion']);

$obj_merged = (object) array_merge((array) $currentFolders, (array) $import);

$jsonData = json_encode($obj_merged, JSON_PRETTY_PRINT);
file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);
