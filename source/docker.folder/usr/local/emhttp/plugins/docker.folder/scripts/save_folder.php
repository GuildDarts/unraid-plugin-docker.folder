<?php
    $response = json_decode("${_POST["settings"]}");
    
    $name = $response->name;
    unset($response->name);

    $inp = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
    $tempObj = json_decode($inp);

    $tempObj->folders->$name = $response;

    // remove old key if renaming folder & rename folder in userprefs
    $rename = $_POST['rename'];
    if (isset($rename)) {
        unset($tempObj->$rename);

        $path = '/boot/config/plugins/dockerMan/userprefs.cfg';
        if (file_exists($path)) {
            $userprefs = parse_ini_file($path,true);
            $index = array_search($rename.'-folder', $userprefs);
            $userprefs[$index] = $name.'-folder';
            write_php_ini($userprefs, $path);
        }
    }

    $jsonData = json_encode($tempObj, JSON_PRETTY_PRINT);
    file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);

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
?>