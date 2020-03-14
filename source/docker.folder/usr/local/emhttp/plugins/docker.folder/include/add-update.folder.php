<style>
.containers {
  display: grid;
  grid-template-columns: auto auto auto;
}
.container_item {
  border: 1px solid rgba(0, 0, 0, 0.8);
}
.settingC-box {
  float: right;
}
.info {
  display: inline;
}
.docker_img {
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

#icon-upload
{
  display: none;
}
#icon-upload-label
{
  cursor: pointer;
  left: -54px;
  position: relative;
}
#icon-upload-preview
{
  height: 44px;
  width: 44px;
  left: -44px;
  position: relative;
}
#icon-upload-input
{
  left: -48px;
  position: relative;
}

</style>



<?php
require_once("/usr/local/emhttp/plugins/docker.folder/include/common.php");
require_once("/usr/local/emhttp/plugins/dynamix.docker.manager/include/DockerClient.php");

//require_once("/usr/local/emhttp/plugins/docker.folder/include/popup.php");

function searchArray($array,$key,$value) {
  if ( function_exists("array_column") && function_exists("array_search") ) {   # faster to use built in if it works
    $result = array_search($value, array_column($array, $key));   
  } else {
    $result = false;
    for ($i = 0; $i <= max(array_keys($array)); $i++) {
      if ( $array[$i][$key] == $value ) {
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

    $index = searchArray($moreInfo,"Name",$container);
    $repository = ($index === false) ? "Unknown" : $moreInfo[$index]['Image'];
    $dockerSettings .= "<div class='container_item'>";
    $dockerSettings .= "<div class='info'><img class='docker_img' src='".$img."'>";
    
    $dockerSettings .= "<strong>$container</strong><br>$repository</div>";
    
    $dockerSettings .= "<div class='settingC-box'><input class='settingC' type='checkbox' name='$container'></div>";

    $dockerSettings .= "</div>";

  }
  $dockerSettings .= "</div>";

function endsWith($haystack, $needle) {
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}





?>

<div>
  <blockquote class="inline_help" style="display: block;">
  <p>Some info about the settings (sorry about bad wording)</p>
  <p>If the start/stop/restart/update input is blank the button will not display</p>
  <p>"<strong>Docker_Default</strong>" in the input will make the button run "docker start/stop/etc $container"</p>
  <p>anything else will be run as a normal bash command this can then run E.g docker-compose</p>
  <p><strong>docker-compose example</strong> "docker-compose -f /path/to/docker-compose.yml up/down/etc"</p>
  
  </blockquote>
  <form id="form" onsubmit="return false">
  <dl>
    <dt>Name:</dt>
    <dd><input class="setting" type="text" name="name" pattern="[^\s]+"  title="no spaces please :)" required></dd>

    <dt>Icon:</dt>
    <dd>
      <img id="icon-upload-preview" src="/plugins/dynamix.docker.manager/images/question.png">
      <input id="icon-upload-input" class="setting" type="text" name="icon">
      <label id="icon-upload-label" for="icon-upload" class="fa fa-upload fa-lg" aria-hidden="true">
      <input id="icon-upload" type="file" onchange="iconEncodeImageFileAsURL(this)" />
    </dd>
    
    <!--BUTTONS-->

    <dt>WebUI:</dt>
    <dd><input class="settingB" type="text" name="WebUI" pattern="^https?:\/\/.*"  title="WebUI must start with http/https"></dd>

    <dt>Start:</dt>
    <dd><input class="settingB" type="text" name="Start"></dd>

    <dt>Stop:</dt>
    <dd><input class="settingB" type="text" name="Stop"></dd>

    <dt>Restart:</dt>
    <dd><input class="settingB" type="text" name="Restart"></dd>

    <dt>Update:</dt>
    <dd><input class="settingB" type="text" name="Update"></dd>

    <!--BUTTONS-END-->

    <div id="dialogAddConfig" style="display:none"></div>
    
    <div id="dockers">
        <?=$dockerSettings?>
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
        if ( $(this).attr('name') == child && folderName !== editFolderName) {
          $(this).prop("disabled", true )
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


  for (const folderName of folderNames) {
    if (folderName == editFolderName) {
      $('.setting').each(function() {
        switch ($(this).attr('name')) {
          case "name":
            $(this).val(folderName)
            $(this).attr("disabled", true)
          break;

          case "icon":
            $(this).val(folders[folderName]['icon'])
          break;
        }
      })

      $('.settingB').each(function() {
        let buttons = folders[folderName]['buttons']
        for (const button of buttons) {
          if ( $(this).attr("name") == button['name'] )  {
            $(this).val(button['cmd'])
          }
        }
      })
    }
  }

  //make it green
  $('input[type="checkbox"]').change(function(){
    if($(this).prop("checked") == true){
      $(this).parent().parent().addClass("checked")
    } else {
      $(this).parent().parent().removeClass("checked")
    }
  })
  
}


function getSettings() {
  let settings = new Object()
  
  $(".setting").each(function() {
    var value = $(this).val();
    var name = $(this).attr('name');
    if ( (typeof value != "string" ) ) {
      var value = "something really went wrong here";
    }
    if ( (value == null) ) {
      value = " ";
    }
    value = value.trim();
    
    
    settings[name] = value;
  });
  
  settings["children"] = folder_children;

  var folder_children = new Array();
  $(".settingC").each(function() {
    var value = $(this).prop("checked");
    var name = $(this).attr('name');
    if (value == true) {
      folder_children.push(name)
    }
    
    
  });
  settings["buttons"] = buttonAdd()
  settings["children"] = folder_children;
  return settings;
}



function buttonAdd() {
  // want to add popup like for add Label/Port/Path

  var tmp_array = new Array();
  $(".settingB").each(function() {
    let button = new Object()

    var value = $(this).val();
    var name = $(this).attr('name');
    if ( (typeof value != "string" ) ) {
      var value = "something really went wrong here";
    }
    if ( (value == null) ) {
      value = " ";
    }
    value = value.trim();
    
    let icon
    switch (name) {
      case "WebUI":
        icon = "globe"
      break;

      case "Start":
        icon = "play"
      break;

      case "Stop":
        icon = "stop"
      break;

      case "Restart":
        icon = "refresh"
      break;

      case "Update":
        icon = "arrow-down"
      break;
    }

    button["name"] = name
    button["icon"] = icon
    button["cmd"] = value

    tmp_array.push(button)
  });

  return tmp_array
}

// add event listen for form submit
$('#form').submit(function(){
  submit()
})

async function submit() {
  $('input[type=button]').prop('disabled',true);

  let settings = await getSettings()

  if (editFolderName == null) {
    let dockerId = await createDocker(settings["name"])
    settings["id"] = dockerId
  } else {
    settings["id"] = folders[editFolderName]['id']
  }


  let settingsSting = JSON.stringify(settings)
  $.post( "/plugins/docker.folder/scripts/save_folder.php", { settings:settingsSting } );

  //lazy fck
  location.replace(`/${location.href.split("/")[3]}`)
}

async function createDocker(name) {
  return postResult = await Promise.resolve ($.get( "/plugins/docker.folder/scripts/docker_folder_create.php", { name:name } )
    );
}

// event listen for icon input change. Sets preview
$("#icon-upload-input").on("input",function(){
  $("#icon-upload-preview").attr('src', $(this).val())
});

function iconEncodeImageFileAsURL(element) {
  var file = element.files[0];
  var reader = new FileReader();
  reader.onloadend = function() {
    $("#icon-upload-input").val(reader.result)
    $("#icon-upload-preview").attr('src', reader.result)
  }
  reader.readAsDataURL(file);
}

</script>