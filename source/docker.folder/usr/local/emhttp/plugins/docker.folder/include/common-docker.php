<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once("$docroot/plugins/dynamix.docker.manager/include/DockerClient.php");

$docker_prefs_file      = $dockerManPaths['user-prefs'];

$DockerClient    = new DockerClient();
$DockerTemplates = new DockerTemplates();
$containers      = $DockerClient->getDockerContainers();
$allInfo         = $DockerTemplates->getAllInfo();

$dockers = [];
$dockerIds = new stdClass;
$dockerAutostart = new stdClass;

foreach ($containers as $ct) {
    $name = $ct['Name'];
    $id = $ct['Id'];
    $info = &$allInfo[$name];
    $is_autostart = $info['autostart'] ? 'true' : 'false';

    $dockerIds->$name = $id;
    $dockerAutostart->$name = $is_autostart;

    array_push($dockers, $name);
}

if (file_exists($docker_prefs_file)) {
    $docker_prefs = parse_ini_file($docker_prefs_file);
    foreach ($docker_prefs as $prefKey => &$pref) {
        if (!strpos($pref, '-folder') && !in_array($pref, $dockers)) {
            array_splice($docker_prefs, $prefKey, 1);
        }
    }
} else {	
    $docker_prefs = json_encode([]);	
}
?>

<script>
window.dockerOptions = {
    type: 'docker',
    listSelector: '#docker_list',
    hideSelector: '#docker_list > tr.sortable',
    hideSelectorName: 'td.ct-name > span.outer > span.inner > span.appname',
    dashboardHideSelectorName: 'span.inner > span:first-child',
    ids: <?= json_encode($dockerIds) ?>,
    autostart: <?= json_encode($dockerAutostart) ?>,
    prefs: <?= json_encode($docker_prefs) ?>,
    activeDropdowns: [],
    folderChildren: [],
    activeFolders: []
}
</script>

<script>
    function dockerDefaultCmd(folder, action) {
        const folderId = folder.id
        const containers = folder['properties']['children']
        const containersString = JSON.stringify(containers)

        folder.parent().find("span.inner > i").removeClass('fa-square fa-play').addClass('fa-refresh fa-spin')

        if (action !== "update") {
            $.post("/plugins/docker.folder/scripts/docker_default_cmd.php", {
                action: action,
                containers: containersString
            }, function() {
                loadlist();
            })
        } else {
            var list = '';
            for (const folder of containers) {
                for (const ct of docker) {
                    if (ct.name == folder && ct.update == 'false') {
                        list += '&ct[]=' + encodeURI(ct.name)
                    }
                }
            }
            if (list !== '') {
                var address = '/plugins/dynamix.docker.manager/include/CreateDocker.php?updateContainer=true' + list;
                popupWithIframe(`Updating all ${folderId} Containers`, address, true, 'loadlist');
            } else {
                swal({
                    title: 'Nothing to update',
                    text: `All containers in ${folderId} are up to date`,
                    type: 'info'
                })
                loadlist()
            }

        }

    }
</script>