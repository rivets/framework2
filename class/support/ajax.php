<?php
/**
 * A class that handles Ajax calls
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2019 Newcastle University
 */
    namespace Support;

/**
 * Handles Ajax Calls.
 */
    final class Ajax extends \Framework\Ajax
    {
/**
 * Add functions that implement your AJAX operations as classes in class/framework/ajax. See the sample.txt file there.
 */
/**
 * If you are using the predefined features of the Framework then you will amost certainly need to
 * add some appropriate values to support permissions
 *
 * The key to both the array fields is the name of the bean type you are working with.
 */
/**
 * @var array<array> Allowed Framework operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static $fwPermissions = [
            'bean'          => [], // [[['ContextName', 'RoleName']], [ 'bean' => [...fields...], ...]] // an array of roles required in form [['context name', 'role name']...] (can be empty)
            'hints'         => [], // 'bean' => ['field', TRUE, [['ContextName', 'RoleName']]] // TRUE if login needed, then an array of roles required in form [['context name', 'role name']...] (can be empty)
            'paging'        => [], // ['bean' => [TRUE, [['ContextName', 'RoleName']]]] array of roles required in form [['context name', 'role name']...] (can be empty)
            'pwcheck'       => [],
            'shared'        => [],
            'table'         => [], // [[['ContextName', 'RoleName']], [ 'bean', ....]] // an array of roles required in form [['context name', 'role name']...] (can be empty)
            'tablesearch'   => [], // [[['ContextName', 'RoleName']], [ 'bean' => [...fields...], ...]] // an array of roles required in form [['context name', 'role name']...] (can be empty)
            'toggle'        => [], // [[['ContextName', 'RoleName']], [ 'bean' => [...fields...], ...]] // an array of roles required in form [['context name', 'role name']...] (can be empty)
            'unique'        => [],
            'uniquenl'      => [], // ['bean' => [...fields...], ...] // an array of beans and fields that can be accessed
            'audit'         => [], // ['bean'..... A list of bean names]
        ];
/**
 * Constructor
 *
 * @param array $permissions    An array of permission sdata - see above;
 */
        public function __construct(array $fwPermissions)
        {
            parent::__construct($fwPermissions);
        }
/**
 * Handle AJAX operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return void
 */
        public function handle(Context $context) : void
        {
            parent::handle($context);
        }
    }
?>