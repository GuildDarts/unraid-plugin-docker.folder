<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/plugins/dynamix.docker.manager/include/Helpers.php";
require_once "$docroot/webGui/include/Helpers.php";
readfile("$docroot/plugins/dynamix.docker.manager/log.htm");

execCommand($_GET["command"]);
    
echo '<div style="text-align:center"><button type="button" onclick="window.parent.jQuery(\'#iframe-popup\').dialog(\'close\')">Done</button></div><br>';
?>