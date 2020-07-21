<?php
/**
 * Class to handle the Framework AJAX uniquenl operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Support\Context;
/**
 * Parsely unique check that does not need a login.
 */
    class UniqueNl extends Ajax
    {
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [FALSE, []]; // does not require login
        }
/**
 * Do a parsley uniqueness check wothout requiring login
 * Send a 404 if it exists (That's how parsley works)
 *
 * @param Context    $context
 *
 * @todo this call ought to be rate limited in some way!
 * @todo Possibly should allow for more than just alphanumeric for non-parsley queries???
 *
 * @return void
 */
        private function uniquenl(Context $context) : void
        {
            [$bean, $field, $value] = $context->restcheck(3);
            $this->access->beanCheck(self::$uniquenlperms, $bean, $field);
            if (\R::count($bean, preg_replace('/[^a-z0-9_]/i', '', $field).'=?', [$value]) > 0)
            {
                $context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
    }
?>