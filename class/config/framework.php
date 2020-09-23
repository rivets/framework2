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
 * @copyright 2015-2020 Newcastle University
 * @package Framework
 */
    namespace Config;

/**
 * Class for doing initial setup of the Framework.
 */
    final class Framework
    {
/*
 * Constants that are used to get the names of the Framework's internal tables
 */
        public const ADMINROLE = 'Admin'; // role names
        public const DEVELROLE = 'Developer';
        public const TESTROLE = 'Tester';

        public const DBPREFIX = '';

        public const FWCONTEXT = self::DBPREFIX.'Site'; // context names
        public const TESTCONTEXT = self::DBPREFIX.'Test';

        public const CONFIG = self::DBPREFIX.'fwconfig'; // table names
        public const CONFIRM = self::DBPREFIX.'confirm';
        public const FORM = self::DBPREFIX.'form';
        public const FORMFIELD = self::DBPREFIX.'formfield';
        public const PAGE = self::DBPREFIX.'page';
        public const PAGEROLE = self::DBPREFIX.'pagerole';
        public const ROLE = self::DBPREFIX.'role';
        public const ROLECONTEXT = self::DBPREFIX.'rolecontext';
        public const ROLENAME = self::DBPREFIX.'rolename';
        public const TABLE = self::DBPREFIX.'table';
        public const TEST = self::DBPREFIX.'fwtest';
        public const USER = self::DBPREFIX.'user';

        public const AUTHTOKEN     = 'X-APPNAME-TOKEN'; // The name of the authentication token field.
        public const AUTHKEY       = 'Some string of text.....'; // The key used to encode the token validation
/**
 * Initialise some standard things for any invocation of a page
 *
 * @return void
 */
        public static function initialise() : void
        {
            error_reporting(E_ALL | E_STRICT);
/*
 * Setup the autoloader
 */
            $dir = dirname(__DIR__, 2);
            /** @psalm-suppress UnusedFunctionCall */
            set_include_path(
                implode(PATH_SEPARATOR, [
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/model']),
                    implode(DIRECTORY_SEPARATOR, [$dir, 'class/modelextend']),
                    get_include_path(),
                ])
            );
            /** @psalm-suppress UnusedFunctionCall */
            spl_autoload_extensions('.php');
            spl_autoload_register();
            /** @psalm-suppress UnresolvableInclude */
            include $dir.'/vendor/autoload.php';
        }
/**
 * Get the value of a Configuration constant. Rather than accessing constants directly
 * This uses refelection to check for the value. This allows the framework to add new
 * constants and not break old code.
 *
 * @param string $name        The constant name - all in upper case
 * @param mixed  $default     A default value for if it is not defined
 *
 * @return mixed
 */
        public static function constant($name, $default = '')
        {
            if (defined('\\Config\\Config::'.$name))
            {
                return constant('\\Config\\Config::'.$name);
            }
            return $default;
        }
    }
?>
