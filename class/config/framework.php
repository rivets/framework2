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
        const FWCONTEXT	    = self::DBPREFIX.'Site';
        const TESTCONTEXT	= self::DBPREFIX.'Test';
        const ADMINROLE	    = self::DBPREFIX.'Admin';
        const DEVELROLE	    = self::DBPREFIX.'Developer';
        const TESTROLE	    = self::DBPREFIX.'Tester';
        const CONFIG	    = self::DBPREFIX.'fwconfig';
        const CONFIRM	    = self::DBPREFIX.'confirm';
        const FORM	        = self::DBPREFIX.'form';
        const FORMFIELD	    = self::DBPREFIX.'formfield';
        const PAGE	        = self::DBPREFIX.'page';
        const PAGEROLE      = self::DBPREFIX.'pagerole';
        const ROLE	        = self::DBPREFIX.'role';
        const ROLECONTEXT	= self::DBPREFIX.'rolecontext';
        const ROLENAME	    = self::DBPREFIX.'rolename';
        const TABLE	        = self::DBPREFIX.'table';
        const USER	        = self::DBPREFIX.'user';

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
            /** @psalm-suppress UnresolvableInclude */
            include $dir.'/vendor/autoload.php';
        }
    }
?>
