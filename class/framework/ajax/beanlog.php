<?php
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
        public static function mklog(int $op, string $bean, int $id, string $field, $value) : void
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