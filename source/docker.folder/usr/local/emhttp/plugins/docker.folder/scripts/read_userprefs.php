<?php
        $userprefs = parse_ini_file("/boot/config/plugins/dockerMan/userprefs.cfg",true);
        echo json_encode($userprefs);
?>