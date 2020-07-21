<?php
/**
 * Class to handle the Framework AJAX tablecheck operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Framework\Exception\BadOperation;
    use \Support\Context;
/**
 * Parsley table check
 */
    class TableCheck extends Ajax
    {
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]]]; // require login, only allow Site Admins to do this
        }
/**
 * Do a parsley table check
 *
 * @param \Support\Context    $context
 *
 * @return void
 */
        final public function handle(Context $context) : void
        {
            [$name] = $context->restcheck(1);
            if (\Support\SiteInfo::tableExists($name))
            {
                $context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
    }
?>