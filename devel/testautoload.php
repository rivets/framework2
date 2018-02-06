<?php
    $dir = dirname(__DIR__);
    set_include_path(
        implode(PATH_SEPARATOR, [
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/framework']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/models']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/modelextend']),
                    get_include_path()
                ])
    );
    spl_autoload_extensions('.php');
    spl_autoload_register();

    include $dir.'/vendor/autoload.php';
?>
