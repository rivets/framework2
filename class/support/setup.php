<?php
/**
 * A class to allow some setup for all pages
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019 Newcastle University
 *
 */
    namespace Support;
    use Support\Context as Context;
/**
 * A class implementing a RedBean model for Form beans
 */
    class Setup
    {
/**
 * For user code
 *
 * @param \Support\Context    $context  The context object
 * @param object    $page     An object about the page about to be rendered
 *
 * @return void
 */
        public static function preliminary(Context $context, $page)
        {
            // Any code you wish to be run before ever page
        }
    }
?>