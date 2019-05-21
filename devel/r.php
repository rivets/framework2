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
 * @var object
 */
    public static $dummy;
/**
 * @param string $b
 * @return object
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
 * @return object
 */
    public static function load(string $b, int $id) : \RedBeanPHP\OODBBean
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
 * @return object
 */
    public static function findOne(string $b, string $x = '', array $y = []) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }
/**
 * @param object $b
 * @return void
 */
    public static function trash($b) : void
    {
    }
/**
 * @param string $x
 * @return array
 */
    public static function inspect(string $x = '') : array
    {
        return [];
    }
}