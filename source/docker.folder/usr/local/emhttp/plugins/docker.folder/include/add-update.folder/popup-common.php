<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/plugins/dynamix.docker.manager/include/Helpers.php";
require_once "$docroot/webGui/include/Helpers.php";
?>

<head>
  <link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.ui.css">
  <link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.switchbutton.css">
  <link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.filetree.css">
  <link rel="stylesheet" type="text/css" href="<? autov("/plugins/dynamix.docker.manager/styles/style-{$display['theme']}.css") ?>">

  <script src="/webGui/javascript/jquery.switchbutton.js"></script>

  <link type="text/css" rel="stylesheet" href="/plugins/docker.folder/include/fa-icon-picker/simple-iconpicker.css">
  <script src="/plugins/docker.folder/include/fa-icon-picker/simple-iconpicker.js"></script>

  <link type="text/css" rel="stylesheet" href="/plugins/docker.folder/include/image-picker/image-picker.css">
  <script src="/plugins/docker.folder/include/image-picker/image-picker.min.js"></script>

  <style>
    .ui-dialog-titlebar-help {
      position: absolute;
      left: .3em;
      top: 50%;
      width: 20px;
      margin: -10px 0 0 0;
      padding: 1px;
      height: 20px;
      cursor: help;
    }

    #popup-helper > p {
      margin: initial;
    }
  </style>

</head>



<body>
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

<div id="templateIconPicker" style="display:none">
  <select class="image-picker">
  </select>
</div>
</body>


<script>
  var confNum = 0;

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

    // Load popup the popup with the template info
    popup.html($("#templatePopupConfig").html());

    // Load Docker Sub Menu
    loadDockerSubMenu(popup)

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

    // Add little helper question mark
    addHelpIcon(popup)

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

    // Load popup the popup with the template info
    popup.html($("#templatePopupConfig").html());

    // Load Docker Sub Menu
    loadDockerSubMenu(popup)

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

    // Add little helper question mark
    addHelpIcon(popup)

    $(".ui-dialog .ui-dialog-titlebar").addClass('menu');
    $(".ui-dialog .ui-dialog-title").css('text-align', 'center').css('width', "100%");
    $(".ui-dialog .ui-dialog-content").css('padding-top', '15px').css('vertical-align', 'bottom');
    $(".ui-button-text").css('padding', '0px 5px');
    $('.fa-icon-picker').iconpicker(".fa-icon-picker");
  }

  function addHelpIcon(popup) {
    let titlebar = popup.prev()

    if (!$(titlebar).children('.ui-dialog-titlebar-help').length) {
      $(titlebar).prepend(`<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-help"><i class="fa fa-question-circle"></i></button>`)
      $(titlebar).children('.ui-dialog-titlebar-help').click(function() {
        popup.find('#popup-helper').toggle('slow')
      })
    }
  }

  function loadDockerSubMenu(popup) {
    sub_menu = popup.find('#popup-docker-sub-menu > dd > select')
    sub_menu.empty()
    $('div.container_item').each(function() {
      if ($(this).hasClass('checked')) {
        var name = $(this).find('.info > span:last-child > :first-child').text()

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

  function loadButtons(folders, folderId) {
    for (const button of folders[folderId]['buttons']) {
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
    var el = $(el).find("*[name=" + name + "]");
    if (el.length) {
      return ($(el).attr('type') == 'checkbox') ? ($(el).is(':checked') ? "on" : "off") : $(el).val();
    } else {
      return "";
    }
  }



  // icon select
  function iconPickerPopup() {
    var title = 'Select Icon';
    var popup = $("#dialogAddConfig");

    // Load popup the popup with the template info
    popup.html($("#templateIconPicker").html());

    // add images to imagePicker
    $('div.container_item.checked').each(function(i) {
      var img = $(this).find('.docker_img').attr('src')
      if (img !== '/plugins/dynamix.docker.manager/images/question.png') {
        popup.find('select').append(`<option data-img-src="${img}" value="${i}"></option>`)
      }
    })

    // Load imagePicker
    popup.find('.image-picker').imagepicker()

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
        "Pick": function() {
          var img = popup.find('.selected > img').attr('src')
          $('#icon-upload-input').val(img)
          $("#icon-upload-preview").attr('src', img)
          $(this).dialog("close");
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
</script>