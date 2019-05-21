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
 * @return \RedBeanPHP\OODBBean
 */
        public function with(string $x) : \RedBeanPHP\OODBBean
        {
             return self::$dummy;
        }
    }
?>