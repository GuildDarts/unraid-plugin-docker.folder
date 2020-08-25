<script>
function userprefs_fix(type) {
    let name = (type === 'vm') ? 'vm' : 'ct'

    return `
    var indexPlusNr = 0;
    // fix for moving expanded docker thats in index 0 (should fix this by attaching children to parent docker)
    if ($(row).attr('class').includes('${type}-folder-child-')) {
        row = $(row).parent()
    }
    row.parent().children().find('td.${name}-name').each(function() {
        var folderNames = Object.keys(folders['${type}'])
        var nam = $(this).find('.inner > :first-child').text();
        var ind = $(this).parent().parent().children('tr').index($(this).parent());

        // skip if its folder child
        if ($(this).parent().attr('class').includes("${type}-folder-child-")) {
            return
        }

        for (const folderName of folderNames) {
            if (nam == folderName) {
                names += nam + "-folder" + ';'
                index += parseInt(ind + indexPlusNr) + ';'
                var loopPlusNr = 0
                $('.${type}-folder-child-' + folderName).each(function(i) {
                    let childName = $(this).find('.inner > :first-child').text()
                    names += childName + ";"
                    index += parseInt(ind + indexPlusNr + i + 1) + ';'
                    loopPlusNr++
                })
                indexPlusNr = indexPlusNr + loopPlusNr
                return
            }
        }

        names += nam + ';'
        index += parseInt(ind + indexPlusNr) + ';'
    });
    `;
}

function userprefs_fix_apply(_type) {
    let type = (_type === 'vm') ? 'kvm' : _type

    let ls = loadlist.toString()

    let args = ls.slice(ls.indexOf("(") + 1, ls.indexOf(")"))
    ls = ls.slice(ls.indexOf("{") + 1, ls.lastIndexOf("}"))

    // apply_folder docker tab
    let docker_list_str = `$('#${type}_list').html(data[0])`
    let docker_list_index = ls.indexOf(docker_list_str)
    if (docker_list_index !== -1) {
        ls = ls.replace(docker_list_str, `${docker_list_str};apply_folder(function(){$('#${type}_list')`)
        ls = ls.replace("}});", "}});});")
    } else {
        // apply_folder dashboard 
        let str = "apply_folder()"
        let position = ls.lastIndexOf("}")
        ls = ls.substring(0, position) + str + ls.substring(position);
    }



    //**FIXES userperfs.cfg**/
    let ls_array = ls.split('\n')

    ls_array = searchArrayAndReplace('row.parent()', ls_array, userprefs_fix(_type))

    ls = ls_array.join('\n')

    loadlist = new Function(args, ls + "\n")
}

function loadlistUpdate(_type) {
    let type = (_type === 'vm') ? 'kvm' : _type

    var row = $(`#${type}_list`).find('tr:first');
    var names = ''; var index = '';
    eval(userprefs_fix(_type))
    $.post('/plugins/dynamix.docker.manager/include/UserPrefs.php',{names:names,index:index});
}
</script>