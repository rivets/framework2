<?php
/**
 * A class that contains code to implement Multi nested static pages
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context;
/**
 * Provide support for a nested static page structure
 * @psalm-suppress UnusedClass
 */
    class Multi extends \Framework\SiteAction
    {
/**
 * Handles static pages that are nested in depth /multi/level/page
 *
 * @param Context   $context    The context object for the site
 *
 * @return string   A template name
 */
        public function handle(Context $context) : array|string
        {
            $action = $context->action();
            $rest = $action.'/'.\implode(\DIRECTORY_SEPARATOR, $context->rest());
            if (!file_exists($context->local()->basedir().'/twigs/'.$rest.'.twig'))
            {
                $context->web()->notfound();
            }
            return $rest.'.twig';
        }
    }
?>
