<?php
/**
 * This is loaded in index.php and ajax.php. It does mean that it has to be included as
 * it is setting up the autoloader and stuff. But it does keep things DRY - only one place to
 * add any new autoload places etc.
 *
 * Note this only has a single static function. This is not great for unit testing, but
 * it seems to make more sense than having to create an instance that is never going to
 * be used again. (Equally this could just be code in a file rather than being a function
 * or a class, but that just seems nasty)
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2019 Newcastle University
 */
    namespace Config;
/**
 * Class for doing initial setup of the Framework.
 */
    class Framework
    {
        const DBPREFIX	    = '';
        const FWCONTEXT	    = DBPREFIX.'Site';
        const TESTCONTEXT	= DBPREFIX.'Test';
        const ADMINROLE	    = DBPREFIX.'Admin';
        const DEVELROLE	    = DBPREFIX.'Developer';
        const TESTROLE	    = DBPREFIX.'Tester';
        const CONFIG	    = DBPREFIX.'fwconfig';
        const CONFIRM	    = DBPREFIX.'confirm';
        const FORM	        = DBPREFIX.'form';
        const FORMFIELD	    = DBPREFIX.'formfield';
        const PAGE	        = DBPREFIX.'page';
        const PAGEROLE      = DBPREFIX.'pagerole';
        const ROLE	        = DBPREFIX.'role';
        const ROLECONTEXT	= DBPREFIX.'rolecontext';
        const ROLENAME	    = DBPREFIX.'rolename';
        const TABLE	        = DBPREFIX.'table';
        const USER	        = DBPREFIX.'user';

/**
 * Initialise some standard things for any invocation of a page
 *
 * @return void
 */
        public static function initialise() : void
        {
            error_reporting(E_ALL|E_STRICT);
/*
 * Setup the autoloader
 */
            $dir = dirname(dirname(__DIR__));
            set_include_path(
                implode(PATH_SEPARATOR, [
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/model']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/modelextend']),
                    get_include_path()
                ])
            );
            spl_autoload_extensions('.php');
            spl_autoload_register();

            include $dir.'/vendor/autoload.php';
        }
    }
?>
