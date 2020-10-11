<?php
    $type = $_POST['type'];

    if ($type !== 'vm') {
        $file = 'folders';
    } else {
        $file = 'folders-vm';
    }

    $response = json_decode("${_POST["settings"]}");

    $folderRaw = file_get_contents("/boot/config/plugins/docker.folder/$file.json");
    $folders = json_decode($folderRaw);

    $editFolderId = $_POST['editFolderId'];
    if ($editFolderId) {
        $id = $editFolderId;
    } else {
        $id = generateId($folders);
    }
    
    $folders->folders->$id = $response;

    $jsonData = json_encode($folders, JSON_PRETTY_PRINT);
    file_put_contents("/boot/config/plugins/docker.folder/$file.json", $jsonData);

    function write_php_ini($array, $file) {
        $res = array();
        foreach($array as $key => $val)
        {
            if(is_array($val))
            {
                $res[] = "[$key]";
                foreach($val as $skey => $sval) $res[] = "$skey=".(is_numeric($sval) ? $sval : '"'.$sval.'"');
            }
            else $res[] = "$key=".(is_numeric($val) ? $val : '"'.$val.'"');
        }
        file_put_contents($file, implode("\r\n", $res));
    }

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