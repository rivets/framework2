<?php
/**
 * A class that handles Ajax calls
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2019 Newcastle University
 * @package Framework
 */
    namespace Support;

    use \Framework\Ajax as FWAjax;
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
 * add some appropriate values to support permissions for the operation named as the key.
 * Not all Framework AJAX operations are available.
 */
/**
 * @var array<array> Allowed Framework operation codes. Values indicate:
 *                   'bean' => [ Must Login (TRUE/FALSE) , [['ContextName', 'RoleName']...], [...field names or empty for all...] ]
 *
 *                   Empty fields array means all fields (except id which is always special)
 *                   Evaluation of multiple context/role pairs is a logical AND.
 *                   If you want an OR then you need to group the pairs to be ORed in yet another nested array.
 */
        protected static $fwPermissions = [
            FWAjax\Bean::class          => [],
            FWAjax\Hints::class         => [],
            FWAjax\Paging::class        => [],
            FWAjax\PwCheck::class       => [],
            FWAjax\Shared::class        => [],
            FWAjax\Table::class         => [],
            FWAjax\TableSearch::class   => [],
            FWAjax\Toggle::class        => [],
            FWAjax\Unique::class        => [],
            FWAjax\UniqueNl::class      => [],
        ];
/**
 * @var array<string> A list of bean names for which logging is required
 */
        protected static $log = []; // ['bean'..... A list of bean names]
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
