<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms. Users should derive their own class from this to handle non-object or template
 * request.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2018 Newcastle University
 *
 */
    namespace Framework;

    use Config\Config as Config;
    use Support\Context as Context;
    use Framework\Web\Web as Web;
    use \Framework\Web\StatusCodes as StatusCodes;
/**
 * The default behaviour when a page does not exist.
 */
    class CatchAll extends Siteaction
    {
/**
 * Handle non-object or template page requests
 *
 * This just diverts to a /error page but it could also just render a 404 template here.
 * Which might be better. Needs thought.
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string|array    A template name  or an array of values
 */
	public function handle(Context $context)
	{
	    switch ($context->action())
	    {
	    case 'favicon.ico':
		$context->web()->sendfile($context->local()->assetsdir().'/favicons/favicon.ico', 'favicon.ico', 'image/x-icon');
		return '';

	    case 'robots.txt':
		return ['robot.twig', 'text/plain; charset="utf-8"', StatusCodes::HTTP_OK];

	    case 'sitemap.xml':
		$context->local()->addval('url', Config::SITEURL);
		return ['sitemap.twig', 'application/xml; charset="utf-8"', StatusCodes::HTTP_OK];

	    default:
		$context->local()->addval('page', $_SERVER['REQUEST_URI']);
		return ['@error/404.twig', Web::HTMLMIME, StatusCodes::HTTP_NOT_FOUND];
	    }
            /* NOT REACHED */
	}
    }
?>
