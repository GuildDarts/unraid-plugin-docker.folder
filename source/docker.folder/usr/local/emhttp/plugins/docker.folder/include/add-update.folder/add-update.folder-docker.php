<?php
$folderFile = 'folders';

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once("$docroot/plugins/docker.folder/include/add-update.folder/add-update.common.php");
require_once("$docroot/plugins/docker.folder/include/add-update.folder/popup-docker.php");
require_once("$docroot/plugins/docker.folder/include/common-docker.php");

$containers = $DockerClient->getDockerContainers();
$info = $DockerTemplates->getAllInfo();

$dockerSettings = "<div class='containers'>";

foreach ($containers as $ct) {
  $name = $ct['Name'];

  $img = $info[$name]['icon'];
  if ($img == null) {
    $img = "/plugins/dynamix.docker.manager/images/question.png";
  }

  $repository = ($index === false) ? "Unknown" : $ct['Image'];
  $id = ($index === false) ? "Unknown" : $ct['Id'];
  $dockerSettings .= "<div class='container_item'>";
  $dockerSettings .= "<div class='info'><span><img class='docker_img' src='" . $img . "'></span>";

  $dockerSettings .= "<span><strong>$name</strong><br>$repository";

  $dockerSettings .= "</span></div>";

  $dockerSettings .= "<div class='settingC-box'><input class='settingC' type='checkbox' name='$name'></div>";

  $dockerSettings .= "</div>";
}
$dockerSettings .= "</div>";
?>



<div id="docker_tabbed" style="float:right;margin-top:-55px"></div>
<div id="docker_menus" style="display:flex;float:right;margin-top:-37px"></div>
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
                      $('#setting-docker_preview_text_update_color').hide()
                      $('#setting-docker_preview_icon_grayscale').hide()
                      $('#setting-docker_preview_icon_show_log').hide()
                      $('#setting-docker_preview_icon_show_webui').hide()
                      $('#setting-docker_preview_advanced_context_menu').hide()
                      break;

                    case 'icon':
                      $('#setting-docker_preview_hover_only').show()
                      $('#setting-docker_preview_text_update_color').show()
                      $('#setting-docker_preview_icon_grayscale').show()
                      $('#setting-docker_preview_icon_show_log').show()
                      $('#setting-docker_preview_icon_show_webui').show()
                      $('#setting-docker_preview_advanced_context_menu').show()
                      break;

                    case 'no-icon':
                      $('#setting-docker_preview_hover_only').show()
                      $('#setting-docker_preview_text_update_color').show()
                      $('#setting-docker_preview_icon_grayscale').hide()
                      $('#setting-docker_preview_icon_show_log').hide()
                      $('#setting-docker_preview_icon_show_webui').hide()
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

        <div id="setting-docker_preview_text_update_color" style="display: none;">
          <dl>
            <dt class="fake-list-item">Make text orange on update:</dt>
            <dd>
              <input class="basic-switch setting" name="docker_preview_text_update_color" type="checkbox" checked/>
            </dd>
          </dl>
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

        <div id="setting-docker_preview_icon_show_webui" style="display: none;">
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

<script>
  let url = new URLSearchParams(window.location.search)
  editFolderName = url.get("folderName")

  init()

  async function init() {
    dockerFolders = await read_folders('folders')
    folders = await dockerFolders['folders']
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

          case "docker_preview_text_update_color":
            $(this).prop('checked', folders[editFolderName]['docker_preview_text_update_color'])
            break;

          case "docker_preview_icon_grayscale":
            $(this).prop('checked', folders[editFolderName]['docker_preview_icon_grayscale'])
            break;

          case "docker_preview_icon_show_log":
            $(this).prop('checked', folders[editFolderName]['docker_preview_icon_show_log'])
            break;

          case "docker_preview_icon_show_webui":
            $(this).prop('checked', folders[editFolderName]['docker_preview_icon_show_webui'])
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

  }

  // add event listen for form submit
  $('#form').submit(function() {
    submit(dockerOptions)
  })
</script>