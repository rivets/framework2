<?php
/**
 * A class that contains code to implement Multi nested static pages
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
/**
 * Provide support for a nested static page structure
 */
    class Multi extends \Framework\Siteaction
    {
/**
 * Handles static pages that are nested in depth /multi/level/page
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle($context)
        {
            $action = $context->action();
            $rest = $action.'/'.implode(DIRECTORY_SEPARATOR, $context->rest());
            if (!file_exists($context->local()->basedir().'/twigs/'.$rest.'.twig'))
            {
                $context->web()->notfound();
            }
            return $rest.'.twig';
        }
    }
?>
