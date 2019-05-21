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
    public static function dispense(string $b) : object
    {
        return new stdClass;
    }

    public static function store(object $b) : int
    {
        return 56;
    }

    public static function load(string $b, int $id) : object
    {
        return new stdClass;
    }

    public static function find(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }

    public static function findAll(string $b, string $x = '', array $y = []) : array
    {
        return [];
    }

    public static function findOne(string $b, string $x = '', array $y = []) : object
    {
        return new stdClass;
    }

    public static function trash(object) : void
    {
    }

    public static function inspect(string $x = '') : array
    {
        return [];
    }
}