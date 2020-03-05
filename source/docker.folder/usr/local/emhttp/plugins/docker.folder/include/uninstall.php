<?php

$folder_file = json_decode(file_get_contents("/boot/config/plugins/docker.folder/folders.json"),true);

$folderKeys = array_keys($folder_file);

foreach ($folderKeys as $key) {
  exec("docker rm $key-folder");
}

exec("docker images -a | grep 'tianon/true' | awk '{print $3}' | xargs docker rmi");

?>