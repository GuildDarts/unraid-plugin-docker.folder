<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once("$docroot/plugins/docker.folder/include/folderVersion.php");
require_once("$docroot/plugins/dynamix.docker.manager/include/DockerClient.php");

$user_prefs      = $dockerManPaths['user-prefs'];
if (file_exists($user_prefs)) {
    $prefs = json_encode(parse_ini_file($user_prefs));
} else {
    $prefs = json_encode([]);
}

$DockerClient    = new DockerClient();
$DockerTemplates = new DockerTemplates();
$containers      = $DockerClient->getDockerContainers();
$allInfo         = $DockerTemplates->getAllInfo();

$dockerIds = new stdClass;
$dockerAutostart = new stdClass;

foreach ($containers as $ct) {
    $name = $ct['Name'];
    $id = $ct['Id'];
    $info = &$allInfo[$name];
    $is_autostart = $info['autostart'] ? 'true' : 'false';

    $dockerIds->$name = $id;
    $dockerAutostart->$name = $is_autostart;
}

echo "<script>var dockerIds = " . json_encode($dockerIds) . ';</script>';
echo "<script>var dockerAutostart = " . json_encode($dockerAutostart) . ';</script>';

// folderVersion var for javascript
echo "<script>foldersVersion = " . $GLOBALS['foldersVersion'] . ';</script>';
?>

