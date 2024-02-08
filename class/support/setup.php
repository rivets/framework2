<?php
/**
 * A class to allow some setup for all pages
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2019-2024 Newcastle University
 * @package Framework\Support
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
 * @used-by \Framework\Dispatch
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public static function preliminary(Context $context, \RedBeanPHP\OODBBean|\stdClass $page) : void
        {
            // Any code you wish to be run before ever page
        }
    }
?>
