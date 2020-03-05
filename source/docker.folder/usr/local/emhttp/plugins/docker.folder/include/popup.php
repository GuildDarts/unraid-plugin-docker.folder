<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/plugins/dynamix.docker.manager/include/DockerClient.php";
require_once "$docroot/plugins/dynamix.docker.manager/include/common.php";
require_once "$docroot/webGui/include/common.php";
?>

<link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.ui.css">
<link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.switchbutton.css">
<link type="text/css" rel="stylesheet" href="/webGui/styles/jquery.filetree.css">
<link rel="stylesheet" type="text/css" href="/plugins/dynamix.docker.manager/styles/style-{$display['theme']}.css">

<div id="templatePopupConfig" style="display:none">
  <dl>
    <dt>Name:</dt>
    <dd><input type="text" name="Name"></dd>
    <div id="Target">
      <dt id="dt1">Target:</dt>
      <dd><input type="text" name="Target"></dd>
    </div>
    <div id="Value">
      <dt id="dt2">Value:</dt>
      <dd><input type="text" name="Value"></dd>
    </div>
    <div id="Default" class="advanced">
      <dt>Default Value:</dt>
      <dd><input type="text" name="Default"></dd>
    </div>
    <div id="Mode"></div>
    <dt>Description:</dt>
    <dd>
      <textarea name="Description" rows="6" style="width:304px;"></textarea>
    </dd>
    <div class="advanced">
      <dt>Display:</dt>
      <dd>
        <select name="Display">
          <option value="always" selected>Always</option>
          <option value="always-hide">Always - Hide Buttons</option>
          <option value="advanced">Advanced</option>
          <option value="advanced-hide">Advanced - Hide Buttons</option>
        </select>
      </dd>
      <dt>Required:</dt>
      <dd>
        <select name="Required">
          <option value="false" selected>No</option>
          <option value="true">Yes</option>
        </select>
      </dd>
      <div id="Mask">
        <dt>Password Mask:</dt>
        <dd>
          <select name="Mask">
            <option value="false" selected>No</option>
            <option value="true">Yes</option>
          </select>
        </dd>
      </div>
    </div>
  </dl>
</div>




<div id="templateDisplayConfig" style="display:none">
  <input type="hidden" name="confName[]" value="{0}">
  <input type="hidden" name="confTarget[]" value="{1}">
  <input type="hidden" name="confDefault[]" value="{2}">
  <input type="hidden" name="confMode[]" value="{3}">
  <input type="hidden" name="confDescription[]" value="{4}">
  <input type="hidden" name="confType[]" value="{5}">
  <input type="hidden" name="confDisplay[]" value="{6}">
  <input type="hidden" name="confRequired[]" value="{7}">
  <input type="hidden" name="confMask[]" value="{8}">
  <table class="settings">
    <tr>
      <td class="{11}" style="vertical-align:top;">{0}:</td>
      <td>
        <input type="text" name="confValue[]" default="{2}" value="{9}" autocomplete="off" {11}>&nbsp;{10}
        <div class="orange-text">{4}</div>
      </td>
    </tr>
  </table>
</div>




<script src="/webGui/javascript/jquery.switchbutton.js"></script>

<script>



    String.prototype.format = function() {
      var args = arguments;
      return this.replace(/{(\d+)}/g, function(match, number) {
        return typeof args[number] != 'undefined' ? args[number] : match;
      });
    };
    function escapeQuote(string) {
    return string.replace(new RegExp('"','g'),"&quot;");
  }


var confNum = 0

