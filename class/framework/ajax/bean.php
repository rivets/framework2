<?php
/**
 * Class to handle the Framework AJAX bean operation
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Config\Framework as FW;
    use \Framework\Exception\BadOperation;
    use \R;
/**
 * Operations on beans
 */
    class Bean extends Ajax
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
 * @var string
 */
        private $model = '';
/**
 * Generate a no content or call the ajaxResult method on a bean if it exists in its Model
 *
 * @param \RedBean\OODBBean $bean       The bean
 * @param string            $method     The method used to get here
 * @param string            $op         The ajax op that was invoked
 *
 * @return void
 */
        private function ajaxResult(\RedBeanPHP\OODBBean $bean, string $method)
        {
/*
 * @psalm-suppress RedundantCondition
 * @psalm-suppress ArgumentTypeCoercion
 */
            if (method_exists($this->model, 'ajaxResult'))
            {
                $bean->ajaxResult($this->context, $method, 'bean');
            }
            elseif ($method == 'post')
            { // this is a creation of a bean
                $this->context->web()->created($bean->getID()); // 201 return code
            }
            else
            {
                $this->context->web()->noContent();
            }
        }

/**
 *  make a new one /ajax/bean/KIND/
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        private function post(string $beanType, array $rest, bool $log) : void
        {
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $beanType);
/*
 * @psalm-suppress RedundantCondition
 * @psalm-suppress ArgumentTypeCoercion
 */
            if (!method_exists($this->model, 'add'))
            { // operation not supported
                throw new BadOperation('Cannot add a '.$beanType);
            }
            /** @psalm-suppress InvalidStringClass */
            $bean = $this->model::add($this->context);
            if ($log)
            {
                BeanLog::mklog($this->context, BeanLog::CREATE, $bean, $id, '*', NULL);
            }
            $this->ajaxResult($bean, 'post');
        }
/**
 * update a field   /ajax/bean/KIND/ID/FIELD/[FN]
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function patch(string $beanType, array $rest, bool $log) : void
        {
            [$id, $field] = $rest;
            $more = $rest[2] ?? NULL;
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $beanType, $field);
            $bean = $this->context->load($beanType, (int) $id, TRUE);
            $old = $bean->{$field};
            $bean->{$field} = empty($more) ? $this->context->formdata('put')->mustFetch('value') : $bean->{$more[0]}($this->context->formdata('put')->mustFetch('value'));
            R::store($bean);
            if ($log)
            {
                BeanLog::mklog($this->context, BeanLog::UPDATE, $bean, $field, $old);
            }
            $this->ajaxResult($bean, 'patch');
        }
/**
 * Map put onto patch
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function put(string $beanType, array $rest, bool $log) : void
        {
            $this->patch($beanType, $rest, $log);
        }
/**
 * DELETE /ajax/bean/KIND/ID/
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function delete(string $beanType, array $rest, bool $log) : void
        {
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $beanType);
            $id = $rest[0] ?? 0; // get the id from the URL
            if ($id <= 0)
            {
                throw new \Framework\Exception\BadValue('Missing value');
            }
            $bean = $this->context->load($beanType, (int) $id);
            if ($log)
            {
                BeanLog::mklog($this->context, BeanLog::DELETE, $bean, '*', json_encode($bn->export()));
            }
            R::trash($bean); // If there is a delete function in the model it will get called automatically.
            $this->context->web()->noContent();
        }
/**
 * Carry out operations on beans
 *
 * @throws BadOperation
 * @throws \Framework\Exception\BadValue
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        final public function handle() : void
        {
            [$beanType, $rest] = $this->restCheck(1);
            $method = strtolower($this->context->web()->method());
            if (!method_exists(self::class, $method))
            {
                throw new \Framework\Exception\BadOperation($method.' is not supported');
            }
            /** @psalm-suppress UndefinedConstant */
            $this->model = '\\Model\\'.$beanType;
            /**
             * @psalm-suppress RedundantCondition
             * @psalm-suppress ArgumentTypeCoercion
             */
            if (method_exists($this->model, 'canAjaxBean'))
            { // permission checking for methods exists for this bean type
                /** @psalm-suppress InvalidStringClass */
                $this->model::canAjaxBean($this->context, $method);
            }
            $this->{$method}($beanType, $rest, $this->controller->log($beanType));
        }
    }
?>