<?php
require_once("index.html");
exit(1);
function get_row($name, $index) {
    return '<div id="_t' . $index. '" style="display:block;">' . $name .'</div>';
}
for ($i = 0; $i < 30; $i++) {
    printf(get_row('_' . $i, $i));
}
?>
