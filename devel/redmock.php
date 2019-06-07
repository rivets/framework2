<?php
    namespace RedBeanPHP;
    class SimpleModel
    {
        /** @var \RedBeanPHP\OODBBean */
        /** @psalm-suppress MissingConstructor  */
        public $bean;
    }

    class OODBBean
    {
        /** @var \RedBeanPHP\OODBBean */
        private static $dummy;

        public function __get(string $name)
        {
        }
        /** @psalm-suppress MissingParamType */
        public function __set(string $name, $value): void {}
        /** @psalm-suppress MissingParamType */
        public function __call($function, $args)
        {
        }
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
            return '';
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
/**
 * @return string
 */
        public function export() : string
        {
             return '';
        }
    }
?>