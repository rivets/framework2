<?php
/**
 * Class to handle the Framework AJAX tablecheck operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
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
        final public function handle() : void
        {
            [$name] = $this->restCheck(1);
            if (\Support\SiteInfo::tableExists($name))
            {
                $this->context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
    }
?>
