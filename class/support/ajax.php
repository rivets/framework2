<?php
/**
 * A class that handles Ajax calls
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2018 Newcastle University
 *
 */
    namespace Support;

    use Support\Context as Context;
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
        public function yourop(Context $context)
        {
            // your code
        }
 */
/**
 * If you are using the pagination or search hinting features of the framework then you need to
 * add some appropriate vaues into these arrays.
 *
 * The key to both the array fields is the name of the bean type you are working with.
 */
/**
 * @var array   Values controlling whether or not pagination calls are allowed
 */
        private static $allowPaging = [
            // 'bean' => [TRUE, [['ContextName', 'RoleName']]] // TRUE if login needed, then an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * @var array   Values controlling whether or not search hint calls are allowed
 */
        private static $allowHints = [
            // 'bean' => [TRUE, [['ContextName', 'RoleName']]] // TRUE if login needed, then an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * @var array   Values controlling whether or not calls on the bean operation are allowed
 */
        private static $allowBean = [
            // 'bean' => [[['ContextName', 'RoleName']], [ 'bean' => [...fields...], ...] // an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * @var array   Values controlling whether or not calls on the toggle operation are allowerd
 */
        private static $allowToggle = [
            // 'bean' => [[['ContextName', 'RoleName']], [ 'bean' => [...fields...], ...]] // an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * @var array   Values controlling whether or not calls on the table operation are allowerd
 */
        private static $allowTable = [
            // 'bean' => [[['ContextName', 'RoleName']], [ 'bean', ....] // an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * Handle AJAX operations
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public function handle(Context $context) : void
        {
            //$this->operation('yourop', [TRUE, [['ContextName', 'RoleName']]]);
            // TRUE if login needed, then an array of roles required in form [['context name', 'role name']...] (can be empty)
            $this->pageOrHint(self::$allowPaging, self::$allowHints);
            $this->beanAccess(self::$allowBean, self::$allowToggle, self::$allowTable);
            parent::handle($context);
        }
    }
?>