<?php
/**
 * A class implementing logging of beans to the datbase
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020-2022 Newcastle University
 * @package Framework\Framework\Ajax\Support
 */
    namespace Framework\Ajax\Support;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * Class to log operations on beans
 */
    class BeanLog
    {
        public const int CREATE = 0;
        public const int UPDATE = 1;
        public const int DELETE = 2;
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