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
 * Add functions that implement your AJAX operations here and register them
 * in the handle method below.
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
 * Handle AJAX operations
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public function handle(Context $context)
        {
            //$this->operation('yourop', [TRUE, [['ContextName', 'RoleName']]]);
            // TRUE if login needed, then an array of roles required in form [['context name', 'role name']...] (can be empty)
/**
 * Don't change anything below here
 */
            $this->pageOrHint(self::$allowPaging, self::$allowHints);
            parent::handle($context);
        }
    }
?>
