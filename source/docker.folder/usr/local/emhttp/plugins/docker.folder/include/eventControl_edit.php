<script>
$(function() {
    var remove_fix = `
    var folderNames = Object.keys(folders)

    if (params['container'] && (params['action'] == 'remove_container' || params['action'] == 'remove_all') ) {
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

    let animating_fix = `
    if (spin) {
        $('#'+params['container']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
        $('.docker-preview-id-'+params['container']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
        $('#advanced-context-'+params['container']).parent().find('i').removeClass('fa-play fa-square fa-pause').addClass('fa-refresh fa-spin');
    }
    `


    var ec = eventControl.toString()

    var args = ec.slice(ec.indexOf("(") + 1, ec.indexOf(")"))
    ec = ec.slice(ec.indexOf("{") + 1, ec.lastIndexOf("}"))

    var str = "console.log('action: '+params['action']+' container: '+params['container']);"
    var position = 1
    ec_final = ec.substring(0, position) + remove_fix + ec.substring(position);



    // fix status icon not animating in preview/advanced context
    let dataArray = ec_final.split('\n'); // convert file data in an array
    const searchKeyword = "$('#'+params['container'])";
    let lastIndex = -1; // lets say, we have not found the keyword

    for (let index = 0; index < dataArray.length; index++) {
        if (dataArray[index].includes(searchKeyword)) { // check if a line contains the 'searchKeyword' keyword
            lastIndex = index;
            break;
        }
    }

    if (lastIndex !== -1) {
        dataArray.splice(lastIndex, 1, animating_fix);
    }

    ec_final = dataArray.join('\n')

    eventControl = new Function(args, ec_final + "\n")
})
</script>