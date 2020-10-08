<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms. Users should derive their own class from this to handle non-object or template
 * request.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2020 Newcastle University
 * @package Framework
 * @subpackage SystemPages
 */
    namespace Framework\Pages;

    use \Framework\Web\StatusCodes;
    use \Framework\Web\Web;
    use \Support\Context;
/**
 * The default behaviour when a page does not exist.
 */
    class CatchAll extends \Framework\SiteAction
    {
/**
 * Handle non-object or template page requests
 *
 * This just diverts to a /error page but it could also just render a 404 template here.
 * Which might be better. Needs thought.
 *
 * @param Context  $context     The context object for the site
 *
 * @return string|array     A template name or array
 */
        public function handle(Context $context)
        {
            $tpl = '';
            $local = $context->local();
            switch ($context->action())
            {
            case 'favicon.ico':
                $context->web()->sendfile($context->local()->assetsdir().'/favicons/favicon.ico', 'favicon.ico', 'image/x-icon');
                break;

            case 'robots.txt':
                $local->addval('url', $local->configval('siteurl'));
                return ['@info/robots.twig', 'text/plain; charset="utf-8"', StatusCodes::HTTP_OK];

            case 'sitemap.xml':
                $local->addval('url', $local->configval('siteurl'));
                return ['@info/sitemap.twig', 'application/xml; charset="utf-8"', StatusCodes::HTTP_OK];

            default:
                $local->addval('page', $_SERVER['REQUEST_URI']);
                \Framework\Dispatch::basicSetup($context, 'error');
                return ['@error/404.twig', Web::HTMLMIME, StatusCodes::HTTP_NOT_FOUND];
            }
            return $tpl;
        }
    }
?>
