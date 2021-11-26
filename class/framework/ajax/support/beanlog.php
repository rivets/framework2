<?php
/**
 * A class implementing logging of beans to the datbase
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020-2021 Newcastle University
 * @package Framework\Framework\Ajax
 */
    namespace Framework\Ajax\Support;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * Class to log operations on beans
 *
 * @todo use an enum instead of const when PHP 8.1 comes out
 */
    class BeanLog
    {
        public const CREATE = 0;
        public const UPDATE = 1;
        public const DELETE = 2;
/**
 * make log entry
 *
 * @param $op          The operation (see constants above)
 * @param $bean        The bean being changed
 * @param $field       The field being changed
 * @param mixed $value The value used in the change
 */
        public static function mklog(Context $context, int $op, \RedBeanPHP\OODBBean $bean, string $field, $value) : void
        {
            $lg = \R::dispense(FW::BEANLOG);
            $lg->user = $context->user();       // who changed it
            $lg->updated = $context->utcnow();  // when
            $lg->op = $op;                      // how they changed it
            $lg->bean = $bean->getMeta('type'); // the bean type
            $lg->bid = $bean->getID();          // the bean id
            $lg->field = $field;                // the field changed
            $lg->value = (string) $value;       // the previous value
            \R::store($lg);
        }
    }
?>