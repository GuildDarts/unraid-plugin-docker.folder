<?php
    require_once('/usr/local/emhttp/plugins/docker.folder/include/folderVersion.php');
    require_once('/usr/local/emhttp/plugins/docker.folder/scripts/migration/docker_migration.php');
    require_once('/usr/local/emhttp/plugins/docker.folder/scripts/migration/vm_migration.php');

    $path = '/boot/config/plugins/docker.folder';
    $file = $_POST['file'];

    if (isset($file)) {
        init($path, $file);
    } else {
        init($path, 'folders');
        init($path, 'folders-vm');
    }
    

    function init($path, $file) {
        $folderType = ($file !== 'folders-vm') ? 'docker' : 'vm';
        $foldersFile = "$path/$file.json";
        $folders_file = file_get_contents($foldersFile);

        if ( file_exists($foldersFile ) ) {
            if (isset($_POST['importFolder'])) {
                $import = $_POST['importFolder'];
                $isImport = true;
            } else {
                $import = $folders_file;
                $isImport = false;
            }
        } else {
            exit();
        }

        $folders = json_decode($import);

        // exit if there are no folders
        if (count((array)$folders) == null || count((array)$folders) < 2 || (count((array)$folders->folders) == 0 && $folders->folders != null) ) {
            logger("No folders to migrate ($file)");
            finish($foldersFile, $folders, $folders_file, $isImport);
            exit();
        }

        file_put_contents($path."$file.backup.json", $folders_file);

        // for when there was no folderVersion
        if ($folders->foldersVersion == null && $file === 'folders') {
            logger('migration_1');
            $folders = migration_1($folders);
        }

        $functionsArray = get_defined_functions();
        $migrationFunctions = array_filter($functionsArray['user'], function($func) use($folderType) {
            if (strpos($func, "$folderType\\migration_") !== false) {
                return true;
            }
        });

        foreach ($migrationFunctions as $function) {
            $func = str_replace("migrations\\$folderType\\migration_", '', $function);
            $func = str_replace('_', '.', $func);

            $version = floatval($func);

            if ($folders->foldersVersion < $version)  {
                logger("$file ($version)");
                $folders = $function($folders);
            }
        }

        finish($foldersFile, $folders, $folders_file, $isImport);
        
    }

    function finish($foldersFile, $folders, $folders_file, $isImport) {
        $folders->foldersVersion = $GLOBALS['foldersVersion'];

        if ($isImport) {
            unset($folders->foldersVersion);
            $currentFolders = json_decode($folders_file);
            $output = (object) array_merge((array) $currentFolders, (array) $folders);
        } else {
            $output = $folders;
        }

        $jsonData = json_encode($output, JSON_PRETTY_PRINT);
        file_put_contents($foldersFile, $jsonData);
    }

    function logger($string) {
        echo $string, PHP_EOL;
        shell_exec("logger 'Docker Folder: $string'");
    }

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