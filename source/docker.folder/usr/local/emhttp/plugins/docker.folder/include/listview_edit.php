<script>
function listview_edit() {
    let lv = listview.toString()

    let args = lv.slice(lv.indexOf("(") + 1, lv.indexOf(")"))
    lv = lv.slice(lv.indexOf("{") + 1, lv.lastIndexOf("}"))

    let dataArray = lv.split('\n'); // convert file data in an array
    const searchKeyword = 'docker_load_stop';
    let lastIndex = -1; // lets say, we have not found the keyword

    for (let index = 0; index < dataArray.length; index++) {
        if (dataArray[index].includes(searchKeyword)) { // check if a line contains the 'searchKeyword' keyword
            lastIndex = index;
            break;
        }
    }

    if (lastIndex !== -1) {
        dataArray.splice(lastIndex, 1);
    }

    let lv_final = dataArray.join('\n')

    listview = new Function(args, lv_final + "\n")
}
</script>