<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/plugins/dynamix.docker.manager/include/Helpers.php";
require_once "$docroot/webGui/include/Helpers.php";
?>

<head>
  <link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.ui.css">
  <link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.switchbutton.css">
  <link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.filetree.css">
  <link rel="stylesheet" type="text/css" href="<?autov("/plugins/dynamix.docker.manager/styles/style-{$display['theme']}.css")?>">

  <script src="/webGui/javascript/jquery.switchbutton.js"></script>

  <link type="text/css" rel="stylesheet" href="/plugins/docker.folder/include/fa-icon-picker/simple-iconpicker.css">
  <script src="/plugins/docker.folder/include/fa-icon-picker/simple-iconpicker.js"></script>
</head>


<div id="templatePopupConfig" style="display:none">
  <dl>
    <dt>Config Type:</dt>
    <dd>
      <select name="Type" onchange="toggleMode(this,false);">
        <option value="WebUI">WebUI</option>
        <option value="Docker_Default">Docker_Default</option>
        <option value="Bash">Bash</option>
        <option value="Docker_Sub_Menu">Docker_Sub_Menu</option>
      </select>
    </dd>
    <div id="popup-name">
      <dt id="dt1">Name:</dt>
      <dd><input type="text" name="Name" required></dd>
    </div>
    <div id="popup-icon">
      <dt id="dt2">Icon:</dt>
      <dd><input class="fa-icon-picker" type="text" name="Icon"></dd>
    </div>
    <div id="popup-cmd">
      <dt id="dt3">CMD:</dt>
      <dd><input type="text" name="CMD"></dd>
    </div>

    <div id="popup-action">
      <dt>Action:</dt>
      <dd>
        <select name="action">
          <option value="start">Start</option>
          <option value="stop">Stop</option>
          <option value="restart">Restart</option>
          <option value="update">Update</option>
          <option value="pause">Pause</option>
          <option value="unpause">Unpause</option>
        </select>
      </dd>
    </div>

    <div id="popup-docker-sub-menu">
      <dt>Docker:</dt>
      <dd>
        <select name="docker_sub_menu">
        </select>
      </dd>
    </div>

  </dl>
</div>




<div id="templateDisplayConfig" style="display:none">
  <input type="hidden" name="confType[]" value="{0}">
  <input type="hidden" name="confName[]" value="{1}">
  <input type="hidden" name="confIcon[]" value="{2}">
  <input type="hidden" name="confCMD[]" value="{3}">
  <dt>{1}:</dt>
  <dd>
    <label class="fa-icon fa fa-{2} fa-lg" aria-hidden="true">
      <input class="fa-input" type="text" default="{3}" value="{3}" name="{1}" disabled>&nbsp;{4}
      <div class="type-info orange-text">{0}</div>
  </dd>
</div>

<div id="templateDividerConfig" style="display:none">
  <input type="hidden" name="confType[]" value="{0}" disabled>
  <input type="hidden" name="confName[]" value="{1}" disabled>
  <input type="hidden" name="confIcon[]" value="{2}" disabled>
  <input type="hidden" name="confCMD[]" value="{3}" disabled>
  <dt>{0}:</dt>
  <dd>
    <input type="text" value="Im Just a Simple Divider" name="{0}" disabled>&nbsp;{4}
  </dd>
</div>


