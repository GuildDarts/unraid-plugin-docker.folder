<?php
require_once("/usr/local/emhttp/plugins/docker.folder/include/folderVersion.php");
$path = '/boot/config/plugins/docker.folder/';
$file = $_POST['file'];

if (isset($file)) {
    main($path, $file);
} else {
    main($path, 'folders');
    main($path, 'folders-vm');
}

function main($path, $file) {
    if (file_exists("$path$file.json") == false) {
        $jsonData = new stdClass;
        $jsonData->foldersVersion = $GLOBALS['foldersVersion'];
        $jsonData->settings = new stdClass;
        $jsonData->folders = new stdClass;
        
        if ($file !== 'folders-vm')  {
            $jsonData->settings->fix_docker_page_shifting = false;
            $jsonData->settings->nuke_uptime_column = false;
        } else {

        }

        $jsonOut = json_encode($jsonData, JSON_PRETTY_PRINT);
        file_put_contents("$path$file.json", $jsonOut);
        echo "$file.json created", PHP_EOL;
    } else {
        echo "$file.json already exists", PHP_EOL;
    }
}

?>