<?php
/**
 * Class to handle the Framework AJAX toggle operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Toggle a flag field in a bean
 */
    class Toggle extends Ajax
    {
/**
 * @var array
 */
        private static array $permissions = [
            FW::CONFIG      => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['local', 'fixed', 'defer', 'async'] ],
            FW::FORM        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['multipart'] ],
            // FW::FORMFIELD   => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::PAGE        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['needlogin', 'mobileonly', 'active', 'needajax', 'needfwutils', 'needparsley', 'neededitable'] ],
            FW::ROLECONTEXT => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['fixed'] ],
            FW::ROLENAME    => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['fixed'] ],
            // FW::TABLE       => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
            FW::TEST        => [ TRUE, [[FW::FWCONTEXT, FW::DEVELROLE]], ['tog'] ], // table does not always exist
            FW::USER        => [ TRUE, [[FW::FWCONTEXT, FW::ADMINROLE]], ['active', 'confirm', FW::ADMINROLE, FW::DEVELROLE] ], // the latter are special see below
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
                [$beanType, $bid, $field] = $this->restCheck(3);
            }
            else // this is legacy
            {
                $fdt = $this->context->formdata('post');
                $beanType = $fdt->mustFetch('bean');
                $field = $fdt->mustFetch('field');
                $bid = $fdt->mustFetch('id');
            }
            $log = $this->controller->log($beanType);
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $beanType, $field);
            $bn = $this->context->load($beanType, (int) $bid);
            if ($beanType === FW::USER && ctype_upper($field[0]) && $this->context->hasadmin())
            { // not simple toggling... and can only be done by the Site Administrator
                if (is_object($bn->hasrole(FW::FWCONTEXT, $field)))
                {
                    $bn->delrole(FW::FWCONTEXT, $field);
                    $res = 0;
                }
                else
                {
                    $bn->addrole(FW::FWCONTEXT, $field, '', $this->context->utcnow());
                    $res = 1;
                }
/**
 * @ToDo Work out how we might add this to the log if someone is wanting to log changes to the User bean!
 */
            }
            else
            {
                $ov = $bn->$field;
                $res = $bn->$field = $ov == 1 ? 0 : 1;
                \R::store($bn);
                if ($log)
                {
                    BeanLog::mklog($this->context, BeanLog::UPDATE, $beanType, $bn->getID(), $field, $ov);
                }
            }
            echo $res;
        }
    }
?>