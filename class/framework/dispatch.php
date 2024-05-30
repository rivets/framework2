<?php
/**
 * Contains the definition of the Dispatch class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2024 Newcastle University
 * @package Framework\Framework
 */
    namespace Framework;

    use \Config\Config;
    use \Config\Framework as FW;
    use \Framework\Exception\BadValue;
    use \Framework\Web\StatusCodes;
    use \Support\Context;
/**
 * This class dispatches pages to the appropriate places
 *
 * @todo use an enum for constants when 8.1 arrives
 */
    class Dispatch
    {
/*
 * Indicates that there is an Object that handles the call
 */
        public const int OBJECT     = 1;
/*
 * Indicates that there is only a template for this URL.
 */
        public const int TEMPLATE   = 2;
/*
 * Indicates that the URL should be temporarily redirected - 302
 */
        public const int REDIRECT   = 3;
/*
 * Indicates that the URL should be permanent redirected - 301
 */
        public const int REHOME     = 4;
/*
 * Indicates that the URL should be permanently redirected - 302
 */
        public const int XREDIRECT  = 5;
/*
 * Indicates that the URL should be temporarily redirected -301
 */
        public const int XREHOME    = 6;
/*
 * Indicates that the URL should be temporarily redirected - 303
 */
        public const int REDIRECT3  = 7;
/*
 * Indicates that the URL should be temporarily redirected - 303
 */
        public const int XREDIRECT3 = 8;
/*
 * Indicates that the URL should be temporarily redirected - 307
 */
        public const int REDIRECT7  = 9;
/*
 * Indicates that the URL should be temporarily redirected - 307
 */
        public const int XREDIRECT7 = 10;
/*
 * Indicates that the URL should be permanently redirected - 308
 */
        public const int REHOME8    = 11;
/*
 * Indicates that the URL should be permanently redirected - 308
 */
        public const int XREHOME8   = 12;
/**
 * @var array<array> Values for determining handling of above codes
 */
        private static array $actions = [
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
        private static array $checks = [
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
 * @var array<string> Constants that might be defined in the configuration that
 *                    need to be passed into templates.
 */
        private static array $configs = ['lang', 'keywords', 'description'];
/**
 * Setup basic values for templates
 */
        public static function basicSetup(Context $context, string $action) : void
        {
            $basicvals = [
                'context'           => $context,
                'action'            => $action,
                'siteinfo'          => \Support\SiteInfo::getinstance(), // make sure we get the derived version not the Framework version
                'ajax'              => FALSE,                            // Mark pages as not using AJAX by default
                'security'          => \Framework\Support\Security::getinstance(),
                'usejquery'         => FALSE,
                'usebootstrapcss'   => TRUE,
                'usebootstrapjs'    => TRUE,
                'usebootbox'        => FALSE,
                'usevue'            => FALSE,
            ];
            foreach (self::$configs as $cf)
            {
                try
                {
                    $constant_reflex = new \ReflectionClassConstant('\\Config\\Config', \strtoupper($cf));
                    $basicvals[$cf] = $constant_reflex->getValue();
                }
                catch (\ReflectionException)
                {
                    NULL; // void
                }
            }
            $context->local()->addval($basicvals, '', TRUE);
            $context->web()->initCSP(); // prepare the CSP values
            \Framework\Support\Security::getInstance()->sslCheck($context);
        }
/**
 * Handle dispatch of a page.
 *
 * @psalm-suppress PossiblyUndefinedMethod
 */
        public static function handle(Context $context, string $action) : void
        {
            $local = $context->local();
            $mime = \Framework\Web\Web::HTMLMIME;
/*
 * Look in the database for what to do based on the first part of the URL. DBRX means do a regexp match (not yet implemented)
 */
            try
            {
                /**
                 * @psalm-suppress TypeDoesNotContainType
                 * @psalm-suppress RedundantCondition
                 * @phan-suppress-next-line PhanUndeclaredClassConstant
                 */
                $page = \R::findOne(FW::PAGE, 'name'.(Config::DBRX ? ' regexp ' : '=').'? and active=?', [$action, 1]);
            }
            catch (\Throwable)
            { // You catch DB errors from pages that are not active or don't explicitly exist.
                $page = NULL;
            }
            if (!is_object($page))
            { // No such page or it is marked as inactive so pass it to NoPage to handle it
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

                if (\is_array($tpl)) // @phan-suppress-current-line PhanPossiblyUndeclaredVariable
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
            if ($tpl !== '') // @phan-suppress-current-line PhanPossiblyUndeclaredVariable
            { // an empty template string means generate no output here...
                $html = $local->getRender($tpl); // @phan-suppress-current-line PhanPossiblyUndeclaredVariable
                // Now set up CSP Header in use : rendering the page may have generated new hashcodes.
                /** @psalm-suppress PossiblyUndefinedVariable - if we get here it is defined */
                $csp->setCSP(); // @phan-suppress-current-line PhanPossiblyUndeclaredVariable
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
 * @param string $source This should be a class name possibly namespaced
 *
 * @throws BadValue
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkObject(string $source) : void
        {
            if (!\preg_match('/^(\\\\?[a-z][a-z0-9]*)+$/i', $source))
            {
                throw new BadValue('Invalid source for page type (class name) "'.$source.'"');
            }
        }
/**
 * Check TEMPLATE
 *
 * @param string $source This should be a twig file name, possibly namespaced
 *
 * @throws BadValue
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkTemplate(string $source) : void
        {
            if (!\preg_match('#^@?(\w+/)?\w+\.twig$#i', $source))
            {
                throw new BadValue('Invalid source for page type (twig) "'.$source.'"');
            }
        }
/**
 * Check REDIRECT - internal so no http
 *
 * @param string $source This should be a local url path
 *
 * @throws BadValue
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkRedirect(string $source) : void
        {
            if (!\preg_match('#^(/.*?)+#i', $source))
            {
                throw new BadValue('Invalid source for page type (local url path)');
            }
        }
/**
 * Check XREDIRECT - external so must be a url
 *
 * @param string $source This should be a URL
 *
 * @throws BadValue
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static function checkXRedirect(string $source) : void
        {
            if (\filter_var($source, \FILTER_VALIDATE_URL) === FALSE)
            {
                throw new BadValue('Invalid source for page type (URL)');
            }
        }
/**
 * Check if a value is appropriate for the dispatch kind
 *
 * @throws BadValue
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