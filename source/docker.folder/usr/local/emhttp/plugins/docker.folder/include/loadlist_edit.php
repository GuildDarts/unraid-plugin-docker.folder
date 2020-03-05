<script>
    let userperfs_fix = `
    var indexPlusNr = 0;
    row.parent().children().find('td.ct-name').each(function() {
        var folderNames = Object.keys(folders)
        var nam = $(this).find('.appname').text();
        var ind = $(this).parent().parent().children().index($(this).parent());

        for (const folderName of folderNames) {
            if (nam == folderName) {
                names += nam + "-folder" + ';'
                index += parseInt(ind+indexPlusNr) + ';'
                var loopPlusNr = 0
                for (const [i, folder] of folders[folderName]['children'].entries()) {
                    names += folder+";"
                    index += parseInt(ind+indexPlusNr+i+1) + ';'
                    loopPlusNr++
                }
                indexPlusNr = indexPlusNr+loopPlusNr
                return
            }
        }
        
        
        names += nam + ';'
        index += parseInt(ind+indexPlusNr) + ';'
    });
    console.log(names)
    console.log(index)
    `

    let ls = loadlist.toString()

    let args = ls.slice(ls.indexOf("(") + 1, ls.indexOf(")"))
    ls = ls.slice(ls.indexOf("{") + 1, ls.lastIndexOf("}"))

    let str = "apply_folder()"
    let position = ls.lastIndexOf("}")
    ls_final = [ls.slice(0, position), str, ls.slice(position)].join("")



    //**FIXES userperfs.cfg**/
    let dataArray = ls_final.split('\n'); // convert file data in an array
    const searchKeyword = 'row.parent()';
    let lastIndex = -1; // let say, we have not found the keyword

    for (let index = 0; index < dataArray.length; index++) {
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
</script>