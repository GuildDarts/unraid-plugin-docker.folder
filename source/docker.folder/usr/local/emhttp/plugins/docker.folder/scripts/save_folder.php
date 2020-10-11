<?php
    $type = $_POST['type'];

    if ($type !== 'vm') {
        $file = 'folders';
    } else {
        $file = 'folders-vm';
    }

    $folder = json_decode("${_POST["settings"]}");
    $childrenRemove = json_decode("${_POST["childrenRemove"]}");

    $folderRaw = file_get_contents("/boot/config/plugins/docker.folder/$file.json");
    $folders = json_decode($folderRaw);

    $editFolderId = $_POST['editFolderId'];
    if ($editFolderId) {
        $id = $editFolderId;
    } else if (isset($folder)) {
        $id = generateId($folders);
    }

    // remove children from folder
    if (isset($childrenRemove)) {
        foreach ($childrenRemove as $child) {
            $needle = $child->child;
            $folderId = $child->folderId;
            if (($key = array_search($child->child, $folders->folders->$folderId->children)) !== false) {
                unset($folders->folders->$folderId->children[$key]);
                $folders->folders->$folderId->children = array_values($folders->folders->$folderId->children);
            }
        }
    }

    if (isset($folder)) {
        $folders->folders->$id = $folder;
    }

    $jsonData = json_encode($folders, JSON_PRETTY_PRINT);
    file_put_contents("/boot/config/plugins/docker.folder/$file.json", $jsonData);



    function generateId($folders) {
        $ids = [];
        foreach ($folders->folders as $folderKey => &$folder) {
            array_push($ids, $folderKey);
        }

        $idUnique = false;
        while ($idUnique === false) {
            $id = substr(md5(rand()), 0, 7);
            if (in_array($id, $ids)) {
                continue;
            }
            array_push($ids, $id);
            $idUnique = true;
        }
        return $id;
    }
?>