<?php
    require_once("/usr/local/emhttp/plugins/docker.folder/include/folderVersion.php");
    init();
    function init() {
        $path = '/boot/config/plugins/docker.folder/';
        $foldersFile = $path.'folders.json';
        if ( file_exists($foldersFile ) ) {
            $folders_file = file_get_contents($path.'folders.json');
            $folders = json_decode($folders_file, true);

            // exit if there are no folders
            if (count($folders) == null || count($folders) < 2) {
                finish($path, $folders);
                exit();
            }

            file_put_contents($path.'folders.backup.json', $folders_file);

            if ($folders['foldersVersion'] == null) {
                $folders = migration_1($folders);
            }
            if ($folders['foldersVersion'] < 2.1) {
                $folders = migration_2($folders);
            }
            if ($folders['foldersVersion'] < 2.2) {
                $folders = migration_3($folders);
            }

            finish($path, $folders);
        }
    }

    function finish($path, $folders) {
        $folders['foldersVersion'] = $GLOBALS['foldersVersion'];

        $jsonData = json_encode($folders, JSON_PRETTY_PRINT);
        file_put_contents($path.'folders.json', $jsonData);
    }

    function migration_1($folders) {
        echo("migration_1");
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion');
            foreach ($folder['buttons'] as $buttonKey => &$button) {

                // if its got type just skip
                if($button['type'] !== null) {
                    continue;
                }

                $isBash = true;

                // WebUI
                if ($button['name'] == 'WebUI') {
                    $isBash = false;

                    $button['type'] = 'WebUI';
                }

                // Docker_Default
                if ($button['cmd'] == 'Docker_Default') {
                    $isBash = false;

                    $button['type'] = 'Docker_Default';
                    $button['cmd'] = strtolower($button['name']);
                } 
                
                // bash
                if ($isBash == true) {
                    $button['type'] = 'Bash';
                }
            }
        }

        return $folders;
    }

    function migration_2($folders) {
        echo("migration_2");
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};
            foreach ($folder['buttons'] as $buttonKey => &$button) {
                // Docker_Sub_Menu set cmd val = name val
                if ($button['type'] == 'Docker_Sub_Menu') {
                    $button['cmd'] = $button['name'];
                }
            }
        }

        return $folders;
    }

    function migration_3($folders) {
        echo("migration_3");
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};
            // remove hidden docker
            exec("docker rm $folderKey-folder");
            
            // remove 'id' key
            unset($folder['id']);
        }

        // remove tianon/true docker image (goodbye old friend ♥)
        exec("docker images -a | grep 'tianon/true' | awk '{print $3}' | xargs docker rmi");

        return $folders;
    }