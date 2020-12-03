<?php
/**
 * Contains the definition of the Dispatch class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2020 Newcastle University
 * @package Framework
 */
    namespace Framework;

    use \Config\Config;
    use \Config\Framework as FW;
    use \Framework\Exception\BadValue;
    use \Framework\Web\StatusCodes;
    use \Support\Context;
/**
 * This class dispatches pages to the appropriate places
 */
    class Dispatch
    {
/*
 * Indicates that there is an Object that handles the call
 */
        public const OBJECT     = 1;
/*
 * Indicates that there is only a template for this URL.
 */
        public const TEMPLATE   = 2;
/*
 * Indicates that the URL should be temporarily redirected - 302
 */
        public const REDIRECT   = 3;
/*
 * Indicates that the URL should be permanent redirected - 301
 */
        public const REHOME     = 4;
/*
 * Indicates that the URL should be permanently redirected - 302
 */
        public const XREDIRECT  = 5;
/*
 * Indicates that the URL should be temporarily redirected -301
 */
        public const XREHOME    = 6;
/*
 * Indicates that the URL should be temporarily redirected - 303
 */
        public const REDIRECT3  = 7;
/*
 * Indicates that the URL should be temporarily redirected - 303
 */
        public const XREDIRECT3 = 8;
/*
 * Indicates that the URL should be temporarily redirected - 307
 */
        public const REDIRECT7  = 9;
/*
 * Indicates that the URL should be temporarily redirected - 307
 */
        public const XREDIRECT7 = 10;
/*
 * Indicates that the URL should be permanently redirected - 308
 */
        public const REHOME8    = 11;
/*
 * Indicates that the URL should be permanently redirected - 308
 */
        public const XREHOME8   = 12;
/**
 * @var array<array> $actions Values for determining handling of above codes
 */
        private static $actions = [
            self::REDIRECT    => [TRUE,  [TRUE, '', FALSE, FALSE]],
            self::REHOME      => [TRUE,  [FALSE, '', FALSE, FALSE]],
            self::XREDIRECT   => [FALSE, [TRUE, '', FALSE, FALSE]],
            self::XREHOME     => [FALSE, [FALSE, '', FALSE, FALSE]],
            self::REDIRECT3   => [TRUE,  [TRUE, '', FALSE, TRUE]],
            self::XREDIRECT3  => [FALSE, [TRUE, '', FALSE, TRUE]],
            self::REDIRECT7   => [TRUE,  [TRUE, '', TRUE, FALSE]],
            self::XREDIRECT7  => [FALSE, [TRUE, '', TRUE, FALSE]],
            self::REHOME8     => [TRUE,  [FALSE, '', TRUE, FALSE]],
            self::XREHOME8    => [FALSE, [FALSE, '', TRUE, FALSE]],
        ];
/**
 * @var array<string>
 */
        private static $checks = [
            self::OBJECT        => 'checkObject',
            self::TEMPLATE      => 'checkTemplate',
            self::REDIRECT      => 'checkRedirect',
            self::REDIRECT3     => 'checkRedirect',
            self::REDIRECT7     => 'checkRedirect',
            self::REHOME        => 'checkRedirect',
            self::REHOME8       => 'checkRedirect',
            self::XREDIRECT     => 'checkXRedirect',
            self::XREDIRECT3    => 'checkXRedirect',
            self::XREDIRECT7    => 'checkXRedirect',
            self::XREHOME       => 'checkXRedirect',
            self::XREHOME8      => 'checkXRedirect',
        ];
/**
 * @var array $configs Constants that might be defined in the configuration that
 * need to be passed into twigs.
 */
        private static $configs = ['lang', 'keywords', 'description'];
/**
 * Setup basic values
 *
 * @param Context   $context
 * @param string    $action
 *
 * @return void
 */
        public static function basicSetup(Context $context, string $action) : void
        {
            $basicvals = [
                'context'           => $context,
                'action'            => $action,
                'siteinfo'          => \Support\SiteInfo::getinstance(), // make sure we get the derived version not the Framework version
                'ajax'              => FALSE,                            // Mark pages as not using AJAX by default
                'security'          => \Framework\Support\Security::getinstance(),
                'usejquery'         => TRUE,
                'usebootstrapcss'   => TRUE,
                'usebootstrapjs'    => TRUE,
                'usebootbox'        => TRUE,
                'usevue'            => FALSE,
            ];
            foreach (self::$configs as $cf)
            {
                try
                {
                    $constant_reflex = new \ReflectionClassConstant('\\Config\\Config', strtoupper($cf));
                    $basicvals[$cf] = $constant_reflex->getValue();
                }
                catch (\ReflectionException $e)
                {
                    NULL; // void
                }
            }
            $context->local()->addval($basicvals, '', TRUE);
        }
/**
 * Handle dispatch of a page.
 *
 * @param Context   $context
 * @param string    $action
 *
 * @psalm-suppress PossiblyUndefinedMethod
 *
 * @return void
 */
        public static function handle(Context $context, string $action) : void
        {
            $local = $context->local();
            $mime = \Framework\Web\Web::HTMLMIME;
/*
 * Look in the database for what to do based on the first part of the URL. DBRX means do a regexp match
 */
            try
            {
                /**
                 * @psalm-suppress TypeDoesNotContainType
                 * @psalm-suppress RedundantCondition
                 */
                $page = \R::findOne(FW::PAGE, 'name'.(Config::DBRX ? ' regexp ' : '=').'? and active=?', [$action, 1]);
            }
            catch (\Exception $e)
            { // You catch DB errors from hacky URL values here.
                $page = NULL;
            }
            if (!is_object($page))
            { // No such page or it is marked as inactive
               $page = new \stdClass();
               $page->kind = self::OBJECT;
               $page->source = '\Pages\NoPage';
            }
            else
            {
                $page->check($context);
            }

            self::basicSetup($context, $action);

            $code = StatusCodes::HTTP_OK;
            switch ($page->kind)
            {
            case self::OBJECT: // fire up the object to handle the request
                $pageObj = new $page->source();
                $csp = $pageObj;
                try
                {
                    $pageObj->ifmodcheck($context); // check for any If- headers
                    \Support\Setup::preliminary($context, $page); // any user setup code
                    $tpl = $pageObj->handle($context);
                    $pageObj->setCache($context); // set up cache-control headers.
                }
                catch(\Framework\Exception\Forbidden $e)
                {
                    $context->web()->noaccess($e->getMessage());
                    /* NOT REACHED */
                }
                catch(BadValue |
                      \Framework\Exception\BadOperation |
                      \Framework\Exception\MissingBean |
                      \Framework\Exception\ParameterCount $e)
                {
                    $context->web()->bad($e->getMessage());
                    /* NOT REACHED */
                }

                if (is_array($tpl))
                { // page is returning more than just a template filename
                    [$tpl, $mime, $code] = $tpl;
                }
                break;

            case self::TEMPLATE: // render a template
                \Support\Setup::preliminary($context, $page); // any user setup code
                $csp = $context->web();
                $tpl = $page->source;
                break;

            default:
                if (!isset(self::$actions[$page->kind]))
                { // check value is OK
                    $context->web()->internal('Bad page kind');
                    /* NOT REACHED */
                }
                if (self::$actions[$page->kind][0])
                { // local diversion
                    $context->divert($page->source, ...self::$actions[$page->kind][1]);
                    /* NOT REACHED */
                }
                $context->web()->relocate($page->source, ...self::$actions[$page->kind][1]); // off site relocation
                /* NOT REACHED */
            }
            /** @psalm-suppress PossiblyUndefinedVariable - if we get here it is defined */
            if ($tpl !== '')
            { // an empty template string means generate no output here...
                $html = $local->getrender($tpl);
                /** @psalm-suppress PossiblyUndefinedVariable - if we get here it is defined */
                $csp->setCSP(); // set up CSP Header in use : rendering the page may have generated new hashcodes.
                $context->web()->sendstring($html, $mime, $code);
            }
            //else if ($code != StatusCodes::HTTP_OK);
            //{
            //    header(StatusCodes::httpHeaderFor($code));
            //}
        }
/**
 * Check OBJECT
 *
 * @param string $source
 *
 * @throws BadValue
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkObject(string $source)
        {
            if (!preg_match('/^(\\\\?[a-z][a-z0-9]*)+$/i', $source))
            {
                throw new BadValue('Invalid source for page type (class name) "'.$source.'"');
            }
        }
/**
 * Check TEMPLATE
 *
 * @param string $source
 *
 * @throws BadValue
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkTemplate(string $source)
        {
            if (!preg_match('#^@?(\w+/)?\w+\.twig$#i', $source))
            {
                throw new BadValue('Invalid source for page type (twig) "'.$source.'"');
            }
        }
/**
 * Check REDIRECT - internal so no http
 *
 * @param string $source
 *
 * @throws BadValue
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkRedirect(string $source)
        {
            if (!preg_match('#^(/.*?)+#i', $source))
            {
                throw new BadValue('Invalid source for page type (local path)');
            }
        }
/**
 * Check XREDIRECT - external so must be a url
 *
 * @param string $source
 *
 * @throws BadValue
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkXRedirect(string $source)
        {
            if (filter_var($source, FILTER_VALIDATE_URL) === FALSE)
            {
                throw new BadValue('Invalid source for page type (URL)');
            }
        }
/**
 * Check if a value is appropriate for the dispatch kind
 *
 * @param int    $kind
 * @param string $source
 *
 * @throws BadValue
 * @return void
 */
        public static function check(int $kind, string $source) : void
        {
            if (!isset(self::$checks[$kind]))
            {
                throw new BadValue('Invalid page type');
            }
            self::{self::$checks[$kind]}($source);
        }
    }
?>