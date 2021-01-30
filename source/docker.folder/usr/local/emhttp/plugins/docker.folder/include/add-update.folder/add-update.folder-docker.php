<?php
require_once("$docroot/plugins/docker.folder/include/add-update.folder/popup-docker.php");
require_once("$docroot/plugins/docker.folder/include/common-docker.php");

$containers = $DockerClient->getDockerContainers();
$info = $DockerTemplates->getAllInfo();

$folderChildren = "<div class='containers'>";

foreach ($containers as $ct) {
  $name = $ct['Name'];

  $img = $info[$name]['icon'];
  if ($img == null) {
    $img = "/plugins/dynamix.docker.manager/images/question.png";
  }

  $repository = ($index === false) ? "Unknown" : $ct['Image'];
  $id = ($index === false) ? "Unknown" : $ct['Id'];
  $folderChildren .= "<div class='container_item'>";
  $folderChildren .= "<div class='info'><span><img class='docker_img' src='" . $img . "'></span>";

  $folderChildren .= "<span><strong>$name</strong><br>$repository";

  $folderChildren .= "</span></div>";

  $folderChildren .= "<div class='settingC-box'><input class='settingC' type='checkbox' name='$name'></div>";

  $folderChildren .= "</div>";
}
$folderChildren .= "</div>";
?>