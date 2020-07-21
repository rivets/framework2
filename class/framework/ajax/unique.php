<?php
/**
 * Class to handle the Framework AJAX unique operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Support\Context;
/**
 * Parsely unique check that does require a login.
 */
    class Unique extends Ajax
    {
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
 * @param Context    $context
 *
 * @todo Possibly should allow for more than just alphanumeric for non-parsley queries???
 *
 * @return void
 */
        private function unique(Context $context) : void
        {
            [$bean, $field, $value] = $context->restcheck(3);
            $this->access->beanCheck('uniqueperms', $bean, $field);
            if (\R::count($bean, preg_replace('/[^a-z0-9_]/i', '', $field).'=?', [$value]) > 0)
            {
                $context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
    }
?>