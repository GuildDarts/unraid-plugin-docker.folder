<script>
    var indexPlusNr = 0;
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
</script>