function addConfigPopup() {
    

    var title = 'Add Configuration';
    var popup = $( "#dialogAddConfig" );

    // Load popup the popup with the template info
    popup.html($("#templatePopupConfig").html());

    // Add switchButton to checkboxes
    popup.find(".switch").switchButton({labels_placement:"right",on_label:'YES',off_label:'NO'});
    popup.find(".switch-button-background").css("margin-top", "6px");

    // Load Mode field if needed and enable field
    toggleMode(popup.find("*[name=Type]:first"),false);

    // Start Dialog section
    popup.dialog({
      title: title,
      resizable: false,
      width: 900,
      modal: true,
      show : {effect: 'fade' , duration: 250},
      hide : {effect: 'fade' , duration: 250},
      buttons: {
        "Add": function() {
          $(this).dialog("close");
          confNum += 1;
          var Opts = Object;
          var Element = this;
          ["Name","Target","Default","Mode","Description","Type","Display","Required","Mask","Value"].forEach(function(e){
            Opts[e] = getVal(Element, e);
          });
          if (! Opts.Name ){
            Opts.Name = makeName(Opts.Type);
          }
          if (! Opts.Description ) {
            Opts.Description = "Container "+Opts.Type+": "+Opts.Target;
          }
          if (Opts.Required == "true") {
            Opts.Buttons  = "<span class='advanced'><button type='button' onclick='editConfigPopup("+confNum+",false)'>Edit</button>";
            Opts.Buttons += "<button type='button' onclick='removeConfig("+confNum+")'>Remove</button></span>";
          } else {
            Opts.Buttons  = "<button type='button' onclick='editConfigPopup("+confNum+",false)'>Edit</button>";
            Opts.Buttons += "<button type='button' onclick='removeConfig("+confNum+")'>Remove</button>";
          }
          Opts.Number = confNum;
          newConf = makeConfig(Opts);
          $("#configLocation").append(newConf);
          reloadTriggers();
          $('input[name="contName"]').trigger('change'); // signal change
        },
        Cancel: function() {
          $(this).dialog("close");
        }
      }
    });
    $(".ui-dialog .ui-dialog-titlebar").addClass('menu');
    $(".ui-dialog .ui-dialog-title").css('text-align','center').css( 'width', "100%");
    $(".ui-dialog .ui-dialog-content").css('padding-top','15px').css('vertical-align','bottom');
    $(".ui-button-text").css('padding','0px 5px');
  }



  function toggleMode(el,disabled) {
    var mode       = $(el).parent().siblings('#Mode');
    var valueDiv   = $(el).parent().siblings('#Value');
    var defaultDiv = $(el).parent().siblings('#Default');
    var targetDiv  = $(el).parent().siblings('#Target');

    var value      = valueDiv.find('input[name=Value]');
    var target     = targetDiv.find('input[name=Target]');
    var driver     = "dog";

    value.unbind();
    target.unbind();

    valueDiv.css('display', '');
    defaultDiv.css('display', '');
    targetDiv.css('display', '');
    mode.html('');

    $(el).prop('disabled',disabled);
    switch ($(el)[0].selectedIndex) {
    case 0: // Path
      mode.html("<dt>Access Mode:</dt><dd><select name='Mode'><option value='rw'>Read/Write</option><option value='rw,slave'>RW/Slave</option><option value='rw,shared'>RW/Shared</option><option value='ro'>Read Only</option><option value='ro,slave'>RO/Slave</option><option value='ro,shared'>RO/Shared</option></select></dd>");
      value.bind("click", function(){openFileBrowser(this,$(this).val(), 'sh', true, false);});
      targetDiv.find('#dt1').text('Container Path:');
      valueDiv.find('#dt2').text('Host Path:');
      break;
    case 1: // Port
      mode.html("<dt>Connection Type:</dt><dd><select name='Mode'><option value='tcp'>TCP</option><option value='udp'>UDP</option></select></dd>");
      value.addClass("numbersOnly");
      if (driver=='bridge') {
        if (target.val()) target.prop('disabled',<?=$disableEdit?>); else target.addClass("numbersOnly");
        targetDiv.find('#dt1').text('Container Port:');
        targetDiv.show();
      } else {
        targetDiv.hide();
      }
      if (driver!='null') {
        valueDiv.find('#dt2').text('Host Port:');
        valueDiv.show();
      } else {
        valueDiv.hide();
        mode.html('');
      }
      break;
    case 2: // Variable
      targetDiv.find('#dt1').text('Key:');
      valueDiv.find('#dt2').text('Value:');
      break;
    case 3: // Label
      targetDiv.find('#dt1').text('Key:');
      valueDiv.find('#dt2').text('Value:');
      break;
    case 4: // Device
      targetDiv.hide();
      defaultDiv.hide();
      valueDiv.find('#dt2').text('Value:');
      value.bind("click", function(){openFileBrowser(this,$(this).val()||'/dev', '', true, true);});
      break;
    }
    reloadTriggers();
  }

  function reloadTriggers() {
    $(".basic").toggle(!$(".advanced-switch:first").is(":checked"));
    $(".advanced").toggle($(".advanced-switch:first").is(":checked"));
    $(".numbersOnly").keypress(function(e){if(e.which != 45 && e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)){return false;}});
  }

  


  function makeConfig(opts) {
    confNum += 1;
    var newConfig = $("#templateDisplayConfig").html();
    newConfig = newConfig.format(opts.Name,
                                 opts.Target,
                                 opts.Default,
                                 opts.Mode,
                                 opts.Description,
                                 opts.Type,
                                 opts.Display,
                                 opts.Required,
                                 opts.Mask,
                                 escapeQuote(opts.Value),
                                 opts.Buttons,
                                 (opts.Required == "true") ? "required" : ""
                                );
    newConfig = "<div id='ConfigNum"+opts.Number+"' class='config_"+opts.Display+"'' >"+newConfig+"</div>";
    newConfig = $($.parseHTML(newConfig));
    value     = newConfig.find("input[name='confValue[]']");
    if (opts.Type == "Path") {
      value.attr("onclick", "openFileBrowser(this,$(this).val(),'',true,false);");
    } else if (opts.Type == "Device") {
      value.attr("onclick", "openFileBrowser(this,$(this).val()||'/dev','',false,true);")
    } else if (opts.Type == "Variable" && opts.Default.split("|").length > 1) {
      var valueOpts = opts.Default.split("|");
      var newValue = "<select name='confValue[]' class='selectVariable' default='"+valueOpts[0]+"'>";
      for (var i = 0; i < valueOpts.length; i++) {
        newValue += "<option value='"+valueOpts[i]+"' "+(opts.Value == valueOpts[i] ? "selected" : "")+">"+valueOpts[i]+"</option>";
      }
      newValue += "</select>";
      value.replaceWith(newValue);
    } else if (opts.Type == "Port") {
      value.addClass("numbersOnly");
    }
    if (opts.Mask == "true") {
      value.prop("type", "password");
    }
    console.log(newConfig)
    return newConfig.prop('outerHTML');
  }


</script>