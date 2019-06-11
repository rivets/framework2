<?php

/**
 * R-Facade (for Composer)
 *
 * If you use Composer you don't use the rb.php file which
 * has the R-facade, so here is a separate, namespaced R-facade for
 * those that prefer this.
 *
 * An alternative option might be to alias RedBeanPHP/Facade.
 *
 * @file    RedBeanPHP/R.php
 * @author  Simirimia
 * @license BSD/GPLv2
 *
 */

class R
{
/**
 * @var \RedBeanPHP\OODBBean
 */
    public static $dummy;
/**
 * @param string $a
 * @param string $b
 * @param string $c
 * @return \RedBeanPHP\OODBBean
 */
    public static function setup(string $a, string $b, string $c) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }
/**
 * @return void
 */
    public static function close() : void
    {
    }
/**
 * @param string $b
 * @return \RedBeanPHP\OODBBean
 */
    public static function dispense(string $b) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }
/**
 * @param object $b
 * @return int
 */
    public static function store($b) : int
    {
        return 56;
    }
/**
 * @param string $b
 * @param int $id
 * @return \RedBeanPHP\OODBBean
 */
    public static function load(string $b, int $id) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }
/**
 * @param string $b
 * @param int $id
 * @return \RedBeanPHP\OODBBean
 */
    public static function loadforupdate(string $b, int $id) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }
/**
 * @param string $b
 * @param string $x
 * @param array $y
 * @return array
 */
    public static function find(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }
/**
 * @param string $b
 * @param string $x
 * @param array $y
 * @return array
 */
    public static function findAll(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }
/**
 * @param string $b
 * @param string $x
 * @param array $y
 * @return ?\RedBeanPHP\OODBBean
 */
    public static function findOne(string $b, string $x = '', array $y = []) : ?\RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }
/**
 * @param string $b
 * @param string $x
 * @param array $y
 * @return int
 */
    public static function count(string $b, string $x = '', array $y = []) : int
    {
        return 0;
    }
/**
 * @param string $b
 * @param string $x
 * @param array $y
 * @return array
 */
    public static function findCollection(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }
/**
 * @param string $b
 * @param string $x
 * @param array $y
 * @return array
 */
    public static function findMulti(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }
/**
 * @param string $b
 * @param array $y
 * @return mixed
 */
    public static function getCell(string $b,  array $y = [])
    {
        return '';
    }
/**
 * @param object $b
 * @return void
 */
    public static function trash($b) : void
    {
    }
/**
 * @param object[] $b
 * @return void
 */
    public static function trashAll(array $b) : void
    {
    }
/**
 * @param int|string $x
 * @return string
 */
    public static function isodatetime($x = ''): string
    {
        return '';
    }
/**
 * @param int|string $x
 * @return string
 */
    public static function isodate($x = ''): string
    {
        return '';
    }
/**
 * @param string $x
 * @return array
 */
    public static function inspect(string $x = '') : array
    {
        return [];
    }
/**
 * @param bool $x
 * @return void
 */
    public static function freeze(bool $x) : void
    {
    }
}