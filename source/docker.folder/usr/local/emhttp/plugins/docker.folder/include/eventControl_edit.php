<script>
    var remove_fix = `
    var folderNames = Object.keys(folders)

    if (params['container']) {
        for (const [dockerName, dockerId] of Object.entries(dockerIds)) {
            if (dockerId == params['container']) {
                for (const folderName of folderNames) {
                    for (const child of folders[folderName]['children']) {
                        if (dockerName == child) {
                            $.post("/plugins/docker.folder/scripts/remove_folder_child.php", {folderName: folderName, child: child}, function(){(async () => {folders = await read_folders()})();});
                        }
                    }
                }
            }
        }
    }
    `


    var ec = eventControl.toString()

    var args = ec.slice(ec.indexOf("(") + 1, ec.indexOf(")"))
    ec = ec.slice(ec.indexOf("{") + 1, ec.lastIndexOf("}"))

    var str = "console.log('action: '+params['action']+' container: '+params['container']);"
    var position = 1
    ec_final = ec.substring(0, position) + remove_fix + ec.substring(position);

    eventControl = new Function(args, ec_final + "\n")
</script>