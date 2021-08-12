<?php
/**
 * A class implementing logging of beans to the datbase
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020-2021 Newcastle University
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
 * @param Context $context
 * @param int $op
 * @param \RedBeanPHP\OODBBean $bean
 * @param string $field
 * @param mixed $value
 */
        public static function mklog(Context $context, int $op, \RedBeanPHP\OODBBean $bean, string $field, $value) : void
        {
            $lg = \R::dispense('beanlog');
            $lg->user = $context->user();
            $lg->updated = $context->utcnow();
            $lg->op = $op;
            $lg->bean = $bean->getMeta('type');
            $lg->bid = $bean->getID();
            $lg->field = $field;
            $lg->value = $value;
            \R::store($lg);
        }
    }
?>