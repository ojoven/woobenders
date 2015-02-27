<?php
// Class Loading
function classAutoLoader($class) {
    $includePaths = array(
        ROOT_PATH. 'app/behaviours/'
    );
    set_include_path(implode(':',$includePaths));
    require $class . ".php";
}
spl_autoload_register('classAutoLoader');