<?php
/**
 * A class to allow some setup for all pages
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2020 Newcastle University
 *
 */
    namespace Support;

/**
 * A class that supports setup code for all pages
 *
 * @psalm-suppress UnusedClass
 */
    class Setup
    {
/**
 * For user code
 *
 * @param Context    $context  The context object
 * @param object              $page     An object about the page about to be rendered
 *
 * @return void
 */
        public static function preliminary(Context $context, $page) : void
        {
            // Any code you wish to be run before ever page
        }
    }
?>