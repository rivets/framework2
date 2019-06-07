<?php
/**
 * Contains definition of Debug class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2019 Newcastle University
 */
    namespace Framework;
/**
 * A class that handles various debugging related things
 */
    class Debug
    {
/**
 * @var ?Resource    The file descriptor
 */
        private static $fd = NULL;
/**
 * @var integer     header count
 */
        private static $hcount = 0;
/**
 * Set up the file
 */
        private static function setup() : void
        {
            if (self::$fd === NULL)
            {
                self::$fd = fopen(Local::getinstance()->makebasepath('debug', 'debug.txt'), 'a');
            }
        }
/**
 * Display a string
 *
 * @param string    $str
 *
 * @todo use the new ... stuff in PHP 5.6 to allow many parameters
 *
 * @return void
 */
        public static function show(string $str) : void
        {
            self::setup();
            fputs(self::$fd, $str."\n");
        }
/**
 * Dump a variable - uses buffering to grab the output.
 *
 * @param mixed $vars
 *
 * @todo use the new ... stuff in PHP 5.6 to allow many parameters
 *
 * @return void
 */
        public static function vdump(...$vars) : void
        {
            self::setup();
            ob_start();
            /** @psalm-suppress ForbiddenCode */
            var_dump(...$vars);
            fputs(self::$fd, ob_get_clean());
        }
/**
 * Flush the output stream
 *
 * @return void
 */
        public static function flush() : void
        {
            if (self::$fd !== NULL)
            {
                fflush(self::$fd);
            }
        }
/**
 * Display a string in an X-DEBUG-INFO header
 *
 * @param string    $str
 *
 * @todo use the new ... stuff in PHP 5.6 to allow many parameters
 *
 * @return void
 */
        public static function head(string $str) : void
        {
            self::$hcount += 1;
            header('X-DEBUG-INFO'.self::$hcount.': '.$str);
        }
    }
?>