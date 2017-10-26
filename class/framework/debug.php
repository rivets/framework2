<?php
/**
 * Contains definition of Debug class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2017 Newcastle University
 */
    namespace Framework;
/**
 * A class that handles various debugging related things
 */
    class Debug
    {
/**
 * @var Resource    The file descriptor
 */
        private static $fd = NULL;
/**
 * Set up the file
 */
        private static function setup()
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
        public static function show($str)
        {
            self::setup();
            fputs(self::$fd, $str."\n");
        }
/**
 * Dump a variable - uses buffering to grab the output.
 *
 * @param mixed    $var
 *
 * @todo use the new ... stuff in PHP 5.6 to allow many parameters
 *
 * @return void
 */
        public static function vdump($var)
        {
            self::setup();
            ob_start();
            var_dump($var);
            fputs(self::$fd, ob_get_clean());
        }
/**
 * Flush the output stream
 *
 * @return void
 */
        public static function flush()
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
        public static function head($str)
        {
            \Framework\Web::getinstance()->addheader('X-DEBUG-INFO', $str);
        }

    }
?>
