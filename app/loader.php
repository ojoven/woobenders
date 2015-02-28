<?php
// Class Loading
function classAutoLoader($class) {
    $includePaths = array(
        ROOT_PATH. 'lib/Phirehose/',
        ROOT_PATH. 'lib/codebird/src/',
        ROOT_PATH. 'lib/',
        ROOT_PATH. 'lib/MysqliDb/',
        ROOT_PATH. 'app/behaviours/',
        ROOT_PATH. 'app/bots/'
    );
    set_include_path(implode(':',$includePaths));
    require $class . ".php";
}
spl_autoload_register('classAutoLoader');