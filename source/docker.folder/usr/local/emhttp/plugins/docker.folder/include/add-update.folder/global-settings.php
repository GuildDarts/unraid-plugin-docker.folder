<style>
#popupGlobalSettings .switch-button-background{
    transform: translateY(7px);
}
</style>

<div id="templatePopupGlobalSettings-docker" style="display:none">
  <div id="popupGlobalSettings">
    <div>
        <dl>
            <dt>Fix docker page shifting:</dt>
            <dd><input class="globalSetting switch" name="fix_docker_page_shifting" type="checkbox" /></dd>
        </dl>
        <blockquote class="inline_help">
            <p>Will hopefully fix the docker page shifting when opening/closing folders</p>
            <p>This is done by setting static width for some columns before folders are created (might cause issues)</p>
        </blockquote>
    </div>
  </div>
</div>

<div id="templatePopupGlobalSettings-vm" style="display:none">
  <div id="popupGlobalSettings">
    <div>
    </div>
  </div>
</div>

<script>
function globalSettingsPopup(_folderType) {
    let folderType = (_folderType === 'folders-vm') ? 'vm' : 'docker'
    let title = 'Import/Export Folder';
    let popup = $('#dialogAddConfig');

    // Load popup the popup with the template info
    popup.html($(`#templatePopupGlobalSettings-${folderType}`).html());

    // load settings
    popup.find('.globalSetting').each(function() {
        switch ($(this).attr('name')) {
            case 'fix_docker_page_shifting':
                $(this).prop('checked', dockerFolders['settings']['fix_docker_page_shifting'])
                break;
        }
    })

    // Add switchButton to checkboxes
    popup.find('.switch').switchButton({
        show_labels: false
    });

    // fix for blockquote not working
    popup.find('dt').each(function(i, e) {
        if ($(e).attr('style').includes('cursor: help;')) {
            let blockquote = $(e).parent().next()
            $(e).click(function() {
                blockquote.toggle('slow')
            })
        }
    })

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
            Save: function() {
                saveSettings()
                $(this).dialog('close')
            },
            Cancel: function() {
                $(this).dialog('close');
            }
        }
    });

    $('.ui-dialog .ui-dialog-titlebar').addClass('menu');
    $('.ui-dialog .ui-dialog-title').css('text-align', 'center').css('width', "100%");
    $('.ui-dialog .ui-dialog-content').css('padding-top', '15px').css('vertical-align', 'bottom');
    $('.ui-button-text').css('padding', '0px 5px');
    $('.fa-icon-picker').iconpicker('.fa-icon-picker');

    function getGlobalSettings() {
        let settings = new Object()

        popup.find('.globalSetting').each(function() {
            let value = $(this).val();
            let name = $(this).attr('name');
            let type = $(this).attr('type')
            if ((typeof value !== 'string')) {
                let value = 'something really went wrong here';
            }
            if ((value === null)) {
                value = ' ';
            }
            value = value.trim();

            // get true/false for checkbox input
            if (type === 'checkbox') {
                value = $(this).prop('checked')
            }

            settings[name] = value;
        });

        return settings;
    }

    function saveSettings() {
        let settings = getGlobalSettings()
        let settingsSting = JSON.stringify(settings)

        console.log(settings)
        dockerFolders['settings'] = settings

        $.post('/plugins/docker.folder/scripts/save_global-settings.php', {
            folderFile: '<?= $folderFile?>',
            settings: settingsSting,
        });
    }
}
</script>