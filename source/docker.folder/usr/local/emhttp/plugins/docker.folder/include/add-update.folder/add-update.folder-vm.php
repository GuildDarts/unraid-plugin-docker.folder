<?php
require_once("$docroot/plugins/docker.folder/include/add-update.folder/popup-vm.php");
require_once("$docroot/plugins/docker.folder/include/common-vm.php");

$folderChildren = "<div class='containers'>";

foreach ($vms as $vm) {
  $res = $lv->get_domain_by_name($vm);
  $desc = $lv->domain_get_description($res);

  $img = $lv->domain_get_icon_url($res);
  if ($img == null) {
    $img = "/plugins/dynamix.docker.manager/images/question.png";
  }

  $folderChildren .= "<div class='container_item'>";
  $folderChildren .= "<div class='info'><span><img class='docker_img' src='" . $img . "'></span>";

  $folderChildren .= "<span><strong>$vm</strong><br>Desc: $desc";

  $folderChildren .= "</span></div>";

  $folderChildren .= "<div class='settingC-box'><input class='settingC' type='checkbox' name='$vm'></div>";

  $folderChildren .= "</div>";
}
$folderChildren .= "</div>";
?>