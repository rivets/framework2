<?php
/**
 * A class that contains a last resort handler for pages that are not found through the normal
 * mechanisms. Users should derive their own class from this to handle non-object or template
 * request.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016-2021 Newcastle University
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
 */
        public function handle(Context $context) : array|string
        {
            $tpl = '';
            $local = $context->local();
            switch ($context->action())
            {
            case 'favicon.ico':
                $context->web()->sendfile($local->assetsdir().'/favicons/favicon.ico', 'favicon.ico', 'image/x-icon');
                break;

            case 'robots.txt':
                $local->addval('url', $local->configval('siteurl'));
                return ['@info/robots.twig', 'text/plain; charset="utf-8"', StatusCodes::HTTP_OK];

            case 'sitemap.xml':
                $local->addval('url', $local->configval('siteurl'));
                return ['@info/sitemap.twig', 'application/xml; charset="utf-8"', StatusCodes::HTTP_OK];

            case '.well-known':
                $rest = $context->rest();
                switch ($rest[0])
                {
                case 'gpc.json':
                    $context->web()->sendJSON((object) ['gpc' => TRUE, 'version' => 1]);
                    break;
                default:
                    $context->web()->notfound();
                    /* NOT REACHED */
                }
                break;

            default:
                $local->addval('page', $context->web()->request());
                \Framework\Dispatch::basicSetup($context, 'error');
                return ['@error/404.twig', Web::HTMLMIME, StatusCodes::HTTP_NOT_FOUND];
            }
            return $tpl;
        }
    }
?>
