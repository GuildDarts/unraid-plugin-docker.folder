<?php
/*
   $folder_file = json_decode(file_get_contents("/boot/config/plugins/docker.folder/results.json"),true);

   $response = json_decode("${_GET["settings"]}",true);
   $name = $response["name"];

   unset($response{'name'});

   $output = new stdClass();
   $fp->$name = $response;

   $fp = fopen('/boot/config/plugins/docker.folder/results.json', 'w');
   fwrite($fp, json_encode($output, JSON_PRETTY_PRINT));
   fclose($fp);
*/
?>


<?php
    $response = json_decode("${_POST["settings"]}",true);
    
    $name = $response["name"];
    unset($response{'name'});

    $inp = file_get_contents('/boot/config/plugins/docker.folder/folders.json');
    $tempObj = json_decode($inp);

    $tempObj->$name = $response;

    $jsonData = json_encode($tempObj, JSON_PRETTY_PRINT);
    file_put_contents('/boot/config/plugins/docker.folder/folders.json', $jsonData);
?>


<?php

    $path = "/boot/config/plugins/dockerMan/userprefs.cfg";

    $userprefs = parse_ini_file($path,true);
    array_unshift($userprefs, $name .= "-folder");
    write_php_ini($userprefs, $path);



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