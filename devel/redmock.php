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
 * @param string $m
 * @return mixed
 */
        public function getmeta(string $m)
        {
        }
/**
 * @param string $x
 * @return \RedBeanPHP\OODBBean
 */
        public function with(string $x) : \RedBeanPHP\OODBBean
        {
             return self::$dummy;
        }
/**
 * @param string $x
 * @return \RedBeanPHP\OODBBean
 */
        public function withCondition(string $x) : \RedBeanPHP\OODBBean
        {
             return self::$dummy;
        }
    }
?>