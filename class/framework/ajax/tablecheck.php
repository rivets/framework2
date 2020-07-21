<?php
/**
 * Class to handle the Framework AJAX tablecheck operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Parsley table check
 */
    class TableCheck extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            FW::CONFIG      => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::FORM        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::FORMFIELD   => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::PAGE        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::PAGEROLE    => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::ROLECONTEXT => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::ROLENAME    => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::TABLE       => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::USER        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
        ];
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
            [$name] = $this->context->restcheck(1);
            if (\Support\SiteInfo::tableExists($name))
            {
                $this->context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
    }
?>