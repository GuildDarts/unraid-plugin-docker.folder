<script>
(function() {
    let remove_fix = `
    var folderNames = Object.keys(folders['vm'])

    if (params['uuid'] && (params['action'] === 'domain-undefine' || params['action'] === 'domain-delete') ) {
        for (const [vmName, vmId] of Object.entries(vmOptions["ids"])) {
            if (vmId === params['uuid']) {
                for (const folderName of folderNames) {
                    for (const child of folders['vm'][folderName]['properties']['children']) {
                        if (vmName === child) {
                            $.post("/plugins/docker.folder/scripts/remove_folder_child.php", {type: 'vm', folderName: folderName, child: child}, function(){(async () => {folders['vm'] = await read_folders('folders-vm')})();});
                        }
                    }
                }
            }
        }
    }
    `

    let animating_fix = `
    if (spin) {
        $('#vm-'+params['uuid']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
        $('.docker-preview-id-vm-'+params['uuid']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
    }
    `


    let ec = ajaxVMDispatch.toString()
    let args = ec.slice(ec.indexOf("(") + 1, ec.indexOf(")"))
    ec = ec.slice(ec.indexOf("{") + 1, ec.lastIndexOf("}"))

    let position = 1
    ec_final = ec.substring(0, position) + remove_fix + ec.substring(position);



    // fix status icon not animating in preview/advanced context
    let ec_array = ec_final.split('\n')

    ec_array = searchArrayAndReplace("$('#vm-'+params['uuid'])", ec_array, animating_fix)

    ec_final = ec_array.join('\n')

    ajaxVMDispatch = new Function(args, ec_final + "\n")
})()
</script>