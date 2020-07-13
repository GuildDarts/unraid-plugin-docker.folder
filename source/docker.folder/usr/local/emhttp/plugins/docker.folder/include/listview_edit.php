<script>
function listview_edit() {
    let lv = listview.toString()

    let args = lv.slice(lv.indexOf("(") + 1, lv.indexOf(")"))
    lv = lv.slice(lv.indexOf("{") + 1, lv.lastIndexOf("}"))

    let lv_array = lv.split('\n');

    lv_array = searchArrayAndReplace('docker_load_stop', lv_array)

    let lv_final = lv_array.join('\n')

    listview = new Function(args, lv_final + "\n")
}
</script>