<?php
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