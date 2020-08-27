<?php
    namespace RedBeanPHP;

    class SimpleModel
    {
        /** @var OODBBean */
        /** @psalm-suppress MissingConstructor  */
        public $bean;
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
 * @psalm-suppress PossiblyUnusedParam
 */
        public function getmeta(string $m)
        {
            return '';
        }
/**
 * @param string $x
 * @return OODBBean
 * @psalm-suppress PossiblyUnusedParam
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function with(string $x) : OODBBean
        {
             return $this;
        }
/**
 * @param string $x
 * @return OODBBean
 * @psalm-suppress PossiblyUnusedParam
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function withCondition(string $x) : OODBBean
        {
             return $this;
        }
/**
 * @return string
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function export() : string
        {
             return '';
        }
    }
?>