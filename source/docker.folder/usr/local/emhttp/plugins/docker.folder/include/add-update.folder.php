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
    left: -44px;
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



<?php
require_once("/usr/local/emhttp/plugins/docker.folder/include/common.php");
require_once("/usr/local/emhttp/plugins/dynamix.docker.manager/include/DockerClient.php");

require_once("/usr/local/emhttp/plugins/docker.folder/include/popup.php");
require_once("/usr/local/emhttp/plugins/docker.folder/include/import-export.php");

function searchArray($array, $key, $value)
{
  if (function_exists("array_column") && function_exists("array_search")) {   # faster to use built in if it works
    $result = array_search($value, array_column($array, $key));
  } else {
    $result = false;
    for ($i = 0; $i <= max(array_keys($array)); $i++) {
      if ($array[$i][$key] == $value) {
        $result = $i;
        break;
      }
    }
  }
  return $result;
}


$DockerTemplates = new DockerTemplates();
$info = $DockerTemplates->getAllInfo();
$DockerClient = new DockerClient();
$moreInfo = $DockerClient->getDockerContainers();

$dockerSettings = "<div class='containers'>";
$containerNames = array_keys($info);

foreach ($containerNames as $container) {

  if (endsWith($container, "-folder")) {
    continue;
  }

  $img = $info[$container]['icon'];
  if ($img == null) {
    $img = "/plugins/dynamix.docker.manager/images/question.png";
  }

  $index = searchArray($moreInfo, "Name", $container);
  $repository = ($index === false) ? "Unknown" : $moreInfo[$index]['Image'];
  $id = ($index === false) ? "Unknown" : $moreInfo[$index]['Id'];
  $dockerSettings .= "<div class='container_item'>";
  $dockerSettings .= "<div class='info'><span><img class='docker_img' src='" . $img . "'></span>";

  $dockerSettings .= "<span><strong>$container</strong><br>$repository";

  $dockerSettings .= "</span></div>";

  $dockerSettings .= "<div class='settingC-box'><input class='settingC' type='checkbox' name='$container'></div>";

  $dockerSettings .= "</div>";
}
$dockerSettings .= "</div>";

function endsWith($haystack, $needle)
{
  return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}





?>
<div id="docker_tabbed" style="float:right;margin-top:-55px"></div>
<div>
  <form id="form" onsubmit="return false">
    <dl style="padding-left: 12px;">
      <div>
        <dl>
          <dt>Name:</dt>
          <dd><input class="setting" type="text" name="name" pattern="[A-Za-z0-9_\-.]+" title="Only a-z nubers and ._- sorry" required></dd>
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
          <p>You can click the folder icon for a menu of icons from the dockers currently inside the folder</p>
        </blockquote>
      </div>

      <div class="advanced" style="display: none">
        <div>
          <dl>
            <dt>Docker preview:</dt>
            <dd>
              <select class="setting" name="docker_preview" onchange="dockerPreview_change(this)">
                <option value="none">None (Default)</option>
                <option value="icon">Icon</option>
                <option value="no-icon">No icon</option>
              </select>
              <script>
                function dockerPreview_change(e) {
                  if (typeof e === 'string') {
                    var val = e
                  } else {
                    var val = $(e).val()
                  }

                  switch (val) {
                    case 'none':
                      $('#setting-docker_preview_hover_only').hide()
                      $('#setting-docker_preview_icon_grayscale').hide()
                      $('#setting-docker_preview_icon_show_log').hide()
                      $('#setting-docker_preview_advanced_context_menu').hide()
                      break;

                    case 'icon':
                      $('#setting-docker_preview_hover_only').show()
                      $('#setting-docker_preview_icon_grayscale').show()
                      $('#setting-docker_preview_icon_show_log').show()
                      $('#setting-docker_preview_advanced_context_menu').show()
                      break;

                    case 'no-icon':
                      $('#setting-docker_preview_hover_only').show()
                      $('#setting-docker_preview_icon_grayscale').hide()
                      $('#setting-docker_preview_icon_show_log').hide()
                      $('#setting-docker_preview_advanced_context_menu').show()
                      break;
                  }
                }
              </script>
            </dd>
          </dl>
        </div>

        <div id="setting-docker_preview_hover_only" style="display: none;">
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

        <div id="setting-docker_preview_icon_grayscale" style="display: none;">
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

        <div id="setting-docker_preview_icon_show_log" style="display: none;">
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

        <div id="setting-docker_preview_advanced_context_menu" style="display: none;">
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

      </div>

      <div class="advanced" style="display: none">
        <dl>
          <dt>Docker icon style:</dt>
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
          <dt>Docker expand button style:</dt>
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
          <dt>Start expanded on Docker:</dt>
          <dd><input class="basic-switch setting" name="docker_start_expanded" type="checkbox" /></dd>
        </dl>
        <blockquote class="inline_help">
            <p>Will make the folder start expanded on the docker tab</p>
        </blockquote>
      </div>

      <div class="advanced" style="display: none">
        <dl>
          <dt>Start expanded on Dashboard:</dt>
          <dd><input class="basic-switch setting" name="dashboard_expanded" type="checkbox" /></dd>
        </dl>
        <blockquote class="inline_help">
            <p>Will make the folder start expanded on the docker dashboard</p>
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
            <p><i class="fa fa-play green-text"></i> The green play icon will show when all docker inside the folder that are set to <strong>autostart</strong> are running</p>
        </blockquote>
      </div>

      <div class="advanced" style="display: none">
        <dl>
          <dt>Regex:</dt>
          <dd><input class="setting" name="regex" type="text" /></dd>
        </dl>
        <blockquote class="inline_help">
            <p>Any docker name the regex matches will be added to folder</p>
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
        <?= $dockerSettings ?>
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

