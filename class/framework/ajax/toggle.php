<?php
/**
 * Class to handle the Framework AJAX toggle operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
    use \Framework\Exception\BadValue;
/**
 * Toggle a flag field in a bean
 */
    class Toggle extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            [
                [[FW::FWCONTEXT, FW::ADMINROLE]],
                [
                    FW::PAGE => [],
                    FW::USER => [],
                    FW::CONFIG => [],
                    FW::FORM => [],
                    FW::FORMFIELD => [],
                    FW::ROLECONTEXT => [],
                    FW::ROLENAME => [],
                    FW::TABLE => [],
                ],
            ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
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
        final public function handle() : void
        {
            $rest = $this->context->rest();
            if (count($rest) > 2)
            {
                [$type, $bid, $field] = $this->context->restcheck(3);
            }
            else // this is legacy
            {
                $fdt = $this->context->formdata('post');
                $type = $fdt->mustFetch('bean');
                $field = $fdt->mustFetch('field');
                $bid = $fdt->mustFetch('id');
            }
            $this->access->beanFindCheck($this->context, 'toggleperms', $type, $field);
            $bn = $this->context->load($type, (int) $bid);
            if ($type === 'user' && ctype_upper($field[0]) && $this->context->hasadmin())
            { # not simple toggling... and can only be done by the Site Administrator
                if (is_object($bn->hasrole(FW::FWCONTEXT, $field)))
                {
                    $bn->delrole(FW::FWCONTEXT, $field);
                }
                else
                {
                    $bn->addrole(FW::FWCONTEXT, $field, '', $this->context->utcnow());
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