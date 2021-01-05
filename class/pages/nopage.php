<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2020 Newcastle University
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context;
/**
 * The default behaviour when a page does not match in the database.
 * @psalm-suppress UnusedClass
 */
    class NoPage extends \Framework\Pages\CatchAll
    {
/**
 * Handle non-object or template page requests
 *
 * @param Context   $context    The context object for the site
 *
 * @return string|array<string>     A template name
 * @phpcsSuppress PHP_CodeSniffer.Standards.Generic.CodeAnalysis.UselessOverridingMethod
 */
        public function handle(Context $context)
        {
            /*--- Your code goes here ---*/
            return parent::handle($context);
        }
    }
?>
