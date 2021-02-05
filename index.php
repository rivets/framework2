<?php
/**
 * Main entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 */
/**
 * See the information at
 *
 * @link https://catless.ncl.ac.uk/framework/
 */
    include 'class/config/framework.php';
    \Config\Framework::initialise();
    \Config\Config::setup(); // add default headers etc. - anything that the user choses to add to the code.

    $local = \Framework\Local::getinstance()->setup(__DIR__, FALSE, TRUE,
        ['twig', ['templateDir' => 'twigs'/*, 'cache' => 'twigcache'*/]], TRUE); // Not Ajax, developer mode on, renderer, load RB
    $context = \Support\Context::getinstance()->setup();

    $local->enabledebug(); // turn debugging on

    $action = $context->action();
    if ($action === '')
    { // default to home if there is nothing
        $action = 'home';
    }
    \Framework\Dispatch::handle($context, $action);
?>