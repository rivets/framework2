<?php
    namespace RedBeanPHP;

    class SimpleModel
    {
        /** @var OODBBean */
        /** @psalm-suppress MissingConstructor  */
        public $bean;
    }
    class PDO
    {
        /** @var OODBBean */
        /** @psalm-suppress MissingConstructor  */
        public $bean;
    }
    class QueryWriter
    {
        /** @var OODBBean */
        /** @psalm-suppress MissingConstructor  */
        public $bean;
    }
    class BeanCollection
    {
/**
 * @return ?\RedBeanPHP\OODBBean
 */
        public function next() : ?\RedBeanPHP\OODBBean
        {
            return new \RedBeanPHP\OODBBean;
        }
    }

    class OODBBean
    {
        /** @var OODBBean */
        private static $dummy;
        /** @psalm-suppress PossiblyUnusedParam */
        public function __get(string $name)
        {
        }
/**
 * @psalm-suppress PossiblyUnusedParam
 * @psalm-suppress MissingParamType
 */
        public function __set(string $name, $value): void {}
        /** @psalm-suppress PossiblyUnusedParam */
        /** @psalm-suppress MissingParamType */
        public function __call($function, $args)
        {
        }

        public function getID() : int
        {
             return 0;
        }

        public function equals(?\RedBeanPHP\OODBBean $bn) : bool
        {
             return FALSE;
        }
/**
 * @return mixed
 * @psalm-suppress PossiblyUnusedParam
 */
        public function getmeta(string $m)
        {
            return '';
        }
/**
 * @psalm-suppress PossiblyUnusedParam
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function with(string $x) : OODBBean
        {
             return $this;
        }
/**
 * @psalm-suppress PossiblyUnusedParam
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function withCondition(string $x) : OODBBean
        {
             return $this;
        }
/**
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function export() : string
        {
             return '';
        }
    }
?>