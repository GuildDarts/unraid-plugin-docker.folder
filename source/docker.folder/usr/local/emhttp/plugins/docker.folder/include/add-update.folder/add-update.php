<?php
$folderType = $_GET['type'];
$folderFile = ($folderType !== 'vm') ? 'folders' : 'folders-vm';

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once("$docroot/plugins/docker.folder/include/common.php");

require_once("$docroot/plugins/docker.folder/include/add-update.folder/popup-common.php");
require_once("$docroot/plugins/docker.folder/include/add-update.folder/import-export.php");
require_once("$docroot/plugins/docker.folder/include/add-update.folder/global-settings.php");

require_once("$docroot/plugins/docker.folder/include/add-update.folder/add-update.folder-$folderType.php");
?>

<head>
  <style>
    dl {
      padding-left: 0px;
    }

    .containers, .folders {
      display: grid;
      grid-template-columns: auto auto auto;
    }

    .container_item, .folder_item {
      border: 1px solid rgba(0, 0, 0, 0.8);
    }

    .settingC-box {
      float: right;
      transform: translateY(20px);
    }

    .info {
      display: inline;
      line-height: initial;
    }

    .info > span:last-child {
      display: inline-block;
      max-width: 330px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .docker_img, .folder_img {
      float: left;
      width: 48px;
      height: 48px;
      padding-right: 5px;
    }

    .disabled {
      background-color: rgb(0, 0, 0, 0.3);
      filter: grayscale(70%);
    }

    .checked {
      background-color: rgb(0, 200, 30, 0.3);
    }

    #icon-upload {
      display: none;
    }

    #icon-upload-label {
      cursor: pointer;
      left: -54px;
      position: relative;
    }

    #icon-upload-preview {
      height: 44px;
      width: 44px;
      left: -48px;
      position: relative;
      cursor: pointer;
    }

    #icon-upload-input {
      left: -48px;
      position: relative;
    }

    .fa-icon {
      left: -20px;
      position: relative;
    }

    .fa-input {
      position: relative;
    }

    .type-info {
      position: relative;
      top: -7px;
      left: 22px;
      font-size: small;
    }

    .image_picker_image {
      height: 44px;
      width: 44px;
    }

    .fake-list-item {
      display: list-item;
      margin-left: 30px;
    }
    .fake-list-item + dd {
      margin-left: 50%;
      transform: translateX(-30%);
    }
    .fake-sub-list-item {
      margin-left: 45px;
      list-style: circle;
    }
  </style>

  <link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.switchbutton.css">
  <script src="/plugins/docker.folder/include/jquery.switchbutton-latest.js"></script>
  <script src="/plugins/dynamix.vm.manager/javascript/dynamix.vm.manager.js"></script>
</head>

