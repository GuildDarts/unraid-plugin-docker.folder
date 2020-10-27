<style>
    :root {
        --border-color: #c4c4c4;
    }
    [class*="-folder-parent-"] > .updatecolumn {
        padding: 4px 0px 4px 6px;
    }
    [class*="-folder-child-"] > td:first-child {
        padding-left: 30px !important;
    }
    .expanded_right {
        float: right;
        transform: translate(-12px, 5px);
        color: #7c7c7c;
        border-style: solid;
        border-color: var(--border-color);
        border-width: 1px;
        border-radius: 5px;
        padding: 5px;
    }
    [class*="-folder-child-div-"] > tr:first-child {
        border-top: 1px solid var(--border-color);
    }
    [class*="-folder-child-div-"] > tr {
        border-bottom: 1px solid var(--border-color);
    }

    .label-tab > td:first-child > div > .outer > .inner {
        padding-top: 6px;
    }
    .label-tab > td:first-child, .dockerPreview {
        padding: 4px !important;
    }
    .label-tab > td:first-child > div {
        max-width: 275px;
        border-style: solid;
        border-color: var(--border-color);
        border-width: 1px;
        border-radius: 5px;
        box-shadow: -10px 0 0px -2px #91c8f0 inset;
    }
    .label-tab > td:first-child > div > .outer > .hand img {
        height: 42px;
        width: 42px;
        margin-right: 0px;
    }
    .label-tab > td:first-child > div > .outer > .hand > :first-child {
        height: 42px;
        width: 42px;
        border-right: solid;
        border-width: 1px;
        border-color: var(--border-color);
        padding: 2px;
        margin-right: 6px;
    }
    .ff-container > .ff-canvas {
        top: 2px;
        left: 2px;
    }
    .label-tab .expanded_right {
        transform: translate(-15px, 10px);
    }

    .dockerPreview-no-icon-container {
        display: grid;
        grid-template-columns: max-content repeat(6, max-content );
        color: #626262;
        border-style: solid;
        border-color: var(--border-color);
        border-width: 1px;
        border-radius: 5px;
        margin-block-start: initial;
        margin-block-end: initial;
        margin-inline-start: initial;
        margin-inline-end: initial;
        padding-inline-start: initial;
        list-style-position: inside;
        padding-top: 6px;
        padding-bottom: 6px;
        box-shadow: 10px 0 0px -2px #beddf5 inset;
    }
    .dockerPreview-no-icon-container > div {
        padding-left: 10px;
        white-space: nowrap;
        overflow: hidden;
    }
    .dockerPreview-no-icon-container > .header {
        display: inline-grid;
        align-items: center;
        font-size: 14px;
        font-weight: bold;
        grid-area: 1 / 1 / span 2 / span 1;
        border-right: solid;
        border-width: 1px;
        border-color: var(--border-color);
        padding-right: 6px;
        margin-top: -6px;
        margin-bottom: -6px;
        margin-left: 4px;
    }
    .dockerPreview-no-icon-container li {
        padding-left: 4px;
        cursor: pointer;
    }
    .dockerPreview-no-icon-container > li > span {
        position: relative;
        left: -10px;
    }
    /* chrome/firefox issue with bullet points */
    @media screen and (min--moz-device-pixel-ratio:0) {
        .dockerPreview-no-icon-container > li > span {
            left: -5px;
        }
    }

    .dockerPreview-icon-container {
        border-style: solid;
        border-color: var(--border-color);
        border-width: 1px;
        border-radius: 5px;
        box-shadow: 10px 0 0px -2px #beddf5 inset;
        padding: 2px;
    }
    .dockerPreview-icon-container img.img {
        margin-right: 6px;
    }
    .dockerPreview-icon-container > td:first-child img.img {
        padding-left: 4px;
    }
    .dockerPreview-icon-container .log > img {
        display: inline-block;
        padding-left: 3px;
    }
    .dockerPreview-icon-container td {
        width: auto;
        padding-right: 6px !important;
        white-space: nowrap;
        position: relative;
    }
    .dockerPreview-icon-container td:not(:first-child) {
        padding-left: 6px;
    }
    .dockerPreview-icon-container td:last-child {
        border-right: 0px;
    }
    .dockerPreview-grayscale {
        white-space: nowrap;
        overflow: hidden;
        filter: grayscale(100%);
        opacity: 0.9;
    }

    .border-right {
        left: 100%;
        top: 3px;
        bottom: 3px;
        position: absolute;
        border-right: 1px solid #b9b7b7;
    }
