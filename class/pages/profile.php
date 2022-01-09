<?php
/**
 * Profile page class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2021 Newcastle University
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context;
/**
 * A profile page class
 */
    class Profile extends \Framework\SiteAction
    {
/**
 * Handle various profile operations /
 *
 * @param Context $context    The context object for the site
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function handle(Context $context) : array|string
        {
            return '@content/profile.twig';
        }
    }
?>