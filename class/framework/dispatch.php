<?php
/**
 * Contains the definition of the Dispatch class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2019 Newcastle University
 */
    namespace Framework;

    use \Config\Config as Config;
    use \Framework\SiteAction as SiteAction;
    use \Framework\Web\StatusCodes as StatusCodes;
    use \Framework\Web\Web as Web;
    use Support\Context as Context;

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
 * @var $action Values for determining handling of above codes
 */
        public static $actions = [
            REDIRECT    => [TRUE,  [TRUE, '', FALSE, FALSE]],
            REHOME      => [TRUE,  [FALSE, '', FALSE, FALSE]],
            XREDIRECT   => [FALSE, [TRUE, '', FALSE, FALSE]],
            XREHOME     => [FALSE, [FALSE, '', FALSE, FALSE]],
            REDIRECT3   => [TRUE,  [TRUE, '', FALSE, TRUE]],
            XREDIRECT3  => [FALSE, [TRUE, '', FALSE, TRUE]],
            REDIRECT7   => [TRUE,  [TRUE, '', TRUE, FALSE]],
            XREDIRECT7  => [FALSE, [TRUE, '', TRUE, FALSE]],
            REHOME8     => [TRUE,  [FALSE, '', TRUE, FALSE]],
            XREHOME8    => [FALSE, [FALSE, '', TRUE, FALSE]],
        ];
/**
 * Handle dispatch of a page.
 *
 * @param object    $context
 * @param string    $action
 *
 * @return void
 */
        static public function handle(Context $context, string $action)
        {
            $local = $context->local();
            $mime = \Framework\Web\Web::HTMLMIME;
/*
 * Look in the database for what to do based on the first part of the URL. DBRX means do a regep match
 */
            $page = \R::findOne('page', 'name'.(Config::DBRX ? ' regexp ' : '=').'? and active=?', [$action, 1]);
            if (!is_object($page))
            { # No such page or it is marked as inactive
               $page = new stdClass;
               $page->kind = self::OBJECT;
               $page->source = '\Pages\NoPage';
            }
            else
            {
                $page->check($context);
            }
        
            $local->addval([
                'context'   => $context,
                'action'    => $action,
                'siteinfo'  => \Support\Siteinfo::getinstance(), // make sure we get the derived version not the Framework version
                'ajax'      => FALSE,                            // Mark pages as not using AJAX by default
            ]);

            $code = StatusCodes::HTTP_OK;
            switch ($page->kind)
            {
            case SiteAction::OBJECT: // fire up the object to handle the request
                $pageObj = new $page->source;
                $csp = $pageObj;
                try
                {
                    $tpl = $pageObj->handle($context);
                    $pageObj->setCache($context);
                }
                catch(\Framework\Exception\Forbidden $e)
                {
                    $context->web()->noaccess($e->getMessage());
                }
                catch(\Framework\Exception\BadValue |
                      \Framework\Exception\BadOperation |
                      \Framework\Exception\MissingBean |
                      \Framework\Exception\ParameterCount $e)
                {
                    $context->web()->bad($e->getMessage());
                }
        
                if (is_array($tpl))
                { // page is returning more than just a template filename
                    list($tpl, $mime, $code) = $tpl;
                }
                break;

            case SiteAction::TEMPLATE: // render a template
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
                }
                else
                {
                    $context->web()->relocate($page->source, ...self::$actions[$page->kind][1]);
                }
                break;
            }

            if ($tpl !== '')
            { # an empty template string means generate no output here...
                $html = $local->getrender($tpl);
                $csp->setCSP($context); // set up CSP Header in use : rendering the page may have generated new hashcodes.
                $context->web()->sendstring($html, $mime, $code);
            }
            //else if ($code != StatusCodes::HTTP_OK);
            //{
            //    header(StatusCodes::httpHeaderFor($code));
            //}
        }
/**
 * Check if a value is apporpriate for the dispatch kind
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public static function check($kind, $source)
        {
            switch ($kind)
            {
            case SiteAction::OBJECT:
                if (!preg_match('/^(\\[a-z][a-z0-9]*)+$/i', $source))
                {
                    throw new BadValue('Invalid source for page type (class name)');
                }
                break;
            case SiteAction::TEMPLATE:
                if (!preg_match('#^@?(\w+/)?\w+\.twig$#i', $source))
                {
                    throw new BadValue('Invalid source for page type(twig)');
                }
                break;
            case SiteAction::REDIRECT: // these need a local URL, i.e. no http
            case SiteAction::REDIRECT3:
            case SiteAction::RDIRECT7:
            case SiteAction::REHOME:
            case SiteAction::REHOME8:
                if (!preg_match('#^(/.*?)+#i', $source))
                {
                    throw new BadValue('Invalid source for page type(twig)');
                }
                break;
            case SiteAction::XREDIRECT: // these need a URL
            case SiteAction::XREDIRECT3:
            case SiteAction::XRDIRECT7:
            case SiteAction::XREHOME:
            case SiteAction::XREHOME8:
                if (filter_var($this->bean->source, FILTER_VALIDATE_URL) === FALSE)
                {
                    throw new BadValue('Invalid source for page type (URL)');
                }
                break;
            default:
                throw new BadValue('Invalid page type');
            }
        }
    }
?>