</style>


<script>
function edit_folder_extra(folder) {
    const folderId = folder.id
    const folderType = folder.options['type']

    // advanced view fix
    if (!$('input.advancedview').prop('checked')) {
        folder.parent().children('td.advanced').hide()
    }

    // add show children button
    if (folder.parent().find('.outer > i').length == 0) {

        switch (folder['properties']['docker_expanded_style']) {
            case 'bottom':
                folder.parent().find('.outer').append(`<br><i class="fa fa-fw fa-chevron-up expanded_bottom""></i>`)
                break;

            case 'right':
                folder.parent().find('.outer').append(`<i class="fa fa-fw fa-chevron-up expanded_right""></i>`)
                break;
        }

        folder.parent().find('.outer > i').click(function() {
            childrenDropdown(folder)
        });
    }

    // docker_start_expanded - activeDropdowns
    if (folder['properties']['docker_start_expanded'] == true && !folder.options['activeDropdowns'].includes(folderId)) {
        folder.options['activeDropdowns'].push(folderId)
    }

    // wrap children in div
    folder.child().wrapAll(`<div class='${folderType}-folder-child-div-${folderId}'></div>`)

    // remove sortable from children and add new sortable
    // adjust child width (done to counter padding)
    folder.child().each(function() {
        $(this).removeClass("sortable").addClass(`sortable-child-${folderId}`)
        $(this).children('td:first-child').css({
            'width': '190'
        })
    })

    // custom sortable helper for children
    var sortableHelper2 = function(e,i){
        i.children().each(function(){
            $(this).width($(this).width());
        });
        return $(i).clone();
    };

    // make children sortable
    $(`.${folderType}-folder-child-div-${folderId}`).sortable({helper:sortableHelper2,appendTo: document.body,cursor:'move',axis:'y',containment:'parent',cancel:'span.docker_readmore,input',delay:100,opacity:0.5,zIndex:9999,
    update:function(e,ui){
        var children = []
        $(`.${folderType}-folder-child-div-${folderId} > tr`).each(function() {
            var nam = $(this).find('.appname').text();
            children.push(nam)
        })
        folder['properties']['children'] = children
        loadlistUpdate(folderType)
    }})

    // open dropdown on loadlist
    for (const dropdown of folder.options['activeDropdowns']) {
        if (dropdown == folderId) {
            docker_toggle_visibility(folderId, folderType)
            folder.parent().find('.outer > i').toggleClass('fa-chevron-down fa-chevron-up')
            childrenMove(folder)
        }
    }

    // folder update status
    var upToDate = true
    for (const child of folder['properties']['children']) {
        waitForGlobal("docker", function() {
            for (const dock of docker) {
                if (child == dock['name']) {
                    if (dock['update'] == 1) {
                        upToDate = false
                    }
                }
            }
        })
    }
    if (upToDate == true) {
        var updateConf = `<span class="green-text" style="white-space:nowrap;"><i class="fa fa-check fa-fw"></i> up-to-date</span>`
    } else {
        var updateConf = `<span class="orange-text" style="white-space:nowrap;"><i class="fa fa-flash fa-fw"></i> update ready</span>`
    }
    folder.parent().find('td.updatecolumn').html(updateConf)

    // // auto start folder
    autoStartFolder(folder)

    // // docker preview
    dockerPreview(folder)

    // icon style
    iconStyle(folder)
}

