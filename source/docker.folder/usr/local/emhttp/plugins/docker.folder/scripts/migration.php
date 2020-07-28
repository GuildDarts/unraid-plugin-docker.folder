<?php
    require_once("/usr/local/emhttp/plugins/docker.folder/include/folderVersion.php");

    function logger($string) {
        echo $string, PHP_EOL;
        shell_exec("logger 'Docker Folder: $string'");
    }

    $path = '/boot/config/plugins/docker.folder/';
    $foldersFile = $path.'folders.json';
    $folders_file = file_get_contents($path.'folders.json');

    if ( file_exists($foldersFile ) ) {
        if (isset($_POST['importFolder'])) {
            init($path, $folders_file, $_POST['importFolder'], true);
        } else {
            init($path, $folders_file, $folders_file, false);
        }
    }

    function init($path, $folders_file, $import, $isImport) {
        
        $folders = json_decode($import);

        // exit if there are no folders
            if (count((array)$folders) == null || count((array)$folders) < 2 || (count((array)$folders->folders) == 0 && $folders->folders != null) ) {
            logger('No folders to migrate');
            finish($path, $folders, $folders_file, $isImport);
            exit();
        }

        file_put_contents($path.'folders.backup.json', $folders_file);

        $functionsArray = get_defined_functions();
        $migrationFunctions = array_filter($functionsArray['user'], function($func) {
            $string = 'migration_';
            $length = strlen($string);
            if (substr($func, 0, $length) === $string) {
                return true;
            }
        });
        
        if ($folders->foldersVersion == null) {
            logger('migration_1');
            $folders = migration_1($folders);
        }

        foreach ($migrationFunctions as $function) {
            $func = str_replace('migration_', '', $function);
            $func = str_replace('_', '.', $func);

            $version = floatval($func);

            if ($folders->foldersVersion < $version)  {
                logger($version);
                $folders = $function($folders);
            }
        }

        finish($path, $folders, $folders_file, $isImport);
        
    }

    function finish($path, $folders, $folders_file, $isImport) {
        $folders->foldersVersion = $GLOBALS['foldersVersion'];

        if ($isImport) {
            unset($folders->foldersVersion);
            $currentFolders = json_decode($folders_file);
            $output = (object) array_merge((array) $currentFolders, (array) $folders);
        } else {
            $output = $folders;
        }

        $jsonData = json_encode($output, JSON_PRETTY_PRINT);
        file_put_contents($path.'folders.json', $jsonData);
    }

    function migration_1($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion');
            foreach ($folder->buttons as $buttonKey => &$button) {

                // if its got type just skip
                if($button->type !== null) {
                    continue;
                }

                $isBash = true;

                // WebUI
                if ($button->name == 'WebUI') {
                    $isBash = false;

                    $button->type = 'WebUI';
                }

                // Docker_Default
                if ($button->cmd == 'Docker_Default') {
                    $isBash = false;

                    $button->type = 'Docker_Default';
                    $button->cmd = strtolower($button->name);
                } 
                
                // bash
                if ($isBash == true) {
                    $button->type = 'Bash';
                }
            }
        }

        return $folders;
    }

    function migration_2_1($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};
            foreach ($folder->buttons as $buttonKey => &$button) {
                // Docker_Sub_Menu set cmd val = name val
                if ($button->type == 'Docker_Sub_Menu') {
                    $button->cmd = $button->name;
                }
            }
        }

        return $folders;
    }

    function migration_2_2($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};
            // remove hidden docker
            exec("docker rm $folderKey-folder");
            
            // remove 'id' key
            unset($folder->id);
        }

        // remove tianon/true docker image (goodbye old friend ♥)
        exec("docker images -a | grep 'tianon/true' | awk '{print $3}' | xargs docker rmi");

        return $folders;
    }

    function migration_2_3($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};
            // add docker_expanded_style
            $folder->docker_expanded_style = 'bottom';

            // rename start_expanded
            $folder->docker_start_expanded = $folder->start_expanded;
            unset($folder->start_expanded);

            // add docker_preview
            $folder->docker_preview = 'none';
        }

        return $folders;
    }

    function migration_2_4($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};
            // add docker_icon_style
            $folder->docker_icon_style = 'docker';

            // add icon_animate_hover
            $folder->icon_animate_hover = false;

            // add docker_preview_hover_only
            $folder->docker_preview_hover_only = false;

            // add docker_preview_icon_grayscale
            $folder->docker_preview_icon_grayscale = true;
        }

        return $folders;
    }

    function migration_2_5($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};

            // add docker_preview_hover_only
            $folder->docker_preview_icon_show_log = false;
        }

        return $folders;
    }

    function migration_2_6($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};

            // add docker_preview_advanced_context_menu
            $folder->docker_preview_advanced_context_menu = false;

            // add docker_preview_advanced_context_menu_activation_mode
            $folder->docker_preview_advanced_context_menu_activation_mode = 'click';

            // add docker_preview_advanced_context_menu_graph_mode
            $folder->docker_preview_advanced_context_menu_graph_mode = 'none';
        }

        return $folders;
    }

    function migration_2_7($folders) {
        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion') {continue;};

            // add docker_preview_text_update_color
            $folder->docker_preview_text_update_color = false;
        }

        return $folders;
    }

    function migration_3_0($folders) {
        $folders->folders = new stdClass;

        foreach ($folders as $folderKey => &$folder) {
            if($folderKey == 'foldersVersion' || $folderKey == 'folders') {continue;}

            // move folders to own object
            $folders->folders->$folderKey = $folder;
            unset($folders->$folderKey);
        }

        foreach ($folders->folders as $folderKey => &$folder) {
            // add docker_preview_icon_show_webui
            $folder->docker_preview_icon_show_webui = false;
        }

        $folders->settings = new stdClass;

        // add fix_docker_page_shifting
        $folders->settings->fix_docker_page_shifting = false;

        return $folders;
    }