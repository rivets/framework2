<?php
/**
 * Class to handle the Framework AJAX paging operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Paging database tables
 */
    class Paging extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            FW::PAGE  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]], []],
            FW::USER  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]], []],
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
            [$bean] = $this->restCheck(1);
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean);
            $fdt = $this->context->formdata('get');
            $order = $fdt->fetch('order', '');
            $page = $fdt->mustFetch('page');
            $pagesize = $fdt->mustFetch('pagesize');
            $res = \Support\SiteInfo::getinstance()->fetch($bean, ($order !== '' ? ('order by '.$order) : ''), [], $page, $pagesize);
            $this->context->web()->sendJSON($res);
        }
    }
?>
