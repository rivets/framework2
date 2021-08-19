<?php
/**
 * Contains definition of abstract Developer class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
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
 */
        public function handle(Context $context) : array|string
        {
            $tpl = '@devel/devel.twig';
            $rest = $context->rest();
            switch ($rest[0])
            {
            case 'ajax': // configure user AJAX functions
                $tpl = '@devel/ajax.twig';
                break;

            case 'csp': // configure CSP values
                $context->web()->initCSP(); // this will set up the necessary data if it hasn't already been done.
                $csp = [];
                foreach (\R::find(FW::CSP, 'order by type,host') as $cd)
                {
                    $csp[$cd->type][] = $cd;
                }
                $context->local()->addval([
                    'csp' => $csp,
                    'force' => $context->formdata('get')->exists('force'),
                ]);
                $tpl = '@devel/csp.twig';
                break;

            case 'hack': // execute some code.
                /** @psalm-suppress UnresolvableInclude */
                include $context->local()->makebasepath('devel', 'hack.php');
                break;

            case 'test':
                $context->local()->addval('action', 'test'); // this is a hack to make the menu bar light up right
                $test = new \Framework\Support\Test();
                if (count($rest) > 1)
                {
                    if (!\method_exists($test, $rest[1]))
                    {
                        $context->web()->bad();
                        /* NOT REACHED */
                    }
                    $tpl = $test->{$rest[1]}($context);
                }
                break;
            }
            return $tpl;
        }

    }
?>