function childrenDropdown(folder) {
    const folderId = folder.id
    const folderType = folder.options['type']

    docker_toggle_visibility(folderId, folderType)
    folder.parent().find('.outer > i').toggleClass('fa-chevron-down fa-chevron-up')

    if (folder.parent().find('.outer > i').hasClass('fa-chevron-up')) {
        // hide disk devices for vms
        if (folderType === 'vm') {
            folder.child().each(function() {
                let id = $(this).attr('parent-id')
                slideUpRows($(`#name-${id}`))
            })
        }

        $(`.${folderType}-folder-child-div-${folderId}`).each(function() {
            $(this).appendTo(`#${folderType}_list_storage`)
        });
        // remove folderId from activeDropdowns
        folder.options['activeDropdowns'] = folder.options['activeDropdowns'].filter(function(elm) {
            return elm != folderId
        })
    } else {
        // show disk devices for vms
        if (folderType === 'vm') {
            let cookie = $.cookie('vmshow')||'';
            let cookieArray = cookie.split(',')

            for (const cookie of cookieArray) {
                slideDownRows($(`#${cookie}`))
            }
        }

        childrenMove(folder)
        // add folderId to activeDropdowns
        folder.options['activeDropdowns'].push(folderId)
    }
}

function childrenMove(folder) {
    const folderId = folder.id
    const folderType = folder.options['type']

    $(`.${folderType}-folder-child-div-${folderId}`).insertAfter(folder.parent())
    $('#kvm_list').find('div > tr').each(function(){
        var parent = $(this).attr('parent-id');
        var child = $('tr[child-id="'+parent+'"]');
        child.detach().insertAfter($(this));
    });
}

function autoStartFolder(folder) {
    // set the init state for folder autostart
    if (checkFolderSwitch()) {
        var initState = 'checked'
    }

    // adds switches
    folder.parent().find('td.autostart').html(
        `<input type="checkbox" class="folder-autostart" folder="${folder.id}" ${initState}>`
    ).find('input').switchButton({
        labels_placement: "right"
    });

    // event: clicks all the children so they match parent
    folder.parent().find('input.folder-autostart').change(function() {
        clickChild($(this).prop('checked'))
    });
    

    function clickChild(state) {
        folder.child().find('input.autostart').each(function() {
            if (state !== $(this).prop('checked')) {
                $(this).parent().find('div.switch-button-background').trigger('click')
            }
        })
    }

    // returns true if any child has autostart checked
    function checkFolderSwitch() {
        var initStateArray = []
        folder.child().find('input.autostart').each(function() {
            initStateArray.push($(this).prop('checked'))
        })
        
        return initStateArray.some( (val, i, arr) => val === true)
    }


}

