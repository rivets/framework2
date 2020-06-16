<?php
/**
 * You can put arbitrary code in here and run it
 */
    \R::freeze(FALSE); // unfreeze the database in case you want to add fields
    $context = \Support\Context::getinstance();
    $siteinfo = \Support\SiteInfo::getinstance();
    $log = '';
    $raw = FALSE;
/******************************************************************************/
/*
 * Your code in here. Concatenate output to $log and it will be shown.
 */

/******************************************************************************/
    $context->local()->addval(['errlog' => $log, 'raw' => $raw]);
    \Support\Context::getinstance()->local()->message(\Framework\Local::MESSAGE, 'Done');
?>
