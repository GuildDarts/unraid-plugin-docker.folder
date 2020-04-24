<?php

// stuff for checking if container is stopped (then dont restart)
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/plugins/dynamix.docker.manager/include/DockerClient.php";

// Read container info
$DockerClient    = new DockerClient();
$DockerTemplates = new DockerTemplates();
$containers      = $DockerClient->getDockerContainers();
$allInfo = $DockerTemplates->getAllInfo();


$action = $_GET["action"];
$iContainers = json_decode("${_GET["containers"]}",true);

foreach ($iContainers as $iContainer) {
    if ($action == "restart") {
        foreach ($containers as $ct) {
            $name = $ct['Name'];
            $info = &$allInfo[$name];
            $running = $info['running'] ? 1 : 0;
            if ($iContainer == $name && $running == 1) {
                exec("docker $action $iContainer");
            }
        }
    } else {
        exec("docker $action $iContainer");
    }
}