<body>
    <div id="docker_tabbed" style="float:right;margin-top:-55px"></div>
    <div id="docker_menus" style="display:flex;float:right;margin-top:-37px"></div>
    <div>
        <form id="form" onsubmit="return false">
            <dl style="padding-left: 12px;">
            <div>
                <dl>
                <dt>Name:</dt>
                <dd><input class="setting" type="text" name="name" title="NOW with unicode 🎂" required></dd>
                </dl>
            </div>

            <div>
                <dl>
                <dt>Icon:</dt>
                <dd>
                    <img id="icon-upload-preview" src="/plugins/dynamix.docker.manager/images/question.png" onclick="iconPickerPopup()">
                    <input id="icon-upload-input" class="setting" type="text" name="icon">
                    <label id="icon-upload-label" for="icon-upload" class="fa fa-upload fa-lg" aria-hidden="true">
                    <input id="icon-upload" type="file" onchange="iconEncodeImageFileAsURL(this)" />
                    </label>
                </dd>
                </dl>
                <blockquote class="inline_help">
                <p>You can click the folder icon for a menu of icons for the containers/vms currently inside the folder</p>
                </blockquote>
            </div>

            <div class="advanced" style="display: none">
                <div>
                <dl>
                    <dt>Container/VM preview:</dt>
                    <dd>
                    <select class="setting" name="docker_preview" onchange="dockerPreview_change(this)">
                        <option value="none">None (Default)</option>
                        <option value="icon-label">Icon Label</option>
                        <option value="icon-basic">Icon Basic</option>
                        <option value="no-icon">No icon</option>
                    </select>
                    <script>
                        function dockerPreview_change(e) {
                            if (typeof e === 'string') {
                                var val = e
                            } else {
                                var val = $(e).val()
                            }

                            $('[id*="setting-docker_preview_"]').each(function() {
                                let preview = $(this).attr('preview')
                                if (preview != null) {
                                    if (preview.includes(val)) {
                                        $(this).show()
                                    } else {
                                        $(this).hide()
                                    }
                                }
                            })
                        }
                    </script>
                    </dd>
                </dl>
                </div>

                <div id="setting-docker_preview_hover_only" preview="icon-label icon-basic no-icon" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Only show preview on hover:</dt>
                    <dd>
                    <input class="basic-switch setting" name="docker_preview_hover_only" type="checkbox" />
                    </dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will make the preview only show when mouse is hovering over</p>
                </blockquote>
                </div>

                <div id="setting-docker_preview_text_update_color" preview="icon-label icon-basic no-icon" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Make text orange on update:</dt>
                    <dd>
                    <input class="basic-switch setting" name="docker_preview_text_update_color" type="checkbox" checked/>
                    </dd>
                </dl>
                </div>

                <div id="setting-docker_preview_icon_grayscale" preview="icon-label icon-basic" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Preview icons grayscaled:</dt>
                    <dd>
                    <input class="basic-switch setting" name="docker_preview_icon_grayscale" type="checkbox" />
                    </dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will make the preview icons grayscaled</p>
                </blockquote>
                </div>

                <div id="setting-docker_preview_icon_show_log" preview="icon-label icon-basic" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Add show log icon:</dt>
                    <dd>
                    <input class="basic-switch setting" name="docker_preview_icon_show_log" type="checkbox" checked/>
                    </dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will add a little log icon that opens the log menu</p>
                </blockquote>
                </div>

                <div id="setting-docker_preview_icon_show_webui" preview="icon-label icon-basic" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Add show webUI icon:</dt>
                    <dd>
                    <input class="basic-switch setting" name="docker_preview_icon_show_webui" type="checkbox" checked/>
                    </dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will add a little globe icon that opens the webUI</p>
                </blockquote>
                </div>

                <div id="setting-docker_preview_no_icon_row_count" preview="no-icon" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Preview max row count: (default 12)</dt>
                    <dd>
                    <input class="setting" name="docker_preview_no_icon_row_count" type="number" min="0" step="1" value="12"/>
                    </dd>
                </dl>
                </div>

                <div id="setting-docker_preview_no_icon_column_count" preview="no-icon" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Preview column count: (default 2)</dt>
                    <dd>
                    <input class="setting" name="docker_preview_no_icon_column_count" type="number" min="0" step="1" value="2"/>
                    </dd>
                </dl>
                </div>

                <?php if($folderType === 'docker'): ?>
                <div id="setting-docker_preview_advanced_context_menu" preview="icon-label icon-basic no-icon" style="display: none;">
                <dl>
                    <dt class="fake-list-item">Preview advanced context menu:</dt>
                    <dd>
                    <input class="basic-switch setting" name="docker_preview_advanced_context_menu" type="checkbox" onchange="advancedContext_change(this)"/>
                    <script>
                        function advancedContext_change(e) {
                        if (typeof e === 'boolean') {
                            var val = e
                        } else {
                            var val = $(e).prop('checked')
                        }

                        if (val) {
                            $('#setting-docker_preview_advanced_context_menu_activation_mode').show()
                            $('#setting-docker_preview_advanced_context_menu_graph_mode').show()
                        } else {
                            $('#setting-docker_preview_advanced_context_menu_activation_mode').hide()
                            $('#setting-docker_preview_advanced_context_menu_graph_mode').hide()
                        }
                        }
                    </script>
                    </dd>
                </dl>
                <blockquote class="inline_help">
                <p>Adds extra info to the context menu, so you never have to open a folder again :P</p>
                </blockquote>
                </div>

                <div id="setting-docker_preview_advanced_context_menu_activation_mode" style="display: none;">
                <dl>
                    <dt class="fake-list-item fake-sub-list-item">Activation mode:</dt>
                    <dd>
                    <select class="setting" name="docker_preview_advanced_context_menu_activation_mode">
                        <option value="click">Click (Default)</option>
                        <option value="hover">Hover</option>
                    </select>
                    </dd>
                </dl>
                </div>

                <div id="setting-docker_preview_advanced_context_menu_graph_mode" style="display: none;">
                <dl>
                    <dt class="fake-list-item fake-sub-list-item">Graph mode:</dt>
                    <dd>
                    <select class="setting" name="docker_preview_advanced_context_menu_graph_mode">
                        <option value="none">None (Default)</option>
                        <option value="split">Split</option>
                        <option value="combined">Combined</option>
                    </select>
                    </dd>
                </dl>
                <blockquote class="inline_help">
                <p>None: disabels graphs</p>
                <p>Split: 2 separate graphs</p>
                <p>Combined: 1 graph</p>
                </blockquote>
                </div>
                <?php endif; ?>

            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Icon style:</dt>
                <dd>
                    <select class="setting" name="docker_icon_style">
                    <option value="label-tab">Label Tab (Default)</option>
                    <option value="docker">Docker</option>
                    </select>
                </dd>
                </dl>
            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Expand button style:</dt>
                <dd>
                    <select class="setting" name="docker_expanded_style">
                    <option value="right">Right (Default)</option>
                    <option value="bottom">Bottom</option>
                    </select>
                </dd>
                </dl>
            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Start expanded on Docker/VM page:</dt>
                <dd><input class="basic-switch setting" name="docker_start_expanded" type="checkbox" /></dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will make the folder start expanded on the docker/vm tab</p>
                </blockquote>
            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Start expanded on Dashboard:</dt>
                <dd><input class="basic-switch setting" name="dashboard_expanded" type="checkbox" /></dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will make the folder start expanded on the docker/vm dashboard</p>
                </blockquote>
            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Expanded on Dashboard button:</dt>
                <dd><input class="basic-switch setting" name="dashboard_expanded_button" type="checkbox" /></dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will add a button to expanded the folder on the dashboard</p>
                </blockquote>
            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Only animate icon on hover:</dt>
                <dd><input class="basic-switch setting" name="icon_animate_hover" type="checkbox" /></dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Will pause animating GIF/SVG and play them when hovering over any part of the folder</p>
                    <p>SVG will only work if it's set to always animate</p>
                </blockquote>
            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Status icon autostart:</dt>
                <dd><input class="basic-switch setting" name="status_icon_autostart" type="checkbox" /></dd>
                </dl>
                <blockquote class="inline_help">
                    <p><i class="fa fa-play green-text"></i> The green play icon will show when all containers/vms inside the folder that are set to <strong>autostart</strong> are running</p>
                </blockquote>
            </div>

            <div class="advanced" style="display: none">
                <dl>
                <dt>Regex:</dt>
                <dd><input class="setting" name="regex" type="text" /></dd>
                </dl>
                <blockquote class="inline_help">
                    <p>Any container name the regex matches will be added to folder</p>
                    <p>Example for adding pterodactyl egg: \b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b</p>
                </blockquote>
            </div>

            <div id="dialogAddConfig" style="display:none"></div>
            <div id="buttonLocation"></div>

            <table class="settings">
                <tr>
                <td></td>
                <td><a href="javascript:addConfigPopup()"><i class="fa fa-plus"></i> Add another Button</a></td>
                </tr>
            </table>
            <table class="settings">
                <tr>
                <td></td>
                <td><a href="javascript:addDivider()"><i class="fa fa-plus"></i> Add another Divider</a></td>
                </tr>
            </table>

            <div id="dockers">
                <?= $folderChildren ?>
            </div>
            <br>

            <table class="settings">
                <tr>
                <td></td>
                <td>
                    <input type="submit" value="Submit">
                </td>
                </tr>
            </table>
            <br><br><br>
            </dl>
        </form>
    </div>
