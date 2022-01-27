<?php
/**
 * A model class for the RedBean object BeanLog
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2022 Newcastle University
 *
 */
    namespace Framework\Model;

    use \Framework\Ajax\Support\BeanLog as BL;
/**
 * A class implementing a RedBean model for BeanLog beans
 */
    class FWBeanLog extends \RedBeanPHP\SimpleModel
    {
        private static $ops = [BL::CREATE => 'Create', BL::UPDATE => 'Update', BL::DELETE => 'Delete'];

        public function operation() : string
        {
            return self::$ops[$this->bean->op];
        }

        public function updated() : string
        {
            return $this->bean->updated;
        }

        public function bean() : \RedBeanPHP\OODBBean
        {
            return \R::load($this->bean->bean, $this->bean->bid);
        }

        public function user() : \RedBeanPHP\OODBBean
        {
            return $this->bean->user;
        }
    }
?>
