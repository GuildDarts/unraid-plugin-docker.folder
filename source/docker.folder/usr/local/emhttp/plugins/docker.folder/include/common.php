<?php
require_once("/usr/local/emhttp/plugins/docker.folder/include/folderVersion.php");
require_once("/usr/local/emhttp/plugins/dynamix.docker.manager/include/DockerClient.php");
$DockerClient    = new DockerClient();
$containers      = $DockerClient->getDockerContainers();

$dockerIds = new stdClass;

foreach ($containers as $ct) {
    $name = $ct['Name'];
    $id = $ct['Id'];

    $dockerIds->$name = $id;
}

echo "<script>var dockerIds = " . json_encode($dockerIds) . ';</script>';
// folderVersion var for javascript
echo "<script>foldersVersion = " . $GLOBALS['foldersVersion'] . ';</script>';
?>

<style type="text/css">
    .docker-folder-hide {
        display: none;
    }

    .sub-dropdown-context:before {
        transform: rotate(-90deg);
        position: absolute;
        top: 10px;
        left: -11px;
        display: inline-block;
        border-right: 7px solid transparent;
        border-bottom: 7px solid #ccc;
        border-left: 7px solid transparent;
        border-bottom-color: rgba(0, 0, 0, 0.2);
        content: '';
    }

    .sub-dropdown-context:after {
        transform: rotate(-90deg);
        position: absolute;
        top: 9px;
        left: -10px;
        display: inline-block;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #ffffff;
        border-left: 6px solid transparent;
        content: '';
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

        dropdown.addClass('docker-dropdown-menu')
        
        for (const button of folders[folderName]['buttons']) {
            dropdownButton(dropdown, folderName, button['type'], button['name'], button['icon'], button['cmd'])
        }

        dropdownButton(dropdown, folderName, "divider")
        dropdownButton(dropdown, folderName, null, "Edit Folder", "wrench")
        dropdownButton(dropdown, folderName, null, "Remove Folder", "trash")
    }


    function dropdownButton(dropdown, folderName, type, name, icon, cmd) {

        if (type == "divider") {
            dropdown.append("<li class='divider'></li>")
            return
        }

        dropdown.append(`<li> <a name='${name}' href='#'> <i class='fa fa-fw fa-${icon} fa-lg'></i> &nbsp;&nbsp;${name}</a> </li>`)

        switch (name) {
            case "Edit Folder":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    editFolder(folderName)
                })
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
                break;
        }

        switch (type) {
            case "Docker_Default":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    dockerDefaultCmd(folderName, cmd)
                })
                break;

            case "WebUI":
                $(dropdown).find(`li > a[name ='${name}']`).attr('href', cmd)
                break;

            case "Bash":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    let title = `${name}: ${folderName}`;
                    let address = `/plugins/docker.folder/scripts/action_docker.php?command=${cmd}`;
                    popupWithIframe(title, address, true, 'loadlist');
                })
                break

            case "Docker_Sub_Menu":
                // get dropdown-id
                var id = dockerIds[cmd]
                $(dropdown).find(`li > a`).each(function() {
                    if ($(this).find('i').hasClass('fa-docker')) {
                        $(this).hover(
                            function() {
                                removeSubMenu()
                                addSubMenu($(this), id)
                            },
                            function() {
                                var dropdownHover = false
                                $(`#dropdown-${id}`).hover(
                                    function() {
                                        dropdownHover = true
                                    },
                                    function() {
                                        removeSubMenu(id)
                                    }
                                )
                            }
                        )
                    } else {
                        $(this).hover(function() {
                            removeSubMenu()
                        })
                    }
                })
                break
        }

        // remove docker sub menu when hover over any other button
        if (type !== 'Docker_Sub_Menu') {
            $(dropdown).find(`li > a[name ="${name}"]`).mouseover(function() {
                removeSubMenu()
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

    function dockerDefaultCmd(folderName, action) {
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

    function addSubMenu(e, id) {
        var offset = e.offset();
        $(`#dropdown-${id}`).css({
            position: 'absolute',
            left: offset.left + e.width() + 35,
            top: offset.top,
            display: 'block'
        }).removeClass('dropdown-context').addClass('sub-dropdown-context')
    }

    function removeSubMenu(id) {
        if (id == null) {
            $(`[id*="dropdown-"]`).each(function() {
                if (!$(this).hasClass('docker-dropdown-menu')) {
                    $(this).hide()
                }
            })
            return
        }

        $(`#dropdown-${id}`).css({
            display: 'none'
        })
    }

    $('body').click(function() {
        removeSubMenu()
    })

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
        } catch (err) {
            if (err instanceof SyntaxError) {
                var result = await Promise.resolve($.get("/plugins/docker.folder/scripts/create_folders_file.php"))
                if (result == 'folders.json created') {
                    console.log(await result)
                    return read_folders()
                } else {
                    throw err
                }
            } else {
                throw err
            }
        }
        // check foldersVersion run migration
        if (folders['foldersVersion'] == null || folders['foldersVersion'] < foldersVersion) {
            console.log("Docker Folder: migration")
            await $.post("/plugins/docker.folder/scripts/migration.php");
            folders = await read_folders()
        }
        delete folders['foldersVersion']
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
        $.post("/plugins/docker.folder/scripts/remove_folder.php", {
            folderName: folderName
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