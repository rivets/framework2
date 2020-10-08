<?php
/**
 * Class to handle the Framework AJAX shared operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
/**
 * Operate on RedBean shared lists
 */
    class Shared extends Ajax
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
        ]; //          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
/**
 * Carry out operations on RB shared lists
 *
 * @param \Support\Context    $context The context object
 *
 * @throws \Framework\Exception\BadOperation
 * @return void
 */
        final public function handle() : void
        {

            [$b1, $id1, $b2, $id2] = $this->restCheck(4);
            $bn1 = $this->context->load($b1, (int) $id1);
            $bn2 = $this->context->load($b2, (int) $id2);
            $perms = $this->controller->permissions(static::class, self::$permissions);
            $this->checkAccess($this->context->user(), $perms, $bn1->getMeta('type')); // check we can access both beans
            $this->checkAccess($this->context->user(), $perms, $bn2->getMeta('type'));
            switch ($this->context->web()->method())
            {
            case 'POST': // make a new share /ajax/shared/KIND1/id1/KIND2/id2
                $bn1->noload()->{'shared'.ucfirst($b2).'List'}[] = $bn2;
                \R::store($bn1);
                break;

            case 'DELETE': // /ajax/shared/KIND1/id1/KIND2/id2
                unset($bn1->{'shared'.ucfirst($b2).'List'}[$bn2->getID()]);
                \R::store($bn1);
                break;

            case'PUT':
            case 'PATCH':
            case 'GET':
            default:
                throw new \Framework\Exception\BadOperation($this->context->web()->method().' not supported');
            }
        }
    }
?>
