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
        public const DBPREFIX	    = '';
        public const FWCONTEXT	    = self::DBPREFIX.'Site';
        public const TESTCONTEXT	= self::DBPREFIX.'Test';
        public const ADMINROLE	    = self::DBPREFIX.'Admin';
        public const DEVELROLE	    = self::DBPREFIX.'Developer';
        public const TESTROLE	    = self::DBPREFIX.'Tester';
        public const CONFIG	    = self::DBPREFIX.'fwconfig';
        public const CONFIRM	    = self::DBPREFIX.'confirm';
        public const FORM	        = self::DBPREFIX.'form';
        public const FORMFIELD	    = self::DBPREFIX.'formfield';
        public const PAGE	        = self::DBPREFIX.'page';
        public const PAGEROLE      = self::DBPREFIX.'pagerole';
        public const ROLE	        = self::DBPREFIX.'role';
        public const ROLECONTEXT	= self::DBPREFIX.'rolecontext';
        public const ROLENAME	    = self::DBPREFIX.'rolename';
        public const TABLE	        = self::DBPREFIX.'table';
        public const TEST	        = self::DBPREFIX.'fwtest';
        public const USER	        = self::DBPREFIX.'user';
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
            /** @psalm-suppress UnusedFunctionCall **/
            set_include_path(
                implode(PATH_SEPARATOR, [
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/model']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/modelextend']),
                    get_include_path()
                ])
            );
            /** @psalm-suppress UnusedFunctionCall **/
            spl_autoload_extensions('.php');
            spl_autoload_register();
            /** @psalm-suppress UnresolvableInclude */
            include $dir.'/vendor/autoload.php';
        }
    }
?>
