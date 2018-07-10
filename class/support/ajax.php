<?php
/**
 * A class that handles Ajax calls
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2018 Newcastle University
 *
 */
    namespace Support;
/**
 * Handles Ajax Calls.
 */
    class Ajax extends \Framework\Ajax
    {
/**
 * Any Ajax for your system goes in here.
 *
 *********  Make sure that you call the parent handle method for anything you are not handling yourself!! ***********
 */
/*
        public function yourop($context)
        {
            // your code
        }
 */
/**
 * Handle AJAX operations
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public function handle($context)
        {
            //$this->operation('yourop', [TRUE, [['ContextName', 'RoleName']]]); // TRUE if login needed, array is a list of roles required  in form ['context name', 'role name']
            parent::handle($context);
        }
    }
?>
