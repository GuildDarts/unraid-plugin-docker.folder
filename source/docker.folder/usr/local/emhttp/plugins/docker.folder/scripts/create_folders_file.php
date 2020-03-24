<?php

$path = '/boot/config/plugins/docker.folder/folders.json';

if (file_exists($path) == false) {
    $jsonData = new stdClass;
    $jsonData->foldersVersion = 2;
    $jsonOut = json_encode($jsonData);
    file_put_contents($path, $jsonOut);
    echo 'folders.json created';
} else {
    echo 'folders.json already exists';
}
?>