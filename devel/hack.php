<?php
/**
 * You can put arbitrary code below and run it
 *
 * Don't remove these lines
 */
    use \R as R;

    \R::freeze(FALSE); // unfreeze the database in case you want to add fields
    $context = \Support\Context::getinstance();
    $local = $context->local();
    $web = $context->web();
    $siteinfo = \Support\SiteInfo::getinstance();
    $log = '';
    $raw = FALSE;
    // set_time_limit(0); // use carefully - only if you know this will terminate!!
/******************************************************************************/
/*
 * Your code in here. Concatenate output to $log and it will be shown. If you want HTML output, set $raw to TRUE;
 */

/******************************************************************************/
    $local->addval(['errlog' => $log, 'raw' => $raw]);
    $local->message(\Framework\Local::MESSAGE, 'Done');
?>
