<?php
/**
 * Contains the definition of the Dispatch class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2019 Newcastle University
 */
    namespace Framework;

    use \Config\Config as Config;
    use \Config\Framework as FW;
    use \Framework\SiteAction as SiteAction;
    use \Framework\Web\StatusCodes as StatusCodes;
    use \Framework\Web\Web as Web;
    use \Support\Context as Context;

/**
 * This class dispatches pages to the appropriate places
 */
    class Dispatch
    {
/*
 * Indicates that there is an Object that handles the call
 */
        const OBJECT	= 1;
/*
 * Indicates that there is only a template for this URL.
 */
        const TEMPLATE	= 2;
/*
 * Indicates that the URL should be temporarily redirected - 302
 */
        const REDIRECT	= 3;
/*
 * Indicates that the URL should be permanent redirected - 301
 */
        const REHOME	= 4;
/*
 * Indicates that the URL should be permanently redirected - 302
 */
        const XREDIRECT	= 5;
/*
 * Indicates that the URL should be temporarily redirected -301
 */
        const XREHOME	= 6;
/*
 * Indicates that the URL should be temporarily redirected - 303
 */
        const REDIRECT3	= 7;
/*
 * Indicates that the URL should be temporarily redirected - 303
 */
        const XREDIRECT3	= 8;
/*
 * Indicates that the URL should be temporarily redirected - 307
 */
        const REDIRECT7	= 9;
/*
 * Indicates that the URL should be temporarily redirected - 307
 */
        const XREDIRECT7	= 10;
/*
 * Indicates that the URL should be permanently redirected - 308
 */
        const REHOME8	= 11;
/*
 * Indicates that the URL should be permanently redirected - 308
 */
        const XREHOME8	= 12;
/**
 * @var array $actions Values for determining handling of above codes
 */
        public static $actions = [
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
 * @var array $configs Constants that might be defined in the configuration that
 * need to be passed into twigs.
 */
        private static $configs = ['lang', 'keywords', 'description'];
/**
 * Handle dispatch of a page.
 *
 * @param \Support\Context    $context
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
 * Look in the database for what to do based on the first part of the URL. DBRX means do a regep match
 */
            /**
             * @psalm-suppress TypeDoesNotContainType
             * @psalm-suppress RedundantCondition
             */
            $page = \R::findOne(FW::PAGE, 'name'.(Config::DBRX ? ' regexp ' : '=').'? and active=?', [$action, 1]);
            if (!is_object($page))
            { # No such page or it is marked as inactive
               $page = new \stdClass;
               $page->kind = self::OBJECT;
               $page->source = '\Pages\NoPage';
            }
            else
            {
                $page->check($context);
            }
        
            $basicvals = [
                'context'           => $context,
                'action'            => $action,
                'siteinfo'          => \Support\SiteInfo::getinstance(), // make sure we get the derived version not the Framework version
                'ajax'              => FALSE,                            // Mark pages as not using AJAX by default
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
                    // void
                }
            }
            $local->addval($basicvals, '', TRUE);

            $etag = '';
            $code = StatusCodes::HTTP_OK;
            switch ($page->kind)
            {
            case self::OBJECT: // fire up the object to handle the request
                $pageObj = new $page->source;
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
                catch(\Framework\Exception\BadValue |
                      \Framework\Exception\BadOperation |
                      \Framework\Exception\MissingBean |
                      \Framework\Exception\ParameterCount $e)
                {
                    $context->web()->bad($e->getMessage());
                    /* NOT REACHED */
                }

                if (is_array($tpl))
                { // page is returning more than just a template filename
                    list($tpl, $mime, $code) = $tpl;
                }
                break;

            case self::TEMPLATE: // render a template
                \Support\Setup::preliminary($context, $page); // any user setup code
                $csp = $context->web();
                $tpl = $page->source;
                break;

            default:
                if (!isset(self::$actions[$page->kind]))
                { # check value is OK
                    $context->web()->internal('Bad page kind');
                    /* NOT REACHED */
                }
                if (self::$actions[$page->kind][0])
                { # local
                    $context->divert($page->source, ...self::$actions[$page->kind][1]);
                    /* NOT REACHED */
                }
                else
                {
                    $context->web()->relocate($page->source, ...self::$actions[$page->kind][1]);
                    /* NOT REACHED */
                }
                /* NOT REACHED */
            }
            /** @psalm-suppress PossiblyUndefinedVariable - if we get here it is defined */
            if ($tpl !== '')
            { # an empty template string means generate no output here...
                $html = $local->getrender($tpl);
                /** @psalm-suppress PossiblyUndefinedVariable - if we get here it is defined */
                $csp->setCSP($context); // set up CSP Header in use : rendering the page may have generated new hashcodes.
                $context->web()->sendstring($html, $mime, $code);
            }
            //else if ($code != StatusCodes::HTTP_OK);
            //{
            //    header(StatusCodes::httpHeaderFor($code));
            //}
        }
/**
 * Check if a value is appropriate for the dispatch kind
 *
 * @param int $kind
 * @param string $source
 *
 * @throws \Framework\Exception\BadValue
 *
 * @return void
 */
        public static function check(int $kind, string $source) : void
        {
            switch ($kind)
            {
            case self::OBJECT:
                if (!preg_match('/^(\\\\?[a-z][a-z0-9]*)+$/i', $source))
                {
                    throw new \Framework\Exception\BadValue('Invalid source for page type (class name) "'.$source.'"');
                }
                break;
            case self::TEMPLATE:
                if (!preg_match('#^@?(\w+/)?\w+\.twig$#i', $source))
                {
                    throw new \Framework\Exception\BadValue('Invalid source for page type (twig) "'.$source.'"');
                }
                break;
            case self::REDIRECT: // these need a local URL, i.e. no http
            case self::REDIRECT3:
            case self::REDIRECT7:
            case self::REHOME:
            case self::REHOME8:
                if (!preg_match('#^(/.*?)+#i', $source))
                {
                    throw new \Framework\Exception\BadValue('Invalid source for page type (local path)');
                }
                break;
            case self::XREDIRECT: // these need a URL
            case self::XREDIRECT3:
            case self::XREDIRECT7:
            case self::XREHOME:
            case self::XREHOME8:
                if (filter_var($source, FILTER_VALIDATE_URL) === FALSE)
                {
                    throw new \Framework\Exception\BadValue('Invalid source for page type (URL)');
                }
                break;
            default:
                throw new \Framework\Exception\BadValue('Invalid page type');
            }
        }
    }
?>
