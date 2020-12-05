<?php

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

function migration_4_0($folders) {
    foreach ($folders->folders as $folderKey => &$folder) {
        foreach ($folder->buttons as $buttonKey => &$button) {
            if($button->type === 'Docker_Default') {
                $button->type = 'Action';
            }

            if($button->type === 'Docker_Sub_Menu') {
                $button->type = 'Sub_Menu';
            }
        }
    }

    return $folders;
}

function migration_4_1($folders) {
    $ids = [];
    $tmpFolders = new stdClass;
    $user_prefs_file = '/boot/config/plugins/dockerMan/userprefs.cfg';
    $user_prefs = parse_ini_file($user_prefs_file);
    foreach ($folders->folders as $folderKey => &$folder) {
        $idUnique = false;
        while ($idUnique === false) {
            $id = substr(md5(rand()), 0, 7);
            if (in_array($id, $ids)) {
                continue;
            }
            array_push($ids, $id);
            $idUnique = true;
        }
        $folder->name = $folderKey;
        $tmpFolders->$id = $folder;
        unset($folders->$folderKey);

        // edit userprefs to use folder ids
        $user_prefs = array_map(function ($pref) use ($folderKey, $id) {
            return $pref === "$folderKey-folder" ? "$id-folder" : $pref;
        }, $user_prefs);
    }

    write_php_ini($user_prefs, $user_prefs_file);
    $folders->folders = $tmpFolders;

    return $folders;
}

function migration_4_2($folders) {
    foreach ($folders->folders as $folderKey => &$folder) {
        // add docker_preview_no_icon_row_count
        $folder->docker_preview_no_icon_row_count = 6;

        // add docker_preview_no_icon_column_count
        $folder->docker_preview_no_icon_column_count = 2;
    }

    return $folders;
}

?>