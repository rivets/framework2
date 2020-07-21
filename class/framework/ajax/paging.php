<?php
/**
 * Class to handle the Framework AJAX paging operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

/**
 * Paging database tables
 */
    class Paging extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            FW::PAGE  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            FW::USER  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            // 'beanname' => [TRUE, [['ContextName', 'RoleName']]]
            // TRUE if login needed, an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
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
        final public function handle() : void
        {
            $fdt = $this->context->formdata('get');
            $bean = $fdt->mustFetch('bean');
            $paging = $this->controller->permissions('paging');
            if (!isset($paging[$bean]))
            { // pagination is NOT allowed for this bean
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
            $this->access->checkPerms($context, $paging[$bean][1]); // make sure we are allowed
            $order = $fdt->fetch('order', '');
            $page = $fdt->mustFetch('page');
            $pagesize = $fdt->mustFetch('pagesize');
            $res = \Support\SiteInfo::getinstance()->fetch($bean, ($order !== '' ? ('order by '.$order) : ''), [], $page, $pagesize);
            $context->web()->sendJSON($res);
        }
    }
?>