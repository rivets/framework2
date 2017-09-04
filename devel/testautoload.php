<?php
    $dir = dirname(__DIR__);
    set_include_path(
        implode(PATH_SEPARATOR, array(
                    implode(DIRECTORY_SEPARATOR, array($dir, 'class')),
                    implode(DIRECTORY_SEPARATOR, array($dir, 'class/support')),
                    implode(DIRECTORY_SEPARATOR, array($dir, 'class/models')),
                    implode(DIRECTORY_SEPARATOR, array($dir, 'lib')),
                    get_include_path()
                ))
    );
    spl_autoload_extensions('.php');
    spl_autoload_register();

    include $dir.'/vendor/autoload.php';
?>
