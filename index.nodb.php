<?php
/**
 * Main entry point of the system
 *
 * The framework assumes a self contained directory structure for a site like this :
 *
 * DOCUMENT_ROOT
 *    /sitename         This can be omitted if the site is the only one present and at the root
 *        /assets
 *            /css      CSS files
 *            /i18n     Any internationalisation files you may need
 *            /images   Image files
 *            /js       JavaScript
 *            /...      Any other stuff that can be accessed without intermediation through PHP
 *        /class        PHP class definition files named "classname.php"
 *        /class/admin  PHP class files for the administrative functions provided by the framework
 *        /errors       Files used for generating error pages.
 *        /lib          PHP files containing non-class definitions
 *        /twigcache    If twigcacheing is on this is where it caches
 *        /twigs        TWIG template files go in here
 *        /twigs/admin  Twig files for the admin support of the framework
 *        /vendor       If you are using composer then it puts stuff in here.
 *
 * The .htaccess file directs
 *         anything in /assets to be served by Apache.
 *         anything beginning "ajax" to be called directly i.e. ajax.php (this may or may not be useful - remove it if not)
 *         everything else gets passed into this script where it treats the URL thus:
 *                 /                    =>  /home and then
 *                 /action/r/e/st/      =>  Broken down in Context class. AN action and an array of parameters.
 *                 Query strings and/or post fields are in the $_ arrays as normal.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    include 'class/support/framework.php';
    Framework::initialise();

    $local = Local::getinstance()->setup(__DIR__, FALSE, TRUE, TRUE, FALSE); # Not Ajax, debug on, load twig, no RB
    $context = Context::getinstance()->setup($local);

/*
 * The valid actions for the site, i.e. valid first part of the URL
 *
 * If the value is false then just the template is rendered and no object called up
 */
    $pages = array(
        'about'     => array(Siteaction::TEMPLATE, 'about.twig'),
        'admin'     => array(Siteaction::OBJECT, 'Admin'),
#        'confirm'  => array(Siteaction::OBJECT, 'UserLogin'),
#        'forgot'   => array(Siteaction::OBJECT, 'UserLogin'),
        'home'      => array(Siteaction::TEMPLATE, 'index.twig'),
#        'login'    => array(Siteaction::OBJECT, 'Userlogin'),
#        'logout'   => array(Siteaction::OBJECT, 'Userlogin'),
#        'register' => array(Siteaction::OBJECT, 'Userlogin'),
    );

    $action = $context->action();
    if (!isset($pages[$action]))
    { # oops, we've been asked to do something that we don't do
        Web::getinstance()->notfound('No such page'); # a basic 404 - the page should be much more helpful
        # DOES NOT RETURN
    }

    $local->addval('context', $context);
    $local->addval('page', $action);
    $local->addval('siteinfo', new SiteInfo($local));

    switch ($pages[$action][0])
    {
    case Siteaction::OBJECT:
        $op = new $pages[$action][1];
        $tpl = $op->handle($context);
        break;

    case Siteaction::TEMPLATE:
        $tpl = $pages[$action][1];
        break;

    default :
        Web::getinstance()->internal('Weird error');
    }

    ob_start('ob_gzhandler');
    $local->render($tpl);
    ob_end_flush();
?>
