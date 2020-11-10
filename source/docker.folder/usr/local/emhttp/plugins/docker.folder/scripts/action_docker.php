<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/plugins/dynamix.docker.manager/include/DockerClient.php";
libxml_use_internal_errors(false); # Enable xml errors

readfile("$docroot/plugins/dynamix.docker.manager/log.htm");
@flush();

execCommand($_GET['command'], true);
    
echo '<div style="text-align:center"><button type="button" onclick="window.parent.jQuery(\'#iframe-popup\').dialog(\'close\')">Done</button></div><br>';
?>