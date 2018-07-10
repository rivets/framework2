<?php
/**
 * Ajax entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2017 Newcastle University
 */
/**
 * The real work is all done in the Ajax class.
 */
    define('REDBEAN_MODEL_PREFIX', '\\Model\\');

    include 'class/config/framework.php';
    \Config\Framework::initialise();

    // Ajax on, debug on, load twig, load RB
    $ld = \Framework\Local::getinstance()->setup(__DIR__, TRUE, TRUE, TRUE, TRUE); # setup the Local singleton
    \Support\Ajax::getinstance()->handle(\Support\Context::getinstance()->setup());
?>
