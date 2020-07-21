<?php
/**
 * Class to handle the Framework AJAX bean operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Ajax;

    use \Framework\Exception\BadOperation;
    use \R;
    use \Support\Context;
/**
 * Operations on beans
 */
    class Bean extends Ajax
    {
/**
 * Carry out operations on beans
 *
 * @param \Support\Context    $context The context object
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final public function handle(Context $context) : void
        {
            $beans = $this->access->findRow($context, 'beanperms');
            $rest = $context->rest();
            $bean = $rest[1];
            if (!isset($beans[$bean]))
            {
                throw new \Framework\Exception\Forbidden('Permission denied: '.$bean);
            }
            $log = in_array($bean, self::$audit);
            $method = $context->web()->method();
            /** @psalm-suppress UndefinedConstant */
            $class = REDBEAN_MODEL_PREFIX.$bean;
            /**
             * @psalm-suppress RedundantCondition
             * @psalm-suppress ArgumentTypeCoercion
             */
            if (method_exists($class, 'canAjaxBean'))
            {
                /** @psalm-suppress InvalidStringClass */
                $class::canAjaxBean($context, $method);
            }
            switch ($method)
            {
            case 'POST': // make a new one /ajax/bean/KIND/
                /**
                 * @psalm-suppress RedundantCondition
                 * @psalm-suppress ArgumentTypeCoercion
                 */
                if (method_exists($class, 'add'))
                {
                    /** @psalm-suppress InvalidStringClass */
                    $id = $class::add($context)->getID();
                    if ($log)
                    {
                        BeanLog::mklog($context, BeanLog::CREATE, $bean, $id, '*', NULL);
                    }
                    echo $id;
                }
                else
                { // operation not supported
                    throw new BadOperation('Cannot add a '.$bean);
                }
                break;

            case 'PATCH':
            case 'PUT': // update a field   /ajax/bean/KIND/ID/FIELD/[FN]
                [$bean, $id, $field, $more] = $context->restcheck(3);
                $this->access->beanCheck($beans, $bean, $field);
                $bn = $context->load($bean, (int) $id, TRUE);
                $old = $bn->$field;
                $bn->$field = empty($more) ? $context->formdata('put')->mustFetch('value') : $bn->{$more[0]}($context->formdata('put')->mustFetch('value'));
                R::store($bn);
                if ($log)
                {
                    BeanLog::mklog($context, BeanLog::UPDATE, $bean, $bn->getID(), $field, $old);
                }
                break;

            case 'DELETE': // /ajax/bean/KIND/ID/
                $id = $rest[2] ?? 0; // get the id from the URL
                if ($id <= 0)
                {
                    throw new \Framework\Exception\BadValue('Missing value');
                }
                $bn = $context->load($bean, (int) $id);
                if ($log)
                {
                    BeanLog::mklog($context, BeanLog::DELETE, $bean, (int) $id, '*', json_encode($bn->export()));
                }
                /**
                 * @psalm-suppress RedundantCondition
                 * @psalm-suppress ArgumentTypeCoercion
                 */
                if (method_exists($class, 'delete')) // call the clean-up function if it has one
                {
                    $bn->delete($context);
                }
                R::trash($bn);
                break;

            case 'GET':
            default:
                throw new BadOperation($method.' not supported');
            }
        }
    }
?>