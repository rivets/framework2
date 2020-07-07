<?php
 /**
  * Class for handling home pages
  *
  * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
  * @copyright 2012-2019 Newcastle University
  */
    namespace Pages;

    use \Support\Context;
/**
 * A class that contains code to implement a home page
 * @psalm-suppress UnusedClass
 */
    class Home extends \Framework\SiteAction
    {
/**
 * Handle various contact operations /
 *
 * @param Context   $context    The context object for the site
 *
 * @return string   A template name
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function handle(Context $context)
        {
            return '@content/index.twig';
        }
    }
?>
