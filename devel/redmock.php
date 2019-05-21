<?php
    namespace RedBeanPHP;
    class OODBBean
    {
        private static $dummy;
/**
 * @return int
 */
        public function getID() : int
        {
             return 0;
        }
/**
 * @param string $x
 * @return \RedBeanPHP\OODBean
 */
        public function with(string $x) : \RedBeanPHP\OODBean
        {
             return self::$dummy;
        }
    }
?>