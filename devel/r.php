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

    public static function setup(string $a, string $b, string $c) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }

    public static function testConnection() : bool
    {
        return FALSE;
    }

    public static function close() : void
    {
    }

    public static function nuke() : void
    {
    }

    public static function wipe(string $table) : void
    {
    }

    public static function dispense(string $b) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }

    public static function store($b) : int
    {
        return 56;
    }

    public static function load(string $b, int $id) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }

    public static function loadforupdate(string $b, int $id) : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }

    public static function find(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }

    public static function findAll(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }

    public static function findOne(string $b, string $x = '', array $y = []) : ?\RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }

    public static function findOrCreate(string $b, array $y = [], string $s = '') : \RedBeanPHP\OODBBean
    {
        return self::$dummy;
    }

    public static function count(string $b, string $x = '', array $y = []) : int
    {
        return 0;
    }

    public static function findCollection(string $b, string $x = '', array $y = []) : \RedBeanPHP\BeanCollection
    {
        return new \RedBeanPHP\BeanCollection();
    }

    public static function findMulti(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }

    public static function getCell(string $b,  array $y = [])
    {
        return '';
    }

    public static function exec(string $b,  array $y = []) : int
    {
        return 0;
    }

    public static function trash($b) : void
    {
    }

    public static function trashAll(array $b) : void
    {
    }

    public static function isodatetime($x = ''): string
    {
        return '';
    }

    public static function isodate($x = ''): string
    {
        return '';
    }

    public static function inspect(string $x = '') : array
    {
        return [];
    }

    public static function freeze(bool $x) : void
    {
    }

    public static function usePartialBeans(bool $x)
    {
    }

    public static function getRedBean() : \RedBeanPHP\OODB
    {
        return new \RedBeanPHP\OODB(new \RedBeanPHP\QueryWriter());
    }

    public static function getPDO() : ?\RedBeanPHP\PDO
    {
        return NULL;
    }
}