<?php

$path = '/boot/config/plugins/docker.folder/folders.json';

if (file_exists($path) == false) {
    $jsonData = json_encode (new stdClass);
    file_put_contents($path, $jsonData);
}
?>