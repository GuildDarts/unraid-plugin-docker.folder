<style type="text/css">
    .docker-folder-hide {
        display: none;
    }
</style>


<script>
    function checkStatus(item, folderName) {
        var selector = $(item).find("span.inner > i")
        var max = folders[folderName]["children"].length
        var cur = 0

        $(`.docker-folder-child-${folderName}`).each(function() {
            if ($(this).find("i.fa").hasClass("started")) {
                cur++
            }
        });

        selector.removeClass()
        if (cur == max) {
            selector.addClass("fa started fa-play green-text")
            selector.parent().parent().switchClass("stopped", "started")
        } else if (cur == 0) {
            selector.addClass("fa stopped fa-square red-text")
        } else {
            selector.addClass("fa started fa-square orange-text")
            selector.parent().parent().switchClass("stopped", "started")
        }

        selector.find("span.state").remove()
        selector.parent().find("span.state").before(`<span class="state">${cur}/${max}</span>`)
    }

    function loadDropdownButtons(folderId, folderName) {
        let dropdown = $(`#dropdown-${folderId}`)
        dropdown.empty()

        for (const button of folders[folderName]['buttons']) {

            if (button['cmd'] == "") {
                continue
            }

            dropdownButton(dropdown, folderName, button['name'], button['icon'], button['cmd'])

            // add divider after WebUi and Restart
            if (button['name'] == "WebUI" && button['cmd'] !== "" || button['name'] == "Restart") {
                dropdownButton(dropdown, folderName, "divider")
            }
        }

        dropdownButton(dropdown, folderName, "divider")
        dropdownButton(dropdown, folderName, "Edit Folder", "wrench")
        dropdownButton(dropdown, folderName, "Remove Folder", "trash")
    }


    function dropdownButton(dropdown, folderName, name, icon, cmd) {

        if (name == "divider") {
            dropdown.append("<li class='divider'></li>")
            return
        }

        dropdown.append(`<li> <a name='${name}' href='#'> <i class='fa fa-fw fa-${icon} fa-lg'></i> &nbsp;&nbsp;${name}</a> </li>`)


        switch (name) {
            case "Edit Folder":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    editFolder(folderName)
                })
                return
            break;

            case "Remove Folder":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    swal({
                        title: "Are you sure?",
                        text: "Remove folder: " + folderName,
                        type: "warning",
                        showCancelButton: true
                    }, function() {
                        folderRemove(folderName)
                    });
                })
                return
            break;
        }

        if (cmd == "Docker_Default") {
            $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                dockerDefaultCmd(folderName, name)
            })
            return
        }

        if (RegExp(/^https?:\/\//g).test(cmd)) {
            $(dropdown).find(`li > a[name ='${name}']`).attr('href', cmd)
        } else {
            $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                let title = `${name}: ${folderName}`;
                let address = `/plugins/docker.folder/scripts/action_docker.php?command=${cmd}`;
                popupWithIframe(title, address, true, 'loadlist');
            })
        }
    }

    function edit_folder_base(folderName, folderId) {
        let foldersChildrenLength = folders[folderName]['children'].length

        // set icon
        if (folders[folderName]["icon"] !== "") {
            $(`#${folderId}`).find("img").attr("src", folders[folderName]["icon"])
        }

        // changes the stopped/started text to folder
        $(`#${folderId}`).parent().find("span.inner > span.state").text("folder")

        // remove -folder from the name
        $(`#${folderId}`).parent().find("span.inner > span").first().text(folderName)

        loadDropdownButtons(folderId, folderName)
        checkStatus($(`#${folderId}`).parent(), folderName)

    }

    function dockerDefaultCmd(folderName, name) {
        let action = name.toLowerCase()
        let containers = folders[folderName]['children']
        let containersString = JSON.stringify(containers)

        $(`.docker-folder-parent-${folderName}`).find("span.inner > i").removeClass('fa-square fa-play').addClass('fa-refresh fa-spin')

        if (action !== "update") {
            $.get("/plugins/docker.folder/scripts/docker_default_cmd.php", {
                action: action,
                containers: containersString
            }, function() {
                loadlist();
            })
        } else {
            var list = '';
            for (const folder of folders[folderName]['children']) {
                for (const ct of docker) {
                    if (ct.name == folder && ct.update == 'false') {
                        list += '&ct[]=' + encodeURI(ct.name)
                    }
                }
            }
            if (list !== '') {
                var address = '/plugins/dynamix.docker.manager/include/CreateDocker.php?updateContainer=true' + list;
                popupWithIframe(`Updating all ${folderName} Containers`, address, true, 'loadlist');
            } else {
                swal({
                    title: 'Nothing to update',
                    text: `All containers in ${folderName} are up to date`,
                    type: 'info'
                })
                loadlist()
            }

        }

    }



    function editFolder(folderName) {
        var path = location.pathname;
        var x = path.indexOf('?');
        if (x != -1) path = path.substring(0, x);
        location = path + '/UpdateFolder?folderName=' + folderName;
    }

    async function read_folders() {
        postResult = await Promise.resolve($.ajax({
            url: "/plugins/docker.folder/scripts/read_folders.php",
            type: "get",
            async: true
        }));
        try {
            var folders = await JSON.parse(postResult)
        }
        catch(err) {
            if (err instanceof SyntaxError) {
                var result = await Promise.resolve($.get("/plugins/docker.folder/include/post-install.php"))
                if (result !== 'err') {
                    return read_folders()
                } else {
                    throw err
                }
            } else {
                throw err
            }
        }
        return await folders
    }

    async function read_userprefs() {
        postResult = await Promise.resolve($.ajax({
            url: "/plugins/docker.folder/scripts/read_userprefs.php",
            type: "get",
            async: true
        }));
        return await JSON.parse(postResult)
    }

    function folderRemove(folderName) {
        delete folders[folderName]
        let foldersSting = JSON.stringify(folders)
        $.get("/plugins/docker.folder/scripts/remove_folder.php", {
            folders: foldersSting
        });

        $.get("/plugins/docker.folder/scripts/docker_folder_remove.php", {
            name: folderName
        }, function() {
            loadlist();
        });
    }

    function docker_hide(folderName, location) {

        $(`#docker_list_storage > .docker-folder-child-${folderName}`).remove()

        if (location == "dashboard") {
            var selector = "#db-box3 > tbody > tr > td:nth-child(2) > span"
            var selectorName = "span.inner > span:first-child"
        } else {
            var selector = "#docker_list > tr.sortable"
            var selectorName = "td.ct-name > span.outer > span.inner > span.appname"
        }

        $(selector).each(function() {
            let name = $(this).find(selectorName).text()
            let folderChild = folders[folderName]['children']
            for (const child of folderChild) {
                if (child == name) {
                    //console.log($(this).classList())
                    $(this).addClass(`docker-folder-hide docker-folder-child-${folderName}`)
                    $(this).appendTo("#docker_list_storage")
                }
            }
        })

    }

    async function docker_toggle_visibility(folderName, location) {
        let userprefs = await read_userprefs()

        if (location == "dashboard") {
            var selector = "#db-box3 > tbody > tr > td:nth-child(2) > span"
        } else {
            var selector = "#docker_list > tr"
        }

        $(`.docker-folder-child-${folderName}`).each(function() {
            $(this).toggleClass("docker-folder-hide");
        });
    }
</script>