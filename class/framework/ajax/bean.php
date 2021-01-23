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
    use \Framework\Exception\BadValue;
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
 * Generate a no content, created or call the ajaxResult method on a bean if it exists in its Model
 *
 * @param \RedBean\OODBBean $bean       The bean
 * @param string            $method     The method used to get here
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
 * @param string        $beanType  The bean type
 * @param array<string> $rest      The rest of the URL
 * @param bool          $log       If TRUE then log the changes
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
                BeanLog::mklog($this->context, BeanLog::CREATE, $bean, '*', NULL);
            }
            $this->ajaxResult($bean, 'post');
        }
/**
 * update a field   /ajax/bean/KIND/ID/FIELD/[FN]
 *
 * If FIELD has the value * then the formdata has several inputs named by field names, if it is a field
 * name then the form contains a single field called value
 *
 * @param string        $beanType  The bean type
 * @param array<string> $rest      The rest of the URL
 * @param bool          $log       If TRUE then log the changes
 * @param string        $method    patch or put - only needed because we are sharing put and patch because of bad routers.
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function patch(string $beanType, array $rest, bool $log, string $method = 'patch') : void
        {
            $perms = $this->controller->permissions(static::class, self::$permissions);
            [$id, $field] = $rest;
            $fdt = $this->context->formdata('put');
            $more = $rest[2] ?? NULL;
            $bean = $this->context->load($beanType, (int) $id, TRUE);
            $old = [];
            foreach ($field == '*' ? $fdt->fetchAll() : [$field => $fdt->mustFetch('value')] as $fname => $value)
            {
                if (is_array($value))
                { // we are looking at the form here so make sure there is no attempt to pass an array value
                    throw new BadValue('Arrays are not supported');
                }
                $this->checkAccess($this->context->user(), $perms, $beanType, $fname); // can we access this field?
                if (!\Support\SiteInfo::hasField($beanType, $fname))
                { // we are looking at the form here so make sure there is no attempt to pass an array value
                    throw new BadValue($beanType.' has no such field as '.$fname);
                }
                $old[$fname] = $bean->{$fname};
                $bean->{$fname} = is_null($more) ? $value : $bean->{$more[0]}($value);
            }
            R::store($bean);
            if ($log)
            {
                foreach ($old as $of => $ov)
                {
                    BeanLog::mklog($this->context, BeanLog::UPDATE, $beanType, $bean->getID(), $of, $ov);
                }
            }
            $this->ajaxResult($bean, $method);
        }
/**
 * Map put onto patch
 *
 * @param string        $beanType  The bean type
 * @param array<string> $rest      The rest of the URL
 * @param bool          $log       If TRUE then log the changes
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function put(string $beanType, array $rest, bool $log) : void
        {
            $this->patch($beanType, $rest, $log, 'patch');
        }
/**
 * DELETE /ajax/bean/KIND/ID/
 *
 * @param string        $beanType  The bean type
 * @param array<string> $rest      The rest of the URL
 * @param bool          $log       If TRUE then log the changes
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
                throw new BadValue('Missing value');
            }
            $bean = $this->context->load($beanType, (int) $id);
            if ($log)
            {
                BeanLog::mklog($this->context, BeanLog::DELETE, $bean, '*', json_encode($bean->export()));
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