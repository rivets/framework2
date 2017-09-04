<?php
/**
 * A class that contains code to handle any requests for the homepage
 *
 * This is a dummy and shouldnt be used if the homepage is simply a twig.
 * If the homepage needs to do some handling then this is the place to put it.
 * You would need to change the entry in the pages table to make it an
 * Object of class Home.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
/**
 * Support / or /home
 */
    class Home extends \Framework\Siteaction
    {
/**
 * Handle home operations /
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle($context)
        {
            return 'index.twig';
        }
    }
?>
