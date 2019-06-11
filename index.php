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
    \Framework\Dispatch::handle($context, $action);
?>