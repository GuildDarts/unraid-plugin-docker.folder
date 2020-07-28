<?php
require_once("/usr/local/emhttp/plugins/docker.folder/include/folderVersion.php");
$path = '/boot/config/plugins/docker.folder/folders.json';

if (file_exists($path) == false) {
    $jsonData = new stdClass;
    $jsonData->foldersVersion = $GLOBALS['foldersVersion'];
    $jsonData->settings = new stdClass;
    $jsonData->settings->fix_docker_page_shifting = false;
    $jsonData->folders = new stdClass;
    $jsonOut = json_encode($jsonData, JSON_PRETTY_PRINT);
    file_put_contents($path, $jsonOut);
    echo 'folders.json created';
} else {
    echo 'folders.json already exists';
}
?>