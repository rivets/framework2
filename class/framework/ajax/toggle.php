<?php
/**
 * Class to handle the Framework AJAX toggle operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Framework\Exception\BadValue;
    use \Support\Context;
/**
 * Toggle a flag field in a bean
 */
    class Toggle extends Ajax
    {
/**
 * Toggle a flag field in a bean
 *
 * Note that for Roles the toggling is more complex and involves role removal/addition rather than
 * simply changing a value.
 *
 * @internal
 * @param Context   $context    The context object for the site
 *
 * @return void
 */
        final public function handle(Context $context) : void
        {
            $rest = $context->rest();
            if (count($rest) > 2)
            {
                [$type, $bid, $field] = $context->restcheck(3);
            }
            else // this is legacy
            {
                $fdt = $context->formdata('post');
                $type = $fdt->mustFetch('bean');
                $field = $fdt->mustFetch('field');
                $bid = $fdt->mustFetch('id');
            }
            $this->access->beanFindCheck($context, 'toggleperms', $type, $field);
            $bn = $context->load($type, (int) $bid);
            if ($type === 'user' && ctype_upper($field[0]) && $context->hasadmin())
            { # not simple toggling... and can only be done by the Site Administrator
                if (is_object($bn->hasrole(FW::FWCONTEXT, $field)))
                {
                    $bn->delrole(FW::FWCONTEXT, $field);
                }
                else
                {
                    $bn->addrole(FW::FWCONTEXT, $field, '', $context->utcnow());
                }
            }
            else
            {
                $bn->$field = $bn->$field == 1 ? 0 : 1;
                R::store($bn);
            }
        }
    }
?>