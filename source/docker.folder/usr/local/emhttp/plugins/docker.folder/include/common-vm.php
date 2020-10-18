<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once("$docroot/plugins/dynamix.vm.manager/include/libvirt_helpers.php");

if (isset($lv)) {
    $vm_prefs_file = '/boot/config/plugins/dynamix.vm.manager/userprefs.cfg';
    $vms = $lv->get_domains();

    $vmIds = new stdClass;

    foreach($vms as $vm) {
        $res = $lv->get_domain_by_name($vm);
        $uuid = $lv->domain_get_uuid($res);

        $vmIds->$vm = $uuid;
    }

    if (file_exists($vm_prefs_file)) {
        $vm_prefs = parse_ini_file($vm_prefs_file);
        foreach ($vm_prefs as $prefKey => &$pref) {
            if (!strpos($pref, '-folder') && !in_array($pref, $vms)) {
                array_splice($vm_prefs, $prefKey, 1);
            }
        }
    } else {	
        $vm_prefs = json_encode([]);	
    }
}

?>

<script>
window.vmOptions = {
    type: 'vm',
    listSelector: '#kvm_list',
    hideSelector: '#kvm_list > tr.sortable',
    hideSelectorName: 'td.vm-name > span.outer > span.inner > a',
    dashboardHideSelectorName: 'span.inner',
    ids: <?= json_encode($vmIds) ?>,
    prefs: <?= json_encode($vm_prefs) ?>,
    activeDropdowns: [],
    folderChildren: [],
    activeFolders: []
}
</script>

<script>
    function vmDefaultCmd(folder, action) {
        const children = folder['properties']['children']
        const childrenLength = children.length

        folder.parent().find("span.inner > i").removeClass('fa-square fa-play fa-pause').addClass('fa-refresh fa-spin')

        post()

        function post(number=0) {
            const id = folder.options['ids'][children[number]]
            const params = {action: `domain-${action}`, uuid: id}

            $.post("/plugins/dynamix.vm.manager/include/VMajax.php", params, function(data) {
                if (data.error) {
                    swal({
                        title:"Execution error", html:true,
                        text:data.error, type:"error"
                    },function(){
                        check(number)
                    });
                } else {
                    check(number)
                }
            },'json');

            function check(number) {
                if (number > childrenLength-1) {
                    post(number++)
                } else {
                    loadlist()
                }
            }
        }
    }
</script>