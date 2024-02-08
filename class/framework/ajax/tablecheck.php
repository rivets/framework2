<?php
/**
 * Class to handle the Framework AJAX tablecheck operation
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2020-2024 Newcastle University
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
 */
        #[\Override]
        public function requires() : array
        {
            return [TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]]]; // require login, only allow Site Admins to do this
        }
/**
 * Do a parsley table check
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
