<?php
    exec("docker create --name='${_GET["name"]}-folder' --net='none' 'tianon/true:latest' ");
    exec("docker ps -a --filter 'name=${_GET["name"]}-folder' --format '{{.ID}}' ", $output);
    echo implode("",$output);
?>