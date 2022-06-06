<?php
/**
 * A model class for the RedBean object BeanLog
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2022 Newcastle University
 */
    namespace Framework\Model;

    use \Config\Framework as FW;
    use \Framework\Ajax\Support\BeanLogOps as BL;
/**
 * A class implementing a RedBean model for BeanLog beans
 */
    final class FWBeanLog extends \RedBeanPHP\SimpleModel
    {
/**
 * Return the Operation name
 */
        public function operation() : string
        {
            /** @phpstan-ignore-next-line */
            return BL::label($this->bean->op);
        }
/**
 * Return the updated field
 */
        public function updated() : string
        {
            return $this->bean->updated;
        }
/**
 * Return the associated bean
 */
        public function bean() : \RedBeanPHP\OODBBean
        {
            return \R::load($this->bean->bean, $this->bean->bid);
        }
/**
 * Return the associated user
 */
        public function user() : \RedBeanPHP\OODBBean
        {
            return $this->bean->{FW::USER};
        }
    }
?>