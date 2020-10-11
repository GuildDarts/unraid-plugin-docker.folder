<script>
(function() {
    let remove_fix = `
    var folderIds = Object.keys(folders['docker'])

    if (params['container'] && (params['action'] === 'remove_container' || params['action'] === 'remove_all') ) {
        for (const [dockerName, dockerId] of Object.entries(dockerOptions["ids"])) {
            if (dockerId === params['container']) {
                for (const folderId of folderIds) {
                    for (const child of folders['docker'][folderId]['properties']['children']) {
                        if (dockerName === child) {
                            $.post("/plugins/docker.folder/scripts/save_folder.php", {type: 'docker', childrenRemove: JSON.stringify([{folderId: folderId, child: child}])}, function(){(async () => {dockerFolders = await read_folders('folders')})();});
                        }
                    }
                }
            }
        }
    }
    `

    let animating_fix = `
    if (spin) {
        $('#'+params['container']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
        $('.docker-preview-id-'+params['container']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
        $('#advanced-context-'+params['container']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
    }
    `


    let ec = eventControl.toString()
    let args = ec.slice(ec.indexOf("(") + 1, ec.indexOf(")"))
    ec = ec.slice(ec.indexOf("{") + 1, ec.lastIndexOf("}"))

    let position = 1
    ec_final = ec.substring(0, position) + remove_fix + ec.substring(position);



    // fix status icon not animating in preview/advanced context
    let ec_array = ec_final.split('\n')

    ec_array = searchArrayAndReplace("$('#'+params['container'])", ec_array, animating_fix)

    ec_final = ec_array.join('\n')

    eventControl = new Function(args, ec_final + "\n")
})()
</script>