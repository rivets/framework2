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
 * @copyright 2015-2022 Newcastle University
 * @package Framework\Config
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
        public const TESTROLE  = 'Tester';

        public const DBPREFIX = 'fw';

        public const FWCONTEXT      = 'Site'; // context names
        public const TESTCONTEXT    = 'Test';

        public const AJAX           = self::DBPREFIX.'ajax'; // table names
        public const BEANLOG        = self::DBPREFIX.'beanlog';
        public const CONFIG         = self::DBPREFIX.'config';
        public const CONFIRM        = self::DBPREFIX.'confirm';
        public const CSP            = self::DBPREFIX.'csp';
        public const FLOOD          = self::DBPREFIX.'flood';
        public const FORM           = self::DBPREFIX.'form';
        public const FORMFIELD      = self::DBPREFIX.'formfield';
        public const PAGE           = self::DBPREFIX.'page';
        public const PAGEROLE       = self::DBPREFIX.'pagerole';
        public const ROLE           = self::DBPREFIX.'role';
        public const ROLECONTEXT    = self::DBPREFIX.'rolecontext';
        public const ROLENAME       = self::DBPREFIX.'rolename';
        public const TABLE          = self::DBPREFIX.'table';
        public const TEST           = self::DBPREFIX.'test';
        public const UPLOAD         = self::DBPREFIX.'upload';
        public const USER           = 'user';

        public const MODELPATH    = '\\Framework\\Model\\';
        public const UPLOADMCLASS   = self::MODELPATH.self::UPLOAD;
        public const USERMCLASS     = self::MODELPATH.self::USER;

        public const AUTHTOKEN     = 'X-APPNAME-TOKEN'; // The name of the authentication token field.
        public const AUTHKEY       = 'Some string of text.....'; // The key used to encode the token validation

        private static $fwBeans = [
            self::AJAX        => '',
            self::BEANLOG     => '',
            self::CONFIG      => '',
            self::CSP         => '',
            self::FORM        => '',
            self::FORMFIELD   => '',
            self::PAGE        => '',
            self::PAGEROLE    => '',
            self::ROLE        => '',
            self::ROLECONTEXT => '',
            self::ROLENAME    => '',
            self::TABLE       => '',
            self::TEST        => '',
            self::UPLOAD      => '',
            self::USER        => '',
        ];

        private static bool $dbDone = FALSE;
/**
 * Initialise some standard things for any invocation of a page
 */
        public static function initialise() : void
        {
            error_reporting(E_ALL | E_STRICT);
/*
 * Setup the autoloader
 */
            $dir = dirname(__DIR__, 2);
            /** @psalm-suppress UnusedFunctionCall */
            \set_include_path(
                \implode(PATH_SEPARATOR, [
                    \implode(DIRECTORY_SEPARATOR, [$dir, 'class']),
                    \implode(DIRECTORY_SEPARATOR, [$dir, 'class/model']),
                    \implode(DIRECTORY_SEPARATOR, [$dir, 'class/modelextend']),
                    \get_include_path(),
                ])
            );
            /** @psalm-suppress UnusedFunctionCall */
            \spl_autoload_extensions('.php');
            \spl_autoload_register();
            /** @psalm-suppress UnresolvableInclude */
            include $dir.'/vendor/autoload.php'; // bring in all the stuff from composer
            define('REDBEAN_MODEL_PREFIX', '\\Model\\');
            //\class_alias('\RedBeanPHP\R', '\R');
            \Config\Update::apply(); // will normally do nothing
        }
/**
 * Get the value of a Configuration constant rather than accessing constants directly.
 *
 * This uses reflection to check for the value. This allows the framework to add new
 * constants and not break old code.
 */
        public static function constant(string $name, array|bool|int|string $default = '') : array|bool|int|string
        {
            if (defined('\\Config\\Config::'.$name))
            {
                return constant('\\Config\\Config::'.$name);
            }
            return $default;
        }
/**
 * Is this a framework bean?
 */
        public static function isFWBean(string $beanType)
        {
            return isset(self::$fwBeans[$beanType]);
        }
/**
 * Initialise the database
 * This is here because the special Update function may need to setup access to the database
 * and will call this to do it. Normally just called from Context
 */
        public static function setupDB(bool $devel) : bool
        {
            if (!self::$dbDone)
            {
                /** @psalm-suppress RedundantCondition - the mock config file has this set to a value so this. Ignore this error */
                if (Config::DBHOST !== '')
                { // looks like there is a database configured
                    \R::setup(Config::DBTYPE.':host='.Config::DBHOST.';dbname='.Config::DB, Config::DBUSER, Config::DBPW); // mysql initialiser
                    if (!R::testConnection())
                    {
                        return FALSE;
                    }
                    \R::freeze(!$devel); // freeze DB for production systems
                    \R::usePartialBeans(TRUE);
                    \R::getRedBean()->setBeanHelper(new Support\FWBeanHelper());
                    self::$dbDone = TRUE;
                }
            }
            return TRUE;
        }
    }
?>