<link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.switchbutton.css">
<script src="/plugins/docker.folder/include/jquery.switchbutton-latest.js"></script>
<script src="/plugins/dynamix.vm.manager/javascript/dynamix.vm.manager.js"></script>

<script>
  let url = new URLSearchParams(window.location.search)
  editFolderName = url.get("folderName")

  init()

  async function init() {
    folders = await read_folders()
    let folderNames = Object.keys(await folders)

    $('.settingC').each(function() {
      for (const folderName of folderNames) {
        let folderChild = folders[folderName]['children']
        for (const child of folderChild) {
          if ($(this).attr('name') == child && folderName !== editFolderName) {
            $(this).parent().parent().addClass("disabled")
          } else if (folderName == editFolderName && $(this).attr('name') == child) {
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

    if (editFolderName !== null) {
      $('.setting').each(function() {
        switch ($(this).attr('name')) {
          case "name":
            $(this).val(editFolderName)
            break;

          case "icon":
            var icon = folders[editFolderName]['icon']
            $(this).val(icon)
            if (icon !== "") {
              $('#icon-upload-preview').attr('src', icon)
            }
            break;

          case "docker_preview":
            $(this).val(folders[editFolderName]['docker_preview'])
            dockerPreview_change(folders[editFolderName]['docker_preview'])
            break;

          case "docker_preview_hover_only":
            $(this).prop('checked', folders[editFolderName]['docker_preview_hover_only'])
            break;

          case "docker_preview_icon_grayscale":
            $(this).prop('checked', folders[editFolderName]['docker_preview_icon_grayscale'])
            break;

          case "docker_preview_icon_show_log":
            $(this).prop('checked', folders[editFolderName]['docker_preview_icon_show_log'])
            break;

          case "docker_preview_advanced_context_menu":
            $(this).prop('checked', folders[editFolderName]['docker_preview_advanced_context_menu'])
            advancedContext_change(folders[editFolderName]['docker_preview_advanced_context_menu'])
            break;

          case "docker_preview_advanced_context_menu_activation_mode":
            $(this).val(folders[editFolderName]['docker_preview_advanced_context_menu_activation_mode'])
            break;

          case "docker_preview_advanced_context_menu_graph_mode":
            $(this).val(folders[editFolderName]['docker_preview_advanced_context_menu_graph_mode'])
            break;

          case "docker_icon_style":
            $(this).val(folders[editFolderName]['docker_icon_style'])
            break;

          case "docker_expanded_style":
            $(this).val(folders[editFolderName]['docker_expanded_style'])
            break;

          case "docker_start_expanded":
            $(this).prop('checked', folders[editFolderName]['docker_start_expanded'])
            break;

          case "dashboard_expanded":
            $(this).prop('checked', folders[editFolderName]['dashboard_expanded'])
            break;

          case "icon_animate_hover":
            $(this).prop('checked', folders[editFolderName]['icon_animate_hover'])
            break;

          case "status_icon_autostart":
            $(this).prop('checked', folders[editFolderName]['status_icon_autostart'])
            break;

          case "dashboard_expanded_button":
            $(this).prop('checked', folders[editFolderName]['dashboard_expanded_button'])
            break;

          case "regex":
            $(this).val(folders[editFolderName]['regex'])
            break;
        }
      })

      loadButtons(folders, editFolderName)

    }

    // add what folder a docker is in
    $('#dockers > .containers').children().each(function() {
      let name = $(this).find('.info > span:last-child > strong').text()
      $(this).find('.info > span:last-child').append(`<br><span class="current-folder">Folder: None</span>`)
      mainLoop:
      for (const folderName of folderNames) {
        let folderChildren = folders[folderName]['children']
        for (const child of folderChildren) {
          if (child === name) {
            $(this).find('.current-folder').text(`Folder: ${folderName}`)
            break mainLoop
          }
        }
      }
    })

    //make it green
    $('.container_item > .settingC-box > input[type="checkbox"]').change(function() {
      if ($(this).prop("checked") == true) {
        $(this).parent().parent().addClass("checked")
      } else {
        $(this).parent().parent().removeClass("checked")
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

  }


  function getSettings() {
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

      // get true/false for checkbox input
      if (type == 'checkbox') {
        value = $(this).prop('checked')
      }

      settings[name] = value;
    });



    if (editFolderName == null) {
      var folder_children = new Array();
    } else {
      var folder_children = folders[editFolderName]['children']
    }
    $(".settingC").each(function() {
      var value = $(this).prop("checked");
      var name = $(this).attr('name');
      if (value == true && !folder_children.includes(name)) {
        folder_children.push(name)

        // remove docker from old folder e.g docker is in folder but you check it in another folder and save
        if ($(this).parent().parent().hasClass('disabled')) {
          let oldFolder = $(this).parent().parent().find('.current-folder').text().replace('Folder: ', '')
          $.post("/plugins/docker.folder/scripts/remove_folder_child.php", {folderName: oldFolder, child: name});
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
    return settings;
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

  // add event listen for form submit
  $('#form').submit(function() {
    submit()
  })

  async function submit() {
    $('input[type=button]').prop('disabled', true);

    let settings = await getSettings()

    let dockerIdsKeys = Object.keys(dockerIds)
    for (const docker of dockerIdsKeys) {
      if (settings['name'] === docker) {
        swal({
          title: "Same Name",
          text: "The Folder has the same name as an existing docker \n (note is case sensitive)",
          type: "warning"
        })
        return
      }
    }

    console.log(settings)

    let settingsSting = JSON.stringify(await settings)

    if (settings['name'] !== editFolderName) {
      var rename = editFolderName
    }

    $.post("/plugins/docker.folder/scripts/save_folder.php", {
      settings: await settingsSting,
      rename: await rename
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

  // import - export
  importExport()
</script>