<?php

$action = $_GET["action"];
$containers = json_decode("${_GET["containers"]}",true);

foreach ($containers as $container) {
    exec("docker $action $container");
}
?>