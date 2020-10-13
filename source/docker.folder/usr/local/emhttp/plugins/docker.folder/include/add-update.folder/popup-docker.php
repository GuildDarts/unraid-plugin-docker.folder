<body>
<div id="templatePopupConfig" style="display:none">
  <dl>
    <blockquote id="popup-helper" style="display: none;">
    </blockquote>
    <dt>Config Type:</dt>
    <dd>
      <select name="Type" onchange="toggleMode(this,false);">
        <option value="WebUI">WebUI</option>
        <option value="WebUI_New_Tab">WebUI New Tab</option>
        <option value="Action">Docker Action</option>
        <option value="Bash">Bash</option>
        <option value="Sub_Menu">Docker Sub Menu</option>
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
</body>

<script>
    function toggleMode(el, disabled) {
    var base = $(el).parent().parent();

    var popup = base.find("#popup-helper")
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
    popup.html('')

    switch ($(el)[0].selectedIndex) {
        case 0: // WebUI
            base.find('#dt3').text('URL:');
            icon_input.val('globe')
            popup.html(`
            <p>[IP] will get your unraid servers ip</p>
            <p>[PORT:xxxx]</p>
            <p>[DOCKER:xxxx] will get specified docker webUI</p>
            `)
        break;

        case 1: // WebUI_New_Tab
            base.find('#dt3').text('URL:');
            icon_input.val('globe')
            popup.html(`
            <p>[IP] will get your unraid servers ip</p>
            <p>[PORT:xxxx]</p>
            <p>[DOCKER:xxxx] will get specified docker webUI</p>
            `)
        break;

        case 2: // Action
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

        case 3: // Bash
            icon.show()
            base.find('#dt3').text('CMD:');
        break;

        case 4: // Sub_Menu
            name.hide()
            cmd.hide()
            subMenu.show()

            subMenu_input.val(name_input.val())
            subMenu_input.change(function() {
                var index = $(this).prop('selectedIndex')
                name_input.val($(this).children("option:selected").text())
                icon_input.val('docker')
                cmd_input.val($(this).children("option:selected").text())
            })
        break;
    }
  }
</script>