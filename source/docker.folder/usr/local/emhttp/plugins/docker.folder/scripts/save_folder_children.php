<?php
    $folerName = $_POST["folderName"];
    $children = json_decode("${_POST["children"]}",true);

    $foldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
    $folders = json_decode($foldersRaw, true);

    $folders[$folerName]['children'] = $children;

    $output = json_encode($folders, JSON_PRETTY_PRINT);
    file_put_contents('/boot/config/plugins/docker.folder/folders.json', $output);
?>