<script>
  var confNum = 0;
  var checkedContainerIds = [];

  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) {
      return typeof args[number] != 'undefined' ? args[number] : match;
    });
  };

  function escapeQuote(string) {
    return string.replace(new RegExp('"', 'g'), "&quot;");
  }


  function addConfigPopup() {
    var title = 'Add Configuration';
    var popup = $("#dialogAddConfig");

    // Load checkedContainerIds
    loadCheckedDockers()

    // Load popup the popup with the template info
    popup.html($("#templatePopupConfig").html());

    // Load Mode field if needed and enable field
    toggleMode(popup.find("*[name=Type]:first"), false);

    // Start Dialog section
    popup.dialog({
      title: title,
      resizable: false,
      width: 900,
      modal: true,
      show: {
        effect: 'fade',
        duration: 250
      },
      hide: {
        effect: 'fade',
        duration: 250
      },
      buttons: {
        "Add": function() {
          confNum += 1;
          $(this).dialog("close");
          var Opts = Object;
          var Element = this;
          ["Type", "Name", "Icon", "CMD"].forEach(function(e) {
            Opts[e] = getVal(Element, e);
          });
          if (!Opts.Name) {
            Opts.Name = makeName(Opts.Type);
          }
          Opts.Buttons = `<button type='button' onclick='editConfigPopup("${confNum}",false)'>Edit</button>`;
          Opts.Buttons += `<button type='button' onclick='removeConfig("${confNum}")'>Remove</button>`;
          Opts.Number = confNum;
          newConf = makeConfig(Opts);
          $("#buttonLocation").append(newConf);
          syncButtons(Opts.Name)
        },
        Cancel: function() {
          $(this).dialog("close");
        }
      }
    });
    $(".ui-dialog .ui-dialog-titlebar").addClass('menu');
    $(".ui-dialog .ui-dialog-title").css('text-align', 'center').css('width', "100%");
    $(".ui-dialog .ui-dialog-content").css('padding-top', '15px').css('vertical-align', 'bottom');
    $(".ui-button-text").css('padding', '0px 5px');
    $('.fa-icon-picker').iconpicker(".fa-icon-picker");
  }

  function editConfigPopup(num, disabled) {
    var title = 'Edit Configuration';
    var popup = $("#dialogAddConfig");
    var index = $(`#ConfigNum-${num}`).index();

    // Load checkedContainerIds
    loadCheckedDockers()

    // Load popup the popup with the template info
    popup.html($("#templatePopupConfig").html());

    // Load existing config info
    var config = $("#ConfigNum-" + num);
    config.find("input").each(function() {
      var name = $(this).attr("name").replace("conf", "").replace("[]", "");
      popup.find("*[name='" + name + "']").val($(this).val());
    });

    // Load Type field if needed
    var type = config.find("input[name='confType[]']").val();
    toggleMode(popup.find("*[name=Type]:first"), disabled);
    popup.find("*[name=Type]:first").val(type);

    // Start Dialog section
    popup.find(".switch-button-background").css("margin-top", "6px");
    popup.dialog({
      title: title,
      resizable: false,
      width: 900,
      modal: true,
      show: {
        effect: 'fade',
        duration: 250
      },
      hide: {
        effect: 'fade',
        duration: 250
      },
      buttons: {
        "Save": function() {
          $(this).dialog("close");
          var Opts = Object;
          var Element = this;
          ["Type", "Name", "Icon", "CMD"].forEach(function(e) {
            Opts[e] = getVal(Element, e);
          });
          Opts.Buttons = `<button type='button' onclick='editConfigPopup("${num}",<?= $disableEdit ?>)'>Edit</button>`;
          Opts.Buttons += `<button type='button' onclick='removeConfig("${num}")'>Remove</button>`;
          if (!Opts.Name) {
            Opts.Name = makeName(Opts.Type);
          }
          Opts.Number = num;
          newConf = makeConfig(Opts);
          config.remove();
          insertAtIndex(newConf, index)
        },
        Cancel: function() {
          $(this).dialog("close");
        }
      }
    });
    $(".ui-dialog .ui-dialog-titlebar").addClass('menu');
    $(".ui-dialog .ui-dialog-title").css('text-align', 'center').css('width', "100%");
    $(".ui-dialog .ui-dialog-content").css('padding-top', '15px').css('vertical-align', 'bottom');
    $(".ui-button-text").css('padding', '0px 5px');
    $('.fa-icon-picker').iconpicker(".fa-icon-picker");
  }

  function toggleMode(el, disabled) {
    var base = $(el).parent().parent();

    var name = base.find("#popup-name")
    var name_input = name.find('input')
    var icon = base.find("#popup-icon")
    var icon_input = icon.find('input')
    var cmd = base.find("#popup-cmd")
    var cmd_input = cmd.find('input')
    var action = base.find("#popup-action")
    var action_input = action.find('select')
    var subMenu = base.find("#popup-docker-sub-menu")
    var subMenu_input = subMenu.find('select')

    name.show()
    icon.hide()
    cmd.show()
    action.hide()
    subMenu.hide()

    switch ($(el)[0].selectedIndex) {
      case 0: // WebUI
        base.find('#dt3').text('URL:');
        icon_input.val('globe')
        break;
      case 1: // Docker_Default
        name.hide()
        icon.hide()
        cmd.hide()
        action.show()

        var iconArray = ['play', 'stop', 'refresh', 'arrow-down', 'pause', 'pause']

        action_input.val(cmd_input.val())
        action_input.change(function() {
          var index = $(this).prop('selectedIndex')
          name_input.val($(this).children("option:selected").text())
          icon_input.val(iconArray[index])
          cmd_input.val($(this).val())
        });

        break;
      case 2: // Bash
        icon.show()
        base.find('#dt3').text('CMD:');
        break;

      case 3: // Docker_Sub_Menu
        name.hide()
        cmd.hide()
        subMenu.show()

        subMenu_input.prop("selectedIndex", -1)

        subMenu_input.change(function() {
          var index = $(this).prop('selectedIndex')
          name_input.val($(this).children("option:selected").text())
          icon_input.val('docker')
          cmd_input.val(checkedContainerIds[index])
        })
        break;
    }
  }



  function loadCheckedDockers() {
    checkedContainerIds = []
    sub_menu = $('#templatePopupConfig').find('#popup-docker-sub-menu > dd > select')
    sub_menu.empty()
    $('#dockers > .containers > .container_item').each(function() {
      if ($(this).hasClass('checked')) {
        var name = $(this).find('.info > strong').text()
        var id = $(this).find('.info > .container-id').text()

        checkedContainerIds.push(id)

        sub_menu.append(`<option value="${name}">${name}</option>`);
      }
    })
  }

  function removeConfig(num) {
    $('#ConfigNum-' + num).fadeOut("fast", function() {
      $(this).remove();
    });
  }

  function addDivider() {
    confNum += 1;
    var Opts = Object;

    Opts.Type = "divider"
    Opts.Name = ""
    Opts.Icon = ""
    Opts.CMD = ""

    Opts.Buttons = `<button type='button' onclick='removeConfig(${confNum})'>Remove</button>`;

    Opts.Number = confNum;
    newConf = makeConfig(Opts, 'divider');
    $("#buttonLocation").append(newConf);
  }

  function insertAtIndex(e, i) {
    if (i === 0) {
      $("#buttonLocation").prepend(e);
      return;
    }
    $("#buttonLocation > div:nth-child(" + (i) + ")").after(e);
  }


  function makeConfig(opts, configType) {
    confNum += 1;
    if (configType == 'divider') {
      var newConfig = $("#templateDividerConfig").html();
    } else {
      var newConfig = $("#templateDisplayConfig").html();
    }
    newConfig = newConfig.format(opts.Type,
      opts.Name,
      opts.Icon,
      opts.CMD,
      opts.Buttons);
    newConfig = "<div class='sortable' id='ConfigNum-" + opts.Number + "'>" + newConfig + "</div>";
    newConfig = $($.parseHTML(newConfig));
    return newConfig.prop('outerHTML');
  }

  function loadButtons(folders, folderName) {
    for (const button of folders[folderName]['buttons']) {
      confNum += 1;
      if (button.type !== 'divider') {
        var Opts = Object;
        Opts.Type = button.type
        Opts.Name = button.name
        Opts.Icon = button.icon
        Opts.CMD = button.cmd

        Opts.Buttons = `<button type='button' onclick='editConfigPopup("${confNum}",<?= $disableEdit ?>)'>Edit</button>`;
        Opts.Buttons += `<button type='button' onclick='removeConfig("${confNum}")'>Remove</button>`;
        Opts.Number = confNum;
        newConf = makeConfig(Opts);
        $("#buttonLocation").append(newConf);
        syncButtons(confNum)
      } else {
        addDivider()
      }
    }
  }

  function syncButtons(num) {
    $(`#ConfigNum-${num}`).find('dd > input').on('input', function() {
      $(`#ConfigNum-${num}`).find('input[name="confCMD[]"]').val($(this).val())
    });
  }

  function getVal(el, name) {
    var el = $(el).find("*[name="+name+"]");
    if (el.length) {
      return ($(el).attr('type') == 'checkbox') ? ($(el).is(':checked') ? "on" : "off") : $(el).val();
    } else {
      return "";
    }
  }
</script>