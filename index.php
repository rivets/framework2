<?php
/**
 * Main entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2018 Newcastle University
 */
/**
 * See the information at
 *
 * @link https://catless.ncl.ac.uk/framework/
 */
    define('REDBEAN_MODEL_PREFIX', '\\Model\\');

    use \Config\Config as Config;
    use \Framework\SiteAction as SiteAction;
    use \Framework\Web\StatusCodes as StatusCodes;

    include 'class/config/framework.php';
    \Config\Framework::initialise();
    Config::setup(); # add default headers etc. - anything that the user choses to add to the code.

    $local = \Framework\Local::getinstance()->setup(__DIR__, FALSE, TRUE, TRUE, TRUE); # Not Ajax, developer mode on, load twig, load RB
    $context = \Support\Context::getinstance()->setup();

    $local->enabledebug(); # turn debugging on

    $mfl = $local->makebasepath('maintenance'); # maintenance mode indicator file
    if (file_exists($mfl) && !$context->hasadmin())
    { # only let administrators in as we are doing maintenance. Could have a similar feature for other roles
	    $context->web()->sendtemplate('support/maintenance.twig', StatusCodes::HTTP_OK, 'text/html',
	        ['msg' => file_get_contents($mfl)]);
	    exit;
    }
    $action = $context->action();
    if ($action === '')
    { # default to home if there is nothing
        $action = 'home';
    }
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

    $local->addval('context', $context);
    $local->addval('action', $action);
    $local->addval('siteinfo', new \Support\Siteinfo($local)); // make sure we get the derived version not the Framework version
/**
 * If you don't want pagination anywhere you can comment out the next bit
 */
    $form = $context->formdata();
    $local->addval('page', $form->filterget('page', 1, FILTER_VALIDATE_INT)); // just in case there is any pagination going on
    $local->addval('pagesize', $form->filterget('pagesize', 10, FILTER_VALIDATE_INT));
/** end of pagination helper **/

    $code = StatusCodes::HTTP_OK;
    switch ($page->kind)
    {
    case Siteaction::OBJECT: # fire up the object to handle the request
        $pageObj = new $page->source;
        $pageObj->setCSP();
        $tpl = $pageObj->handle($context);
        if (is_array($tpl))
        {
            list($tpl, $mime, $code) = $tpl;
        }
        break;

    case Siteaction::TEMPLATE: # render a template
        Config::setCSP();
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
?>