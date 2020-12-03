<?php
/**
 * Class to handle the Framework AJAX unique operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Parsely unique check that does require a login.
 */
    class Unique extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            FW::PAGE        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['name'] ],
            FW::ROLECONTEXT => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['name'] ],
            FW::ROLENAME    => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['name'] ],
            FW::USER        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['login'] ],
        ];
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [TRUE, []]; // requires login
        }
/**
 * Do a parsley uniqueness check
 * Send a 404 if it exists (That's how parsley works)
 *
 * @todo Possibly should allow for more than just alphanumeric for non-parsley queries???
 *
 * @return void
 */
        final public function handle() : void
        {
            [$bean, $field, $value] = $this->restCheck(3);
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean, $field);
            if (\R::count($bean, preg_replace('/[^a-z0-9_]/i', '', $field).'=?', [$value]) > 0)
            {
                $this->context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
            $this->context->web()->noContent();
        }
    }
?>