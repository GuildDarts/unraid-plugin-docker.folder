<script>
    var userperfs_fix = `
    var indexPlusNr = 0;
    // fix for moving expanded docker thats in index 0 (should fix this by attaching children to parent docker)
    if ($(row).attr('class').includes('docker-folder-child-')) {
        row = $(row).parent()
    }
    row.parent().children().find('td.ct-name').each(function() {
        var folderNames = Object.keys(folders)
        var nam = $(this).find('.appname').text();
        var ind = $(this).parent().parent().children('tr').index($(this).parent());

        // skip if its folder child
        if ($(this).parent().attr('class').includes("docker-folder-child-")) {
            return
        }

        for (const folderName of folderNames) {
            if (nam == folderName) {
                names += nam + "-folder" + ';'
                index += parseInt(ind + indexPlusNr) + ';'
                var loopPlusNr = 0
                for (const [i, folder] of folders[folderName]['children'].entries()) {
                    names += folder + ";"
                    index += parseInt(ind + indexPlusNr + i + 1) + ';'
                    loopPlusNr++
                }
                indexPlusNr = indexPlusNr + loopPlusNr
                return
            }
        }

        names += nam + ';'
        index += parseInt(ind + indexPlusNr) + ';'
    });
    `

    var ls = loadlist.toString()

    var args = ls.slice(ls.indexOf("(") + 1, ls.indexOf(")"))
    ls = ls.slice(ls.indexOf("{") + 1, ls.lastIndexOf("}"))

    // apply_folder docker tab
    var docker_list_str = "$('#docker_list').html(data[0])"
    var docker_list_index = ls.indexOf(docker_list_str)
    if (docker_list_index !== -1) {
        ls = ls.replace(docker_list_str, `${docker_list_str};apply_folder(function(){$('#docker_list')`)
        ls_final = ls.replace("}});", "}});});")
    } else {
        // apply_folder dashboard 
        var str = "apply_folder()"
        var position = ls.lastIndexOf("}")
        ls_final = ls.substring(0, position) + str + ls.substring(position);
    }

    //console.log(args)
    //console.log(ls_final)


    //**FIXES userperfs.cfg**/
    var dataArray = ls_final.split('\n'); // convert file data in an array
    const searchKeyword = 'row.parent()';
    var lastIndex = -1; // lets say, we have not found the keyword

    for (var index = 0; index < dataArray.length; index++) {
        if (dataArray[index].includes(searchKeyword)) { // check if a line contains the 'searchKeyword' keyword
            lastIndex = index;
            break;
        }
    }

    if (lastIndex !== -1) {
        dataArray.splice(lastIndex, 1, userperfs_fix);
    }
    


    ls_final = dataArray.join('\n')

    loadlist = new Function(args, ls_final + "\n")



    function loadlistUpdate(e,ui) {
        var row = $('#docker_list').find('tr:first');
        var names = ''; var index = '';
        eval(userperfs_fix)
        $.post('/plugins/dynamix.docker.manager/include/UserPrefs.php',{names:names,index:index});
    }
</script>