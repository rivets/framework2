<?php
/**
 * Contains definition of abstract Developer class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 * @subpackage SystemPages
 */
    namespace Framework\Pages;

    use \Config\Framework as FW;
    use \Support\Context;
/**
 * Class for developer hacks and helpers...
 */
    class Developer extends \Framework\SiteAction
    {
        use \Support\NoCache; // don't cache developer pages.
/**
 * Handle various admin operations /devel/xxxx
 *
 * The test for developer status is done in index.php so deos not need to be repeated here.
 *
 * @param Context  $context    The context object for the site
 *
 * @return string   A template name
 */
        public function handle(Context $context)
        {
            $tpl = '@devel/devel.twig';
            $rest = $context->rest();
            switch ($rest[0])
            {
            case 'ajax': // configure user AJAX functions
                $tpl = '@devel/ajax.twig';
                break;

            case 'hack': // execute some code.
                /** @psalm-suppress UnresolvableInclude */
                include $context->local()->makebasepath('devel', 'hack.php');
                break;

            case 'test':
                $test = new \Framework\Support\Test();
                if (count($rest) > 1)
                {
                    if (method_exists($test, $rest[1]))
                    {
                        $tpl = $test->{$rest[1]}($context);
                    }
                    else
                    {
                        $context->web()->bad();
                        /* NOT REACHED */
                    }
                }
                break;
            }
            return $tpl;
        }

    }
?>
