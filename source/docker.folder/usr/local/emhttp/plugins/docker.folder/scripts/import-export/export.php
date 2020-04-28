<?php
$selection = json_decode($_GET["selection"]);
//$selection = ['random_web'];


$foldersRaw = file_get_contents("/boot/config/plugins/docker.folder/folders.json");
$folders = json_decode($foldersRaw, true);

echo json_encode($folders, JSON_PRETTY_PRINT);
?>