<style type="text/css">
    .img {
        width: 32px;
        height: 32px;
        margin-right: 10px;
        border: none;
        text-decoration: none;
        vertical-align: middle;
    }

    .docker-folder-hide {
        display: none;
    }

    [class*="docker-folder-child-div-"] {
        display: contents;
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

    .dropdown-subMenu > a:after {
        display: block;
        float: right;
        width: 0;
        height: 0;
        margin-top: 5px;
        margin-right: -10px;
        border-color: transparent;
        border-left-color: #cccccc;
        border-style: solid;
        border-width: 5px 0 5px 5px;
        content: " ";
    }
</style>

<script src="/plugins/docker.folder/include/freezeframe.min.js"></script>
<script>
    function checkStatus(folderName) {
        var selector = $(`.docker-folder-parent-${folderName}`).find("span.inner > i")
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

        // status_icon_autostart
        if (folders[folderName]['status_icon_autostart'] && selector.hasClass('orange-text')) {
            var autoStarted = true

            $(`.docker-folder-child-${folderName}`).each(function() {
                var childName = $(this).find('.inner > span:first-child').text()
                if ($(this).find("i.fa").hasClass("stopped") && dockerAutostart[childName] == 'true') {
                    autoStarted = false
                    return
                }
            });

            if (autoStarted) {
                selector.removeClass()
                selector.addClass("fa started fa-play green-text")
            }
        }

        selector.find("span.state").remove()
        selector.parent().find("span.state").before(`<span class="state">${cur}/${max}</span>`)
    }

    function loadDropdownButtons(folderName) {
        context.attach(`#folder-${folderName}`, [{}]);

        let dropdown = $(`#dropdown-folder-${folderName}`)
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
                $(dropdown).find(`li > a[name ='${name}']`).attr('href', webUIMatch(cmd))
                break;

            case "WebUI_New_Tab":
                $(dropdown).find(`li > a[name ='${name}']`).attr('href', webUIMatch(cmd)).attr('target', '_blank')
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
                $(dropdown).find(`li > a[name ="${name}"]`).each(function() {
                    if ($(this).find('i').hasClass('fa-docker')) {
                        $(this).parent().addClass('dropdown-subMenu')
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

        function webUIMatch(url = '') {
            // why does it not work without "|| []" *hmmm
            var ipRegex = /\[IP\]/g
            var portRegex = /\[PORT:(\d+)\]/g
            var dockerRegex = /\[DOCKER:([\w\-]+)\]/g

            var portMatch = portRegex.exec(url) || []
            var port = portMatch[1]

            var dockerMatch = dockerRegex.exec(url) || []
            var docker = dockerMatch[1]

            url = url.replace(portRegex, port)
            url = url.replace(ipRegex, "<?= $host ?>")

            if (docker !== undefined) {
                url = getDockerWebUI(docker)
            }

            return url

            function getDockerWebUI(docker) {
                let id = dockerIds[docker]
                let href = $(`#dropdown-${id}`).find('li:first-child > a').attr('href')
                
                if (href !== '#') {
                    return href
                }
            }
        }

    }

    function edit_folder_base(folderName) {
        // regex
        if (!(folders[folderName]['regex'] == '' || folders[folderName]['regex'] == null)) {
            let dockerIdsKeys = Object.keys(dockerIds)
            let regex = new RegExp(folders[folderName]['regex'])
            for (const docker of dockerIdsKeys) {
                if (docker.match(regex) && !folders[folderName]['children'].includes(docker)) {
                    folders[folderName]['children'].push(docker)
                }
            }
        }

        // add folder element
        folderChildren = folderChildren.concat(folders[folderName]['children'])

        if (location.pathname == "/Dashboard") {
            var selector = "#db-box3 > tbody.docker_view > tr > td:nth-child(2)"
            var selectorType = "span"
            var folderTemplate = `<span class="outer solid apps stopped docker-folder-parent-${folderName}"><span class="hand" id="folder-${folderName}"><img src="/plugins/dynamix.docker.manager/images/question.png?1587731339" class="img"></span><span class="inner"><span class="">${folderName}</span><br><i class="fa fa-square stopped red-text"></i><span class="state">folder</span></span></span>`
        } else {
            var selector = "#docker_list"
            var selectorType = "tr"
            var folderTemplate = `<tr class="sortable docker-folder-parent-${folderName}"><td class="ct-name" style="width:220px;padding:8px;"><div><span class="outer"><span class="hand" id="folder-${folderName}"><img src="/plugins/dynamix.docker.manager/images/question.png?1587731339" class="img"></span><span class="inner"><span class="appname ">${folderName}</span><br><i class="fa fa-square stopped red-text"></i><span class="state">folder</span></span></span></td><td class="updatecolumn"></td><td colspan="3" class="dockerPreview"></td><td class="advanced" style="display: table-cell;"><span class="cpu">USAGE</span><div class="usage-disk mm"><span id="cpu" style="width: 0%;"></span><span></span></div><br><span class="mem">USAGE</span></div></td><td></td><td></td></tr>`
        }

        var perfs = <?= $prefs ?>;
        var insertIndex = 0
        // insert at start if not in perfs
        if (!perfs.includes(`${folderName}-folder`)) {
            insertAtIndex(insertIndex, folderTemplate, selector, selectorType)
        } else {
            for (i = 0; i < perfs.length; i++) {
                if (perfs[i] == `${folderName}-folder`) {
                    insertAtIndex(insertIndex, folderTemplate, selector, selectorType)
                    break
                }
                if (folderChildren.includes(perfs[i])) {
                    continue
                }
                // continue incase folder does not get remove from userprefs (better safe than sorry)
                let folderNames = Object.keys(folders)
                if (perfs[i].includes('-folder') && !folderNames.includes(perfs[i].slice(0, -7))) {
                    continue
                }
                insertIndex++
            }

        }

        loadDropdownButtons(folderName)

        // set icon
        let icon = folders[folderName]['icon']
        if (icon !== '') {
            if ( (icon.slice(icon.length - 3) === 'svg' || icon.includes('image\/svg+xml')) && folders[folderName]['icon_animate_hover'] ) {

                const decodedSVG = atob(icon.replace('data:image\/svg+xml;base64,', ''));

                if (decodedSVG.includes('keyframes')) {
                    $(`.docker-folder-parent-${folderName}`).find('img').replaceWith(decodedSVG)

                    const svgElement = $(`.docker-folder-parent-${folderName}`).find('svg')
                    const svgId = svgElement.attr('id')
                    svgElement.addClass('img')

                    svgElement.find('style')[0].sheet.insertRule(`#${svgId} * {animation-play-state: paused !important}`, 0)

                    $(`.docker-folder-parent-${folderName},.docker-folder-child-div-${folderName},#dropdown-folder-${folderName}`).hover(
                        function() {
                            setSvgPlayState('running')
                        }, function() {
                            setSvgPlayState('paused')
                        }
                    );

                    function setSvgPlayState(state) {
                        svgElement.find('style')[0].sheet.cssRules[0].style['cssText'] = `animation-play-state: ${state} !important`
                    }
                    
                } else {
                    $(`.docker-folder-parent-${folderName}`).find('img').attr('src', icon)
                }

            } else {
                $(`.docker-folder-parent-${folderName}`).find('img').attr('src', icon)

                if ( (icon.slice(icon.length - 3) === 'gif' || icon.includes('image\/gif')) && folders[folderName]['icon_animate_hover'] ) {
                    let element = $(`.docker-folder-parent-${folderName}`).find('img').addClass('freezeframe')
                    const iconFreeze = new Freezeframe({
                        selector: element,
                        trigger: false,
                        overlay: false,
                        responsive: false,
                        warnings: false
                    });

                    $(`.docker-folder-parent-${folderName},.docker-folder-child-div-${folderName},#dropdown-folder-${folderName}`).hover(
                        function() {
                            iconFreeze.start()
                        }, function() {
                            iconFreeze.stop()
                        }
                    );
                }
            }
        }

        docker_hide(folderName)
        checkStatus(folderName)

    }

    function insertAtIndex(i, template, selector, selectorType) {
        if (i === 0) {
            $(selector).prepend($(template));
            return;
        }


        $(`${selector} > ${selectorType}:nth-child(${i})`).after($(template));
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

    function showContextMenu(e, id) {
        setTimeout(function(){
            let height = $(`#dropdown-${id}`).height()
            $(`#dropdown-${id}`).css({
                position: 'absolute',
                top: e.pageY + 10,
                left: e.pageX - 13,
                display: 'block'
            })
        }, 1)
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

    async function read_folders(runscount) {
        var runs = 1 + (parseInt(runscount) || 0);
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
            // check if to many runs
            console.log(runs)
            if (runs >= 5) {
                swal({
                    title: "read_folders error",
                    text: "looks like migration is running wild. Please report this on the forums",
                    type: "warning",
                    showCancelButton: false
                })
                return
            }
            console.log("Docker Folder: migration")
            $.post("/plugins/docker.folder/scripts/migration.php", function() {
                read_folders(runs)
            });
        }

        if (runs > 1) {
            swal({
                title: "docker.folder migration",
                text: "looks like migration just ran. You should only see this once after an update. (you should refresh browser)",
                type: "info",
                showCancelButton: false
            })
        }

        delete folders['foldersVersion']
        return await folders
    }

    function folderRemove(folderName) {
        $.post("/plugins/docker.folder/scripts/remove_folder.php", {
            folderName: folderName
        }, function() {
            location.reload()
        });
    }

    function docker_hide(folderName) {

        if (location.pathname == "/Dashboard") {
            $(`#docker_list_storage > .docker-folder-child-${folderName}`).remove()
            var selector = "#db-box3 > tbody.docker_view > tr > td:nth-child(2) > span"
            var selectorName = "span.inner > span:first-child"
        } else {
            $(`#docker_list_storage > div.docker-folder-child-div-${folderName} > .docker-folder-child-${folderName}`).remove()
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

        if (location == "dashboard") {
            var selector = "#db-box3 > tbody > tr > td:nth-child(2) > span"
        } else {
            var selector = "#docker_list > tr"
        }

        $(`.docker-folder-child-${folderName}`).each(function() {
            $(this).toggleClass("docker-folder-hide");
        });
    }

    var waitForGlobal = function(key, callback) {
        if (window[key]) {
            callback();
        } else {
            setTimeout(function() {
                waitForGlobal(key, callback);
            }, 100);
        }
    };
</script>