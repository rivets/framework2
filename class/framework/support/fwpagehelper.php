<?php
/**
 * A helper class for the RedBean object FWPage
 *
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! This is a Framework system class - do not edit !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2022 Newcastle University
 * @package Framework\Support
 */
    namespace Framework\Support;

    use \Support\Context;
/**
 * A class implementing a RedBean model for Page beans
 */
    final class FWPageHelper
    {
/**
 * make a new FWPage object
 */
        public static function doObject(Context $context, \RedBeanPHP\OODBBean $page) : void
        {
            if (!\preg_match('/\\\\/', $page->source))
            { // no namespace so put it in \Pages
                $page->source = '\\Pages\\'.$page->source;
                \R::store($page);
            }
            $tl = \strtolower($page->source);
            $tspl = \explode('\\', $page->source);
            $base = \array_pop($tspl);
            $lbase = \strtolower($base);
            $namespace = \implode('\\', \array_filter($tspl));
            $src = \preg_replace('/\\\\/', \DIRECTORY_SEPARATOR, $tl).'.php';
            $file = $context->local()->makebasepath('class', $src);
            if (!\file_exists($file))
            { // make the file
                $fd = \fopen($file, 'w');
                if ($fd === FALSE)
                {
                    throw new \Framework\Exception\InternalError('Cannot create PHP file');
                }
                $context->local()->initRender(['twig', ['templateDir' => 'twigs'/*, 'cache' => 'twigcache'*/]]);
                \fwrite($fd, $context->local()->getRender('@util/pagesample.twig', ['pagename' => $page->name, 'namespace' => $namespace]));
                \fclose($fd);
            }
/**
 * @todo Make the render and template initialisation values a config value somewhere to detwig the system
 */
            $context->local()->initRender(['twig', ['templateDir' => 'twigs'/*, 'cache' => 'twigcache'*/]]);
            $context->local()->makeTemplate($context, ['content', $lbase.'.twig']); // make a basic twig if there is not one there already.
        }
/**
 * Handle a template page
 *
 * @todo detwig this
 */
        public static function doTemplate(Context $context, \RedBeanPHP\OODBBean $page) : void
        {
            if (!\preg_match('/\.twig$/', $page->source))
            { // doesn't end in .twig
                $page->source .= '.twig';
            }
            else
            { // sometimes there are extra .twig extensions...
                $page->source = \preg_replace('/(\.twig)+$/', '.twig', $page->source); // this removes extra .twigs .....
            }
            if (!\preg_match('/^@/', $page->source))
            {
                if (\preg_match('#/#', $page->source))
                { // has directory separator characters in it so leave it alone - may be new top-level twig directory.
                    $name = $page->source;
                }
                else
                { // no namespace so put it in @content
                    $page->source = '@content/'.$page->source;
                    $name = ['content', $page->source];
                    \R::store($page);
                }
            }
            elseif (\preg_match('%^@content/(.*)%', $page->source, $m))
            { // this is in the User twig content directory
                $name = ['content', $m[1]];
            }
            elseif (\preg_match('%@([a-z]+)/(.*)%', $page->source, $m))
            { // this is using a system twig
                $name = ['framework', $m[1], $m[2]];
            }
            else
            {
                throw new \Framework\Exception\BadValue('Not recognised');
            }
            $context->local->makeTemplate($context, $name);
        }
    }
?>