function dockerPreview(folder) {
    const folderId = folder.id
    const folderType = folder.options['type']
    const dockerPreview = $(`.${folderType}-folder-parent-${folderId} > .dockerPreview`)

    switch (folder['properties']['docker_preview']) {
        case 'no-icon':
            dockerPreview.append(`<ul class="dockerPreview-no-icon-container"><div class="header">Dockers:</div></ul>`)
            $(`.${folderType}-folder-child-div-${folderId}`).children().each(function(i) {
                if (i < 12) {
                    let child = $(this).find('.appname').text()
                    let id = $(this).find('.hand').attr('id')

                    let row = ((2+i) % 2 == 0) ? 1 : 2
                    let element = $(`<li class="docker-preview-id-${id}"><span>${child}</span></li>`)
                    dockerPreview.find('.dockerPreview-no-icon-container').append(element)
                    dockerPreview.find('.dockerPreview-no-icon-container > li:last-of-type').css('grid-area', `${row}`)

                    // make text orange on update
                    updateText(folderId, id, element, 'no-icon')

                    // add context menu
                    dockerPreview.find(`.dockerPreview-no-icon-container > li.docker-preview-id-${id}`).click(function(e) {
                        showContextMenu(e, id, folder)
                    })
                }
            })

            hoverOnly(folderId)
            break;

        case 'icon':
            dockerPreview.append(`<div class="dockerPreview-icon-container"></div></div>`)
            var dockerPreviewWidth = dockerPreview.width()
            var widthTotal = 0
            var childrenCount = $(`.${folderType}-folder-child-${folderId}`).length
            $(`.${folderType}-folder-child-${folderId}`).each(function(i) {
                let clone = $(this).children('td:first-child').clone(true).removeAttr('style class').appendTo(dockerPreview.children('.dockerPreview-icon-container'))
                    clone.children('.advanced').remove()
                    let name = clone.find('.outer > .inner > :first-child').text()
                    let idElement = clone.find('.outer > .hand')
                    let id = idElement.attr('id')
                    idElement.removeAttr('id')
                    idElement.addClass(`docker-preview-id-${id}`)
                    if (folder['properties']['docker_preview_icon_show_log']) {
                        let log = $(this).find('td:last-child > .log').clone(true)
                            log.children('img').removeClass('basic').removeAttr('style')
                            log.children('.advanced').remove()
                        clone.find('.outer > .inner').append(log)
                    }
                    if (folder['properties']['docker_preview_icon_show_webui']) {
                        let webui = getDockerWebUI(folder.options['ids'][name])
                        if (webui) {
                            clone.find('.outer > .inner').append(`<a href="${webui}" target="_blank" style="color: initial;"><i class="fa fa-fw fa-globe fa-lg" style="opacity: 0.6; transform: translateY(1.2px);"></i></a>`)
                        }
                    }
                let width = clone.width()
                widthTotal += width

                if (widthTotal >= dockerPreviewWidth) {
                    clone.remove()
                    return false
                } else {
                    clone.prev().children('.outer').append('<div class="border-right">')
                }

                // make text orange on update
                updateText(folderId, id, clone, 'icon')

                // add context menu
                idElement.click(function(e) {
                    showContextMenu(e, id, folder)
                })
            })

            // grayscale
            if (folder['properties']['docker_preview_icon_grayscale']) {
                dockerPreview.children('.dockerPreview-icon-container').find('img').addClass('dockerPreview-grayscale')
            }

            hoverOnly(folderId, folderType)
            break;
    }

    function hoverOnly(folderId, folderType) {
        if (folder['properties']['docker_preview_hover_only']) {
            $(`.${folderType}-folder-parent-${folderId} > .dockerPreview > :first-child`).css('visibility', 'hidden').fadeTo(0, 0)

            $(`.${folderType}-folder-parent-${folderId} > .dockerPreview`).hover(
                function() {
                    $(this).children(':first-child').css('visibility', 'initial').fadeTo(500, 1)
                }, function() {
                    $(this).children(':first-child').fadeTo(500, 0, function(){
                        $(this).css('visibility', 'hidden')
                    })
                }
            );
        }
    }

    function updateText(folderId, id, element, type) {
        if (folder['properties']['docker_preview_text_update_color']) {
            waitForGlobal('docker', function() {
                for (const dock of docker) {
                    if (id === dock['id']) {
                        if (dock['update'] === 1) {
                            if (type === 'icon') {
                                let appname = element.find('.outer > .inner > .appname')
                                let appnameChild = appname.children('a')

                                if (appnameChild.length > 0) {
                                    appnameChild.addClass('orange-text')
                                } else {
                                    appname.addClass('orange-text')
                                }
                            } else {
                                element.children('span').addClass('orange-text')
                            }
                            break
                        }
                    }
                }
            })
        }
    }
}

function iconStyle(folder) {
    const folderId = folder.id

    switch (folder['properties']['docker_icon_style']) {
        case 'label-tab':
            // blue text / bigger text size
            folder.parent().find('> td:first-child .appname').addClass('blue-text').css({
                'font-size': 'larger',
                'font-weight': 'bold'
            })

            folder.parent().addClass('label-tab')
            break;
    }
}

function addFolder(type) {
  var path = location.pathname;
  var x = path.indexOf('?');
  if (x!=-1) path = path.substring(0,x);
  location = `${path}/AddFolder?type=${type}`;
}
</script>