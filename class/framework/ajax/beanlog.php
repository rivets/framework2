<?php
/**
 * A class implementing logging of beans to the datbase
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Support\Context;
/**
 * Class to log operations on beans
 */
    class BeanLog
    {
        public const CREATE = 0;
        public const UPDATE = 1;
        public const DELETE = 2;
/**
 * make log entry
 *
 * @param int $op
 * @param string $bean
 * @param int $id
 * @param string $field
 * @param mixed $value
 *
 * @return void
 */
        public static function mklog(Context $context, int $op, string $bean, int $id, string $field, $value) : void
        {
            $lg = \R::dispense('beanlog');
            $lg->user = $context->user();
            $lg->updated = $context->utcnow();
            $lg->op = $op;
            $lg->bean = $bean;
            $lg->bid = $id;
            $lg->field = $field;
            $lg->value = $value;
            \R::store($lg);
        }
    }
?>