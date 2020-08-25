<?php
    $type = $_POST['type'];
    $folerName = $_POST['folderName'];
    $child = $_POST['child'];

    if ($type !== 'vm') {
        $file = 'folders';
    } else {
        $file = 'folders-vm';
    }

    $foldersRaw = file_get_contents("/boot/config/plugins/docker.folder/$file.json");
    $dockerFolders = json_decode($foldersRaw);
    $folders = $dockerFolders->folders;

    if (($key = array_search($child, $folders->$folerName->children)) !== false) {
        unset($folders->$folerName->children[$key]);
        $folders->$folerName->children = array_values($folders->$folerName->children);
    }

    $dockerFolders->folders = $folders;
    $output = json_encode($dockerFolders, JSON_PRETTY_PRINT);
    file_put_contents("/boot/config/plugins/docker.folder/$file.json", $output);
?>