<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms. 
 *
 * Note that 
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
/**
 * The default behaviour when a page does not match in the database.
 */
    class NoPage extends \Framework\CatchAll
    {
/**
 * Handle non-object or template page requests
 *
 * This just diverts to a /error page but it could also just render a 404 template here.
 * Which might be better. Needs thought.
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    return parent::handle($context);
	}
    }
?>
