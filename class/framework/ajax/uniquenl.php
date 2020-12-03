<?php
/**
 * Class to handle the Framework AJAX uniquenl operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Parsely unique check that does not need a login.
 */
    class UniqueNl extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            FW::USER => [ FALSE, [], ['login'] ],
        ];
/**
 * Return permission requirements
 *
 * @return array
 */
        final public function requires()
        {
            return [FALSE, []]; // does not require login
        }
/**
 * Do a parsley uniqueness check wothout requiring login
 * Send a 404 if it exists (That's how parsley works)
 *
 * @todo this call ought to be rate limited in some way!
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
