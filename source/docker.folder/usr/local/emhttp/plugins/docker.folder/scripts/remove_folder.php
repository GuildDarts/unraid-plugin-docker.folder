<?php

$response = json_decode("${_GET["folders"]}",true);

if ($response == null) {
    $response = new stdClass;
}

$jsonData = json_encode($response, JSON_PRETTY_PRINT);
file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);

?>