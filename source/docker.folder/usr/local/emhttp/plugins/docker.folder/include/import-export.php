<?php
$foldersRaw = file_get_contents("/boot/config/plugins/docker.folder/folders.json");
$folders = json_decode($foldersRaw, true);

// remove foldersVersion
unset($folders['foldersVersion']);

$foldersSettings = "<div class='folders'>";

foreach ($folders as $folderKey => &$folder) {

  $img = $folder['icon'];
  if ($img == "") {
    $img = "/plugins/dynamix.docker.manager/images/question.png";
  }

  $foldersSettings .= "<div class='folder_item'>";
  $foldersSettings .= "<div class='info'><img class='folder_img' src='" . $img . "'>";

  $foldersSettings .= "<strong>$folderKey</strong>";

  $foldersSettings .= "<div class='container-id' style='display:none;'>$id</div></div>";

  $foldersSettings .= "<div class='import-export-folder-select-box'><input class='import-export-folder-select switch' type='checkbox' name='$folderKey'></div>";

  $foldersSettings .= "</div>";
}
$foldersSettings .= "</div>";

?>

<div id="templatePopupImportExport" style="display:none">
  <blockquote>
  <p>Info about import/export <b>PLEASE READ</b></p>
  <p>If you import a folder with the same name as an existing folder the existing folder will be overridden</p>
  <p>There is current no check for if and import as the same children as an existing folder. Please be careful as this might cause issues</p>
  </blockquote>
  <br>
  <div><?= $foldersSettings ?></div>
  <div>
    <button type='button' onclick='$("#import-input").click()'>Import</button>
    <input id='import-input' type='file' onchange='Import(this)' style="display: none;">
    <button type='button' onclick='Export("select")'>Export Selection</button>
    <button type='button' onclick='Export("all")'>Export All Folders</button>
  </div>
</div>


<script>
  function Import(element) {
    var files = element.files;
    if (files.length <= 0) {
      return false;
    }

    var fr = new FileReader();

    fr.onload = function(e) {
      var result = JSON.parse(e.target.result);

      if (result['foldersVersion'] == null) {
        swal({
            title: "no foldersVersion",
            text: "looks like there is no foldersVersion in import. Are you sure you still want to import? (might cause issues)",
            type: "warning",
            confirmButtonText: 'Yes',
            showCancelButton: true
          },
          function(inputValue) {
            if (inputValue == true) {
              importDownload(result)
            }
          });
      } else if (result['foldersVersion'] < foldersVersion) {
        var resultString = JSON.stringify(result);
        $.post("/plugins/docker.folder/scripts/migration.php", {
          importFolder: resultString
        }, function() {
          swal({
            title: "Folder Import Done (migration)",
            text: "Import is done, check that everything imported correctly if not please report it on the forums :)",
            type: "success",
            showCancelButton: false
          })
        })
      } else {
        importUpload(result)
      }

      console.log(result)

      function importUpload(result) {
        var resultString = JSON.stringify(result);
        console.log(resultString);
        $.post("/plugins/docker.folder/scripts/import-export/import.php", {
          import: resultString
        }, function() {
          swal({
            title: "Folder Import Done",
            text: "Import is done, check that everything imported correctly if not please report it on the forums :)",
            type: "success",
            showCancelButton: false
          })
        })
      }
    }

    fr.readAsText(files.item(0));
  }

  async function Export(mode) {
    if (mode == "all") {
      var name = "AllFolders"
      var selection = Object.keys(await folders)
    } else {
      var selection = []
      $('.import-export-folder-select').each(function() {
        if ($(this).prop("checked")) {
          selection.push($(this).attr('name'))
        }
      })
      if (selection.length == 0) {
        return
      }
      // name
      if (selection.length == 1) {
        var name = selection[0]
      } else {
        var name = "multipleFolders"
      }
    }

    selectionString = JSON.stringify(selection)

    var exportFolders = await Promise.resolve($.get("/plugins/docker.folder/scripts/import-export/export.php", {
      selection: selectionString
    }));

    console.log(await exportFolders)

    download(name, await exportFolders);
  }



  function importExport() {
    $("#docker_tabbed").append(`<div id='import-export'><button type='button' onclick='importExportPopup()'>Import/Export</button></div>`)
  }


  function importExportPopup() {
    var title = 'Import/Export Folder';
    var popup = $("#dialogAddConfig");

    // Load popup the popup with the template info
    popup.html($("#templatePopupImportExport").html());

    // Add switchButton to checkboxes
    popup.find(".switch").switchButton({
      show_labels: false
    });

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

  function download(name, text) {
    var filename = `docker.folder-${name}-${date()}.json`

    var element = document.createElement('a');
    element.setAttribute('href', 'data:application/json;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
  }

  function date() {
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = today.getFullYear();

    return today = `${yyyy}/${mm}/${dd}`;
  }
</script>