</body>


<script>
    let url = new URLSearchParams(window.location.search)
    editFolderId = url.get('folderId')

    init()

    async function init() {
        dockerFolders = await read_folders('folders')
        folders = await dockerFolders['folders']
        let folderIds = Object.keys(await folders)

        $('.settingC').each(function() {
            for (const folderId of folderIds) {
                let folderChild = folders[folderId]['children']
                for (const child of folderChild) {
                    if ($(this).attr('name') == child && folderId !== editFolderId) {
                        $(this).parent().parent().addClass("disabled")
                    } else if (folderId == editFolderId && $(this).attr('name') == child) {
                        $(this).prop("checked", true)
                        $(this).parent().parent().addClass("checked")
                    }
                }
            }
            // add switch button
            $(this).switchButton({
                show_labels: false
            });
        });

        if (editFolderId !== null) {
            $('.setting').each(function() {
                let elementName = $(this).attr('name')
                let folderSetting = folders[editFolderId][elementName]
                if (typeof(folderSetting) === 'boolean') {
                    $(this).prop('checked', folderSetting)
                } else {
                    $(this).val(folderSetting).trigger('input').trigger('change')
                }
            })

            // buttons sortable
            var sortableHelper = function(e, i) {
                i.children().each(function() {
                    $(this).width($(this).width());
                });
                return i;
            };

            $('#buttonLocation').sortable({
                helper: sortableHelper,
                items: 'div.sortable',
                cursor: 'move',
                axis: 'y',
                containment: 'parent',
                cancel: 'span.docker_readmore,input',
                delay: 100,
                opacity: 0.5,
                zIndex: 9999
            })

            $('.basic-switch').switchButton({
                show_labels: false
            });

            loadButtons(folders, editFolderId)

        }

        // add what folder a docker is in
        $('#dockers > .containers').children().each(function() {
            let name = $(this).find('.info > span:last-child > strong').text()
            $(this).find('.info > span:last-child').append(`<br><span class="current-folder">Folder: None</span>`)
            mainLoop:
            for (const folderId of folderIds) {
                let folderChildren = folders[folderId]['children']
                for (const child of folderChildren) {
                if (child === name) {
                    let folderName = folders[folderId]['name']
                    $(this).find('.current-folder').text(`Folder: ${folderName}`)
                    break mainLoop
                }
                }
            }
        })
    }

    function getSettings(options) {
    let settings = new Object()

    $(".setting").each(function() {
      var value = $(this).val();
      var name = $(this).attr('name');
      var type = $(this).attr('type')
      if ((typeof value != "string")) {
        var value = "something really went wrong here";
      }
      if ((value == null)) {
        value = " ";
      }
      value = value.trim();

      switch (type) {
        // get true/false for checkbox input
        case 'checkbox':
          value = $(this).prop('checked')
          break;
        case 'number':
          value = parseInt(value)
          break;
      }

      settings[name] = value;
    });



    if (editFolderId == null) {
      var folder_children = new Array();
    } else {
      var folder_children = folders[editFolderId]['children']
    }
    let childrenRemove = []
    $(".settingC").each(function() {
      var value = $(this).prop("checked");
      var name = $(this).attr('name');
      if (value == true && !folder_children.includes(name)) {
        folder_children.push(name)

        // remove docker from old folder e.g docker is in folder but you check it in another folder and save
        if ($(this).parent().parent().hasClass('disabled')) {
          let oldFolder = $(this).parent().parent().find('.current-folder').text().replace('Folder: ', '')
          let remove = {folderId: oldFolder, child: name}
          childrenRemove.push(remove)
        }
      }
      // remove value from array e.g removing a folder
      if (value == false && folder_children.includes(name) == true) {
        folder_children = folder_children.filter(function(elm) {
          return elm != name
        })
      }

    });
    settings["buttons"] = buttonAdd()
    settings["children"] = folder_children;
    return {folderSettings: settings, childrenRemove: childrenRemove};
  }



    function buttonAdd() {
        // want to add popup like for add Label/Port/Path

        var tmp_array = new Array();
        $("#buttonLocation > [id*='ConfigNum-']").each(function() {
        let button = new Object()

        $(this).find("input").each(function() {
            var name = $(this).attr("name").replace("conf", "").replace("[]", "").toLowerCase();
            if ($(this).attr('type') == 'hidden' && $(this).val() !== "") {
            button[name] = $(this).val()
            }
        });

        tmp_array.push(button)
        });

        return tmp_array
    }

    async function submit(options) {
        $('input[type=button]').prop('disabled', true);

        let settings = await getSettings(options)

        console.log(settings)

        let settingsSting = JSON.stringify(await settings['folderSettings'])
        let childrenRemoveString = JSON.stringify(await settings['childrenRemove'])

        $.post("/plugins/docker.folder/scripts/save_folder.php", {
            type: options['type'],
            settings: await settingsSting,
            editFolderId: await editFolderId,
            childrenRemove: await childrenRemoveString
        }, function() {
            //lazy fck
            location.replace(`/${location.href.split("/")[3]}`)
        });
    }

    // event listen for icon input change. Sets preview
    $("#icon-upload-input").on("input", function() {
        if ($(this).val() !== "") {
            $("#icon-upload-preview").attr('src', $(this).val())
        } else {
            $("#icon-upload-preview").attr('src', '/plugins/dynamix.docker.manager/images/question.png')
        }
    });

    function iconEncodeImageFileAsURL(element) {
        var file = element.files[0];
        // 3mb
        if (file.size < 3145728) {
            var reader = new FileReader();
            reader.onloadend = function() {
                $("#icon-upload-input").val(reader.result)
                $("#icon-upload-preview").attr('src', reader.result)
            }
            reader.readAsDataURL(file);
        } else {
            swal({
                title: "Too large",
                text: "images above the 3mb-5mb range cause issues ;(",
                type: "warning"
            })
        }
    }

    // advanced - basic switch
    var this_tab = $('input[name$="tabs"]').length;
    $(function() {
        if ($.cookie('dockerFolder_view_mode') == 'advanced') {
            $('.advanced').show();
            $('.basic').hide();
        }

        var content = "<div class='switch-wrapper'><input type='checkbox' class='advanced-switch'></div>";
        <? if (!$tabbed) : ?>
        $("#docker_tabbed").prepend(content);
        <? else : ?>
        var last = $('input[name$="tabs"]').length;
        var elementId = "normalAdvanced";
        $('.tabs').append("<span id='" + elementId + "' class='status vhshift' style='display:none;'>" + content + "&nbsp;</span>");
        if ($('#tab' + this_tab).is(':checked')) {
            $('#' + elementId).show();
        }
        $('#tab' + this_tab).bind({
            click: function() {
            $('#' + elementId).show();
            }
        });
        for (var x = 1; x <= last; x++)
            if (x != this_tab) $('#tab' + x).bind({
            click: function() {
                $('#' + elementId).hide();
            }
            });
        <? endif; ?>
        $('.advanced-switch').switchButton({
            labels_placement: "left",
            on_label: 'Advanced View',
            off_label: 'Basic View',
            checked: $.cookie('dockerFolder_view_mode') == 'advanced'
        });
        $('.advanced-switch').change(function() {
            var status = $(this).is(':checked');
            $.cookie('dockerFolder_view_mode', $('.advanced-switch').is(':checked') ? 'advanced' : 'basic', {
                expires: 3650
            });
            toggleRows('advanced', status, 'basic');
        });
    });

    // add import - export & global settings buttons
    $(function() {
        $('#docker_menus').append(`
        <div id='global-settings'><button type='button' onclick='globalSettingsPopup("<?= $folderFile?>")'>Global Settings</button></div>
        <div id='import-export'><button type='button' onclick='importExportPopup()'>Import/Export</button></div>
        `)
    })

    // make containers/vms green
    $('.container_item > .settingC-box > input[type="checkbox"]').change(function() {
        if ($(this).prop("checked") == true) {
            $(this).parent().parent().addClass("checked")
        } else {
            $(this).parent().parent().removeClass("checked")
        }
    })

    // add event listen for form submit
    $('#form').submit(function() {
        submit(dockerOptions)
    })
</script>