<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms. 
 *
 * Note that 
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2019 Newcastle University
 *
 */
    namespace Pages;

    use Support\Context as Context;
/**
 * The default behaviour when a page does not match in the database.
 */
    class NoPage extends \Framework\Pages\CatchAll
    {
/**
 * Handle non-object or template page requests
 *
 * This just diverts to a /error page but it could also just render a 404 template here.
 * Which might be better. Needs thought.
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string|string[]	A template name
 */
        public function handle(Context $context)
        {
            return parent::handle($context);
        }
    }
?>
