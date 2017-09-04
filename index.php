<?php
/**
 * Main entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2017 Newcastle University
 */
/**
 * The framework assumes a self contained directory structure for a site like this :
 *
 * DOCUMENT_ROOT
 *    /sitename         This can be omitted if the site is the only one present and at the root
 *        /assets
 *            /css      CSS files
 *	      /favicons Favicon files
 *            /i18n     Any internationalisation files you may need
 *            /images   Image files
 *            /js       JavaScript
 *	      /public	Where non-access controlled uploaded files gets stored
 *            /...      Any other stuff that can be accessed without intermediation through PHP
 *        /class        PHP class definition files named "classname.php"
 *            /support  PHP class files for the administrative functions provided by the framework
 *            /models	RedBean Model class files
 *	  /debug	Used for debugging messages
 *        /lib          PHP files containing non-class definitions
 *	  /private	Where uploaded files get stored
 *        /twigcache    If twig cacheing is on this is where it stores the files
 *	  /twigs	Where the twig files go
 *            /error       Twig template files for error messages
 *            /support     Twig files for the admin support of the framework
 *        /vendor       If you are using composer then it puts stuff in here.
 *
 *         The .htaccess file directs
 * 	   anything in /assets to be served by Apache unless you are usin gthe Assets class to improve caching
 *         anything beginning "ajax" to be called directly i.e. ajax.php (this may or may not be useful - remove it if not)
 *         everything else gets passed into this script where it treats the URL thus:
 *                 /                        =>        /home and then
 *                 /action/r/e/st/          =>        Broken down in Context class. An action and an array of parameters.
 *
 *         Query strings and/or post fields are in the $_ arrays as normal but please use the access functions provided
 *         to get at the values whenever appropriate!
 */
    use \Config\Config as Config;
    use \Framework\SiteAction as SiteAction;
    use \Framework\Web\StatusCodes as StatusCodes;

    include 'class/config/framework.php';
    \Config\Framework::initialise();
    Config::setup(); # add default headers etc. - anything that the user choses to add to the code.

    $local = \Framework\Local::getinstance()->setup(__DIR__, FALSE, TRUE, TRUE, TRUE); # Not Ajax, developer mode on, load twig, load RB
    $context = \Context::getinstance()->setup();

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
        $page->check($context);
	//if (($page->needlogin) && !$context->hasuser())
	//{ # not logged in
	//    $context->divert('/login?page='.urlencode($local->debase($_SERVER['REQUEST_URI'])));
	//    /* NOT REACHED */
	//}
	//
	//if (($page->admin && !$context->hasadmin()) ||		// not an admin
	//    ($page->devel && !$context->hasdeveloper()) ||	// not a developer
	//    ($page->mobileonly && !$context->hastoken()))	// not mobile and logged in
	//{
	//    $context->web()->sendstring($local->getrender('error/403.twig'), $mime, StatusCodes::HTTP_FORBIDDEN);
	//    exit;
	//}
    }

    $local->addval('context', $context);
    $local->addval('action', $action);
    $local->addval('siteinfo', new \Siteinfo($local)); // make sure we get the derived version not the Framework version
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
?>