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
 * Set up the debug text file
 *
 * @return void
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
 * @return void
 */
        public static function show(string $str) : void
        {
            self::setup();
            /** @psalm-suppress PossiblyNullArgument  */
            fputs(self::$fd, $str."\n");
        }
/**
 * Dump a variable - uses buffering to grab the output.
 *
 * @param mixed $vars
 *
 * @return void
 */
        public static function vdump(...$vars) : void
        {
            self::setup();
            ob_start();
            /** @psalm-suppress ForbiddenCode */
            var_dump(...$vars);
            /** @psalm-suppress PossiblyNullArgument  */
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
 * @return void
 */
        public static function head(string $str) : void
        {
            self::$hcount += 1;
            header('X-DEBUG-INFO'.self::$hcount.': '.$str);
        }
    }
?>