<?php
    namespace Framework;

    use \Config\Config as Config;
    use \Framework\SiteAction as SiteAction;
    use \Framework\Web\StatusCodes as StatusCodes;
    use \Framework\Web\Web as Web;

/**
 * Contains the definition of the Dispatch class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2016 Newcastle University
 */
/**
 * This class dispatches pages to the appropriate places
 */
    class Dispatch
    {
        use \Utility\Singleton;

        public function handle($context, $action)
        {
            $local = $context->local();
            $mime = \Framework\Web\Web::HTMLMIME;
/*
 * Look in the database for what to do based on the first part of the URL. DBOP is either = or regexp
 */
            $page = \R::findOne('page', 'name'.Config::DBOP.'? and active=?', [$action, 1]);
            if (!is_object($page))
            { # No such page or it is marked as inactive
               $page = new stdClass;
               $page->kind = Siteaction::OBJECT;
               $page->source = 'NoPage';
            }
            else
            {
                if (($page->needlogin) && !$context->hasuser())
                { # not logged in
                    $context->divert('/login?page='.urlencode($local->debase($_SERVER['REQUEST_URI'])));
                    /* NOT REACHED */
                }
        
                if (($page->admin && !$context->hasadmin()) ||		// not an admin
                    ($page->devel && !$context->hasdeveloper()) ||	// not a developer
                    ($page->mobileonly && !$context->hastoken()))	// not mobile and logged in
                {
                    $context->web()->sendstring($local->getrender('error/403.twig'), $mime, StatusCodes::HTTP_FORBIDDEN);
                    exit;
                }
            }
        
            $local->addval('context', $context);
            $local->addval('page', $action);
            $local->addval('siteinfo', new \Siteinfo($local)); // make sure we get the derived version not the Framework version
        
            $code = StatusCodes::HTTP_OK;
            switch ($page->kind)
            {
            case Siteaction::OBJECT: # fire up the object to handle the request
                $tpl = (new $page->source)->handle($context);
                if (is_array($tpl))
                {
                    list($tpl, $mime, $code) = $tpl;
                }
                break;
        
            case Siteaction::TEMPLATE: # render a template
                $tpl = $page->source;
                break;
        
            case Siteaction::REDIRECT: # redirect to somewhere else on the this site (temporary)
                $context->divert($page->source, TRUE);
                /* NOT REACHED */
        
            case Siteaction::REHOME: # redirect to somewhere else on the this site (permanent)
                $context->divert($page->source, FALSE);
                /* NOT REACHED */
        
            case Siteaction::XREDIRECT: # redirect to an external URL (temporary)
                $context->web()->relocate($page->source, TRUE);
                /* NOT REACHED */
        
            case Siteaction::XREHOME: # redirect to an external URL (permanent)
                $context->web()->relocate($page->source, FALSE);
                /* NOT REACHED */
        
            default :
                $context->web()->internal('Weird error');
                /* NOT REACHED */
            }
        
            if ($tpl !== '')
            { # an empty template string means generate no output here...
                $context->web()->sendstring($local->getrender($tpl), $mime, $code);
            }
        }
    }
?>
