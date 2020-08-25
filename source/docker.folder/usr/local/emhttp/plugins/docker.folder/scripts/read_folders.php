<?php
        $file = $_GET['file'];
        $folder_file = file_get_contents("/boot/config/plugins/docker.folder/$file.json");
        echo $folder_file;
?>