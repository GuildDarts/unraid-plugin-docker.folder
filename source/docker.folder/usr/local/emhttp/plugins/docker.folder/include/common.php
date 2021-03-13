<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once("$docroot/plugins/docker.folder/include/folderVersion.php");

$unraid = parse_ini_file('/etc/unraid-version');

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

    .folder-hide {
        display: none;
    }

    [class*="-folder-child-div-"] {
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

<script>
class folder {
    constructor(_id, _properties, _options) {
        this.id = _id
        this.properties = _properties
        this.options = _options
    }

    parent() {
        return $(`.${this.options['type']}-folder-parent-${this.id}`)
    }

    child() {
        return $(`.${this.options['type']}-folder-child-${this.id}`)
    }
}
</script>

<script src="/plugins/docker.folder/include/freezeframe.min.js"></script>
<script>
    function checkStatus(folder) {
        const folderId = folder.id

        var selector = folder.parent().find("span.inner > i")
        var max = folder['properties']['children'].length
        var cur = 0

        folder.child().each(function() {
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
        if (folder['properties']['status_icon_autostart'] && selector.hasClass('orange-text')) {
            var autoStarted = true

            folder.child().each(function() {
                var childName = $(this).find('.inner > span:first-child').text()
                if ($(this).find("i.fa").hasClass("stopped") && folder.options['autostart'][childName] == 'true') {
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

    function loadDropdownButtons(folder) {
        const folderId = folder.id
        const folderType = folder.options['type']

        context.attach(`#${folderType}-folder-${folderId}`, [{}]);

        let dropdown = $(`#dropdown-${folderType}-folder-${folderId}`)
        dropdown.empty()

        dropdown.addClass('docker-dropdown-menu')

        for (const button of folder['properties']['buttons']) {
            dropdownButton(dropdown, folder, button['type'], button['name'], button['icon'], button['cmd'])
        }

        dropdownButton(dropdown, folder, "divider")
        dropdownButton(dropdown, folder, null, "Edit Folder", "wrench")
        dropdownButton(dropdown, folder, null, "Remove Folder", "trash")
    }


    function dropdownButton(dropdown, folder, type, name, icon, cmd) {
        const folderName = folder.properties['name']
        const folderId = folder.id
        const folderType = folder.options['type']

        if (type == "divider") {
            dropdown.append("<li class='divider'></li>")
            return
        }

        dropdown.append(`<li> <a name='${name}' href='#'> <i class='fa fa-fw fa-${icon} fa-lg'></i> &nbsp;&nbsp;${name}</a> </li>`)

        switch (name) {
            case "Edit Folder":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    editFolder(folderId, folder.options['type'])
                })
                break;

            case "Remove Folder":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    swal({
                        title: "Are you sure?",
                        text: `Remove folder: ${folder.properties['name']} (${folderId})`,
                        type: "warning",
                        showCancelButton: true
                    }, function() {
                        folderRemove(folder)
                    });
                })
                break;
        }

        switch (type) {
            case "Action":
                let selector = $(dropdown).find(`li > a[name ="${name}"]`)
                if (folderType !== 'vm') {
                    selector.click(function() {
                        dockerDefaultCmd(folder, cmd)
                    })
                } else {
                    selector.click(function() {
                        vmDefaultCmd(folder, cmd)
                    })
                }
                break;

            case "WebUI":
                $(dropdown).find(`li > a[name ='${name}']`).attr('href', webUIMatch(folder, cmd))
                break;

            case "WebUI_New_Tab":
                $(dropdown).find(`li > a[name ='${name}']`).attr('href', webUIMatch(folder, cmd)).attr('target', '_blank')
                break;

            case "Bash":
                $(dropdown).find(`li > a[name ="${name}"]`).click(function() {
                    let title = `${name}: ${folderName} (${folderId})`
                    let address = `/plugins/docker.folder/scripts/action_docker.php?command=${cmd}`
                    popupWithIframe(title, address, true, 'loadlist')
                })
                break

            case "Sub_Menu":
                // get dropdown-id
                var id = folder.options['ids'][cmd]
                $(dropdown).find(`li > a[name ="${name}"]`).each(function() {
                    if ($(this).find('i').hasClass('fa-docker')) {
                        $(this).parent().addClass('dropdown-subMenu')
                        $(this).hover(
                            function() {
                                removeSubMenu(folderType)
                                addSubMenu(folderType, $(this), id)
                            },
                            function() {
                                var dropdownHover = false
                                $(`#dropdown-${id}`).hover(
                                    function() {
                                        dropdownHover = true
                                    },
                                    function() {
                                        removeSubMenu(folderType, id)
                                    }
                                )
                            }
                        )
                    } else {
                        $(this).hover(function() {
                            removeSubMenu(folderType)
                        })
                    }
                })
                break
        }

        // remove docker sub menu when hover over any other button
        if (type !== 'Docker_Sub_Menu') {
            $(dropdown).find(`li > a[name ="${name}"]`).mouseover(function() {
                removeSubMenu(folderType)
            })
        }

        function webUIMatch(folder, url = '') {
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
                url = getDockerWebUI(folder.options['ids'][docker])
            }

            return url
        }

    }

    function edit_folder_base(folder) {
        const folderId = folder.id
        const folderName = folder.properties['name']
        const folderType = folder.options['type']

        // regex
        if (folder['properties']['regex'] !== '' && folder['properties']['regex'] != null) {
            let dockerIdsKeys = Object.keys(folder.options['ids'])
            let regex = new RegExp(folder['properties']['regex'])
            for (const docker of dockerIdsKeys) {
                if (docker.match(regex) && !folder['properties']['children'].includes(docker)) {
                    folder['properties']['children'].push(docker)
                }
            }
        }

        // add folder element
        folder.options['folderChildren'] = folder.options['folderChildren'].concat(folder['properties']['children'])

        if (location.pathname === '/Dashboard') {
            let type = (folderType === 'docker') ? 'apps': 'vms'
            var selector = `#db-box3 > tbody.${folderType}_view > tr > td:nth-child(2)`
            var folderTemplate = `<span class="outer solid ${type} stopped ${folderType}-folder-parent-${folderId}" data-id="${folderId}" data-name="${folderName}"><span class="hand" id="${folderType}-folder-${folderId}"><img src="/plugins/dynamix.docker.manager/images/question.png?1587731339" class="img"></span><span class="inner"><span class="">${folderName}</span><br><i class="fa fa-square stopped red-text"></i><span class="state">folder</span></span></span>`
        } else {
            var selector = folder['options']['listSelector']
            let name = (folderType === 'vm') ? 'vm' : 'ct'
            if (folder['options']['type'] === 'docker') {
                const dockerhandle = '<?= version_compare($unraid['version'], '6.9.0', '>=') ? '<td><span class="dockerhandle"><i class="fa fa-arrows-v"></i></span></td>' : '' ?>'
                var folderTemplate = `<tr class="sortable ${folderType}-folder-parent-${folderId}" data-id="${folderId}" data-name="${folderName}"><td class="${name}-name" style="width:220px;padding:8px;"><div><span class="outer"><span class="hand" id="${folderType}-folder-${folderId}"><img src="/plugins/dynamix.docker.manager/images/question.png?1587731339" class="img"></span><span class="inner"><span class="appname"><a class="exec" onclick="editFolder('${folderId}', '${folderType}')">${folderName}</a></span><br><i class="fa fa-square stopped red-text"></i><span class="state">folder</span></span></span></td><td class="updatecolumn"></td><td colspan="3" class="dockerPreview"></td><td class="advanced" style="display: table-cell;"><span class="cpu">USAGE</span><div class="usage-disk mm"><span id="cpu" style="width: 0%;"></span><span></span></div><br><span class="mem">USAGE</span></div></td><td class="autostart"></td><td></td>${dockerhandle}</tr>`
            } else {
                var folderTemplate = `<tr class="sortable ${folderType}-folder-parent-${folderId}" data-id="${folderId}" data-name="${folderName}"><td class="${name}-name" style="width:220px;padding:8px;"><div><span class="outer"><span class="hand" id="${folderType}-folder-${folderId}"><img src="/plugins/dynamix.docker.manager/images/question.png?1587731339" class="img"></span><span class="inner"><span class="appname"><a class="exec" onclick="editFolder('${folderId}', '${folderType}')">${folderName}</a></span><br><i class="fa fa-square stopped red-text"></i><span class="state">folder</span></span></span></td><td colspan="5" class="dockerPreview"></td><td class="autostart"></td></tr>`
            }
        }

        const prefsArray = Object.values(folder['options']['prefs']);

        var insertIndex = 0

        // add another index if folder is expanded, as children are in there own div
        for (const docker of folder.options['activeDropdowns']) {
            if (folder.options['activeFolders'].includes(docker)) {
                insertIndex++
            }
        }

        // insert at start if not in prefs
        if (!prefsArray.includes(`${folderId}-folder`)) {
            insertAtIndex(0, folderTemplate, selector)
        } else {
            for (pref of prefsArray) {
                if (pref === `${folderId}-folder`) {
                    insertAtIndex(insertIndex, folderTemplate, selector)
                    break
                }
                if (folder.options['folderChildren'].includes(pref)) {
                    continue
                }
                // continue if folder is not in activeFolders
                if (pref.includes('-folder') && !folder.options['activeFolders'].includes(pref.slice(0, -7))) {
                    continue
                }
                // continue incase folder does not get remove from userprefs (better safe than sorry)
                let folderIds = Object.keys(folders[folderType])
                if (pref.includes('-folder') && !folderIds.includes(pref.slice(0, -7))) {
                    continue
                }
                insertIndex++
            }
        }

        // add folder to activeFolders
        folder.options['activeFolders'].push(folderId)

        loadDropdownButtons(folder)

        // set icon
        let icon = folder['properties']['icon']
        if (icon !== '') {
            if ( (icon.slice(icon.length - 3) === 'svg' || icon.includes('image\/svg+xml')) && folder['properties']['icon_animate_hover'] && !isApple) {

                const decodedSVG = atob(icon.replace('data:image\/svg+xml;base64,', ''));

                if (decodedSVG.includes('keyframes')) {
                    folder.parent().find('img').replaceWith(decodedSVG)

                    const svgElement = folder.parent().find('svg')
                    const svgId = svgElement.attr('id')
                    svgElement.addClass('img')

                    svgElement.find('style')[0].sheet.insertRule(`#${svgId} * {animation-play-state: paused !important}`, 0)

                    $(`.${folderType}-folder-parent-${folderId},.${folderType}-folder-child-div-${folderId},#dropdown-${folderType}-folder-${folderId}`).hover(
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
                    folder.parent().find('img').attr('src', icon)
                }

            } else {
                folder.parent().find('img').attr('src', icon)

                if ( (icon.slice(icon.length - 3) === 'gif' || icon.includes('image\/gif')) && folder['properties']['icon_animate_hover'] && !isApple) {
                    let element = folder.parent().find('img').addClass('freezeframe')
                    const iconFreeze = new Freezeframe({
                        selector: element,
                        trigger: false,
                        overlay: false,
                        responsive: false,
                        warnings: false
                    });

                    $(`.${folderType}-folder-parent-${folderId},.${folderType}-folder-child-div-${folderId},#dropdown-${folderType}-folder-${folderId}`).hover(
                        function() {
                            iconFreeze.start()
                        }, function() {
                            iconFreeze.stop()
                        }
                    );
                }
            }
        }

        docker_hide(folder)
        checkStatus(folder)

    }

    function insertAtIndex(i, template, selector) {
        if (i === 0) {
            $(selector).prepend($(template));
            return;
        }

        $(`${selector} > :not([id*="name-"])`).eq(i-1).after($(template));
    }

    function getDockerWebUI(id) {
        let href = $(`#dropdown-${id}`).find('li:first-child > a').attr('href')
        
        if (href !== '#') {
            return href
        }
    }

    function showContextMenu(e, id, folder) {
        if (!folder['properties']['docker_preview_advanced_context_menu']) {
            setTimeout(function(){
                let height = $(`#dropdown-${id}`).height()
                $(`#dropdown-${id}`).css({
                    position: 'absolute',
                    top: e.pageY + 25,
                    left: e.pageX - 13,
                    display: 'block'
                })
            }, 1)
        }
    }

    function addSubMenu(folderType, e, id) {
        let vm = (folderType === 'vm') ? 'vm-' : ''
        var offset = e.offset();
        setTimeout(function(){
            $(`#dropdown-${vm}${id}`).css({
                position: 'absolute',
                left: offset.left + e.width() + 35,
                top: offset.top,
                display: 'block'
            }).removeClass('dropdown-context').addClass('sub-dropdown-context')
        }, 1)
    }

    function removeSubMenu(folderType, id) {
        let vm = (folderType === 'vm') ? 'vm-' : ''
        if (id == null) {
            $(`[id*="dropdown-${vm}"]`).each(function() {
                if (!$(this).hasClass('docker-dropdown-menu')) {
                    $(this).hide()
                }
            })
            return
        }

        $(`#dropdown-${vm}${id}`).css({
            display: 'none'
        })
    }

    $(document).click(function() {
        removeSubMenu('docker')
        removeSubMenu('vm')
    })

    function editFolder(folderId, type) {
        var path = location.pathname;
        var x = path.indexOf('?');
        if (x != -1) path = path.substring(0, x);
        location = `${path}/UpdateFolder?type=${type}&folderId=${folderId}`;
    }

    async function read_folders(file, runscount) {
        var runs = 1 + (parseInt(runscount) || 0);
        postResult = await Promise.resolve($.get('/plugins/docker.folder/scripts/read_folders.php', {
            file: file
        }));
        try {
            var tmpFolders = await JSON.parse(postResult)
        } catch (err) {
            if (err instanceof SyntaxError) {
                var result = await Promise.resolve($.get("/plugins/docker.folder/scripts/create_folders_file.php", {
                    file: file
                }))
                if (result.includes('created')) {
                    console.log(`${file}.json created`)
                    return read_folders(file)
                } else {
                    throw err
                }
            } else {
                throw err
            }
        }
        // check foldersVersion run migration
        if (tmpFolders['foldersVersion'] == null || tmpFolders['foldersVersion'] < foldersVersion) {
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
            $.post("/plugins/docker.folder/scripts/migration/common_migration.php", function() {
                read_folders(file, runs)
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

        delete tmpFolders['foldersVersion']
        return await tmpFolders
    }

    function folderRemove(folder) {
        $.post("/plugins/docker.folder/scripts/remove_folder.php", {
            type: folder.options['type'],
            folderId: folder.id
        }, function() {
            location.reload()
        });
    }

    function docker_hide(folder) {
        const folderId = folder.id
        const folderType = folder.options['type']

        if (location.pathname === '/Dashboard') {
            $(`#${folderType}_list_storage > .${folderType}-folder-child-${folderId}`).remove()
            var selector = `#db-box3 > tbody.${folderType}_view > tr > td:nth-child(2) > span.outer`
            var selectorName = folder.options['dashboardHideSelectorName']
        } else {
            $(`#${folderType}_list_storage > .${folderType}-folder-child-div-${folderId} > .${folderType}-folder-child-${folderId}`).remove()
            var selector = folder['options']['hideSelector']
            var selectorName = folder['options']['hideSelectorName']
        }

        $(selector).each(function() {
            let name = $(this).find(selectorName).textFirst()
            let folderChildren = folder['properties']['children']
            for (const child of folderChildren) {
                if (child === name && !$(this).attr('class').includes('-folder-parent-')) {
                    // hide disk devices for vms
                    if (folderType === 'vm' && location.pathname !== '/Dashboard') {
                        let id = $(this).attr('parent-id')
                        slideUpRows($(`#name-${id}`))
                    }

                    $(this).addClass(`folder-hide ${folderType}-folder-child-${folderId}`)
                    $(this).appendTo(`#${folderType}_list_storage`)
                }
            }
        })

    }

    function docker_toggle_visibility(folderId, folderType) {
        $(`.${folderType}-folder-child-${folderId}`).each(function() {
            $(this).toggleClass('folder-hide');
        });
    }

    const isApple = /iPhone|iPad|iPod|Mac|Macintosh/i.test(navigator.userAgent)

    var waitForGlobal = function(key, callback) {
        if (window[key]) {
            callback();
        } else {
            setTimeout(function() {
                waitForGlobal(key, callback);
            }, 100);
        }
    };
    
    function searchArrayAndReplace(searchKeyword, dataArray, replace) {
        let lastIndex = -1

        for (let index = 0; index < dataArray.length; index++) {
            if (dataArray[index].includes(searchKeyword)) {
                lastIndex = index
                break
            }
        }

        if (lastIndex !== -1) {
            dataArray.splice(lastIndex, 1, replace);
        }

        return dataArray
    }

    (function($) {
        // return only selector text if its not empty
        $.fn.textFirst = function() {
            let text = $(this).contents().filter(function(){ 
                return this.nodeType == 3; 
            }).text()

            return (text !== '') ? text : $(this).text()
        };
    }(jQuery));
</script>
