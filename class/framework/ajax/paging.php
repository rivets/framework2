<?php
/**
 * Class to handle the Framework AJAX paging operation
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2020-2024 Newcastle University
 * @package Framework\Framework\Ajax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Paging database tables
 */
    class Paging extends Ajax
    {
/**
 * @var array These permissions let the Site Admin manipulate the Framework internal tables. The first element is a
 *            bool indicating if a login is required, the second is a list of ['Context', 'Role'] pairs that a user
 *            must have. The third element is a list of accessible field names.
 */
        private static array $permissions = [
            FW::PAGE  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]], []],
            FW::USER  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]], []],
        ];
/**
 * Return permission requirements
 *
 * First element is a bool indicating of login is required. The second element is a list of ['Context', 'Role']
 * that the user must have.
 */
        #[\Override]
        public function requires() : array
        {
            return [FALSE, []]; // login not required
        }
/**
 * Get a page of bean values
 *
 * @throws \Framework\Exception\Forbidden
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
