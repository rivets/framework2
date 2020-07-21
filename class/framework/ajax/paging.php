<?php
/**
 * Class to handle the Framework AJAX paging operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Support\Context;
/**
 * Paging database tables
 */
    class Paging extends Ajax
    {
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [FALSE, []]; // login not required
        }
/**
 * Get a page of bean values
 *
 * @param Context    $context    The context object for the site
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        final public function handle(Context $context) : void
        {
            $fdt = $context->formdata('get');
            $bean = $fdt->mustFetch('bean');
            if (!isset(self::$paging[$bean]))
            { // pagination is NOT allowed for this bean
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
            $this->access->checkPerms($context, self::$paging[$bean][1]); // make sure we are allowed
            $order = $fdt->fetch('order', '');
            $page = $fdt->mustFetch('page');
            $pagesize = $fdt->mustFetch('pagesize');
            $res = \Support\SiteInfo::getinstance()->fetch($bean, ($order !== '' ? ('order by '.$order) : ''), [], $page, $pagesize);
            $context->web()->sendJSON($res);
        }
    }
?>