<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once("$docroot/plugins/docker.folder/include/common.php");

require_once("$docroot/plugins/docker.folder/include/add-update.folder/add-update.common.php");

require_once("$docroot/plugins/docker.folder/include/add-update.folder/popup-common.php");
require_once("$docroot/plugins/docker.folder/include/add-update.folder/import-export.php");
require_once("$docroot/plugins/docker.folder/include/add-update.folder/global-settings.php");

?>

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

<script>
    $(function() {
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
    })

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

      // get true/false for checkbox input
      if (type == 'checkbox') {
        value = $(this).prop('checked')
      }

      settings[name] = value;
    });



    if (editFolderId == null) {
      var folder_children = new Array();
    } else {
      var folder_children = folders[editFolderId]['children']
    }
    $(".settingC").each(function() {
      var value = $(this).prop("checked");
      var name = $(this).attr('name');
      if (value == true && !folder_children.includes(name)) {
        folder_children.push(name)

        // remove docker from old folder e.g docker is in folder but you check it in another folder and save
        if ($(this).parent().parent().hasClass('disabled')) {
          let oldFolder = $(this).parent().parent().find('.current-folder').text().replace('Folder: ', '')
          $.post("/plugins/docker.folder/scripts/remove_folder_child.php", {type: options['type'], folderId: oldFolder, child: name});
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

  async function submit(options) {
    $('input[type=button]').prop('disabled', true);

    let settings = await getSettings(options)

    console.log(settings)

    let settingsSting = JSON.stringify(await settings)

    $.post("/plugins/docker.folder/scripts/save_folder.php", {
        type: options['type'],
        settings: await settingsSting,
        editFolderId: await editFolderId
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
  
</script>