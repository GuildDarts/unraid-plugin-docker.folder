<?php
    $folerName = $_POST["folderName"];
    $child = $_POST["child"];

    $foldersRaw = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
    $folders = json_decode($foldersRaw, true);

    if (($key = array_search($child, $folders[$folerName]['children'])) !== false) {
        unset($folders[$folerName]['children'][$key]);
        $folders[$folerName]['children'] = array_values($folders[$folerName]['children']);
    }

    $output = json_encode($folders, JSON_PRETTY_PRINT);
    file_put_contents('/boot/config/plugins/docker.folder/folders.json', $output);
?>