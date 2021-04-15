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
 * @return string|array     A template name or an array with more complex information
 */
        public function handle(Context $context)
        {
            return '@content/profile.twig';
        }
    }
?>