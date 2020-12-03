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
        private $class = '';
/**
 *  make a new one /ajax/bean/KIND/
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        private function post(string $bean, array $rest, bool $log) : void
        {
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean);
/*
 * @psalm-suppress RedundantCondition
 * @psalm-suppress ArgumentTypeCoercion
 */
            if (!method_exists($this->class, 'add'))
            { // operation not supported
                throw new BadOperation('Cannot add a '.$bean);
            }
            /** @psalm-suppress InvalidStringClass */
            $id = $this->class::add($this->context)->getID();
            if ($log)
            {
                BeanLog::mklog($this->context, BeanLog::CREATE, $bean, $id, '*', NULL);
            }
            $this->context->web()->created($id); // 201 return code
        }
/**
 * update a field   /ajax/bean/KIND/ID/FIELD/[FN]
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function patch(string $bean, array $rest, bool $log) : void
        {
            [$id, $field] = $rest;
            $more = $rest[2] ?? NULL;
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean, $field);
            $bn = $this->context->load($bean, (int) $id, TRUE);
            $old = $bn->{$field};
            $bn->{$field} = empty($more) ? $this->context->formdata('put')->mustFetch('value') : $bn->{$more[0]}($this->context->formdata('put')->mustFetch('value'));
            R::store($bn);
            if ($log)
            {
                BeanLog::mklog($this->context, BeanLog::UPDATE, $bean, $bn->getID(), $field, $old);
            }
            $this->context->web()->noContent();
        }
/**
 * Map put onto patch
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function put(string $bean, array $rest, bool $log) : void
        {
            $this->patch($bean, $rest, $log);
        }
/**
 * DELETE /ajax/bean/KIND/ID/
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function delete(string $bean, array $rest, bool $log) : void
        {
            $this->checkAccess($this->context->user(), $this->controller->permissions(static::class, self::$permissions), $bean);
            $id = $rest[0] ?? 0; // get the id from the URL
            if ($id <= 0)
            {
                throw new \Framework\Exception\BadValue('Missing value');
            }
            $bn = $this->context->load($bean, (int) $id);
            if ($log)
            {
                BeanLog::mklog($this->context, BeanLog::DELETE, $bean, (int) $id, '*', json_encode($bn->export()));
            }
            /**
             * @psalm-suppress RedundantCondition
             * @psalm-suppress ArgumentTypeCoercion
             */
            if (method_exists($this->class, 'delete')) // call the clean-up function if it has one
            {
                $bn->delete($this->context);
            }
            R::trash($bn);
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
            [$bean, $rest] = $this->restCheck(1);
            $method = strtolower($this->context->web()->method());
            if (!method_exists(self::class, $method))
            {
                throw new \Framework\Exception\BadOperation($method.' is not supported');
            }
            /** @psalm-suppress UndefinedConstant */
            $this->class = REDBEAN_MODEL_PREFIX.$bean;
            /**
             * @psalm-suppress RedundantCondition
             * @psalm-suppress ArgumentTypeCoercion
             */
            if (method_exists($this->class, 'canAjaxBean'))
            {
                /** @psalm-suppress InvalidStringClass */
                $this->class::canAjaxBean($this->context, $method);
            }
            $this->{$method}($bean, $rest, $this->controller->log($bean));
        }
    }
?>
