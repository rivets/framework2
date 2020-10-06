<?php
/**
 * Ajax entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 */
/**
 * The real work is all done in the Ajax class.
 */
    define('REDBEAN_MODEL_PREFIX', '\\Model\\');

    include 'class/config/framework.php';
    \Config\Framework::initialise();

    \Framework\Local::getinstance()->setup(__DIR__, TRUE, TRUE, TRUE, TRUE); // AJAX, developer mode on, load twig, load RB
    \Support\Ajax::getinstance()->handle(\Support\Context::getinstance()->setup());
?>
