<?php
/**
 * Class to handle the Framework AJAX shared operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
    use \Framework\Exception\BadValue;
/**
 * Operate on RedBean shared lists
 */
    class Shared extends Ajax
    {
/**
 * @var array
 */
        private static $permissions = [
            [ [[FW::FWCONTEXT, FW::ADMINROLE]], [] ]
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

            [$b1, $id1, $b2, $id2] = $this->context->restcheck(4);
            $bn1 = $this->context->load($b1, (int) $id1);
            $bn2 = $this->context->load($b2, (int) $id2);
            $beans = $this->access->findRow($this->context, $this->controller->permissions('shared'));
/**
 * @todo This check is not right as the array format is slightly different for sharedperms
 *       Fix when this gets properly implemented.
 */
            $this->access->beanCheck($beans, $bn1->getMeta('type'), '');
            $this->access->beanCheck($beans, $bn2->getMeta('type'), '');
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