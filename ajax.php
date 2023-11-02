<?php
/**
 * Ajax entry point of the system
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 *
 * The real work is all done in the Ajax class.
 */
    include 'class/config/framework.php';
    \Config\Framework::initialise();

    \Framework\Local::getinstance()->setup(__DIR__, TRUE, TRUE, [], TRUE); // AJAX, developer mode on, renderer, load RB
    \Support\Ajax::getinstance()->handle(\Support\Context::getinstance()->setup());
?>