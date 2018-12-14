<?php
/**
 * Contains the definition of the Dispatch class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2018 Newcastle University
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
        use \Framework\Utility\Singleton;
/**
 * Handle dispatch of a page.
 *
 * @param object    $context
 * @param string    $action
 *
 * @return void
 */
        public function handle(Context $context, string $action)
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
               $page->kind = Siteaction::OBJECT;
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
                catch(\Exception $e)
                { // any other exception - this will be a framework internal error
                    $context->web()->internal($e->getMessage());
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

            case SiteAction::REDIRECT: // redirect to somewhere else on the this site (temporary)
                $context->divert($page->source, TRUE);
                /* NOT REACHED */

            case SiteAction::REHOME: // redirect to somewhere else on the this site (permanent)
                $context->divert($page->source, FALSE);
                /* NOT REACHED */

            case SiteAction::XREDIRECT: // redirect to an external URL (temporary)
                $context->web()->relocate($page->source, TRUE);
                /* NOT REACHED */

            case SiteAction::XREHOME: // redirect to an external URL (permanent)
                $context->web()->relocate($page->source, FALSE);
                /* NOT REACHED */

            default :
                $context->web()->internal('Weird error');
                /* NOT REACHED */
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
    }
?>
