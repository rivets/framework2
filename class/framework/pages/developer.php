<?php
/**
 * Contains definition of abstract Developer class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2017 Newcastle University
 */
    namespace Framework\Pages;
    
    use \Support\Context as Context;
/**
 * Class for developer hacks and helpers...
 */
    Class Developer extends \Framework\SiteAction
    {
/**
 * Handle various admin operations /devel/xxxx
 *
 * The test for developer status is done in index.php so deos not need to be repeated here.
 *
 * @param \Support\Context	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle(Context $context)
        {
            $tpl = '@devel/devel.twig';
            $rest = $context->rest();
            switch ($rest[0])
            {
            case 'assert': // failed assertion
                assert(TRUE == FALSE);
                break;

            case 'hack': # execute some code.
                \R::freeze(FALSE); // turn off freezing so that you can fiddle with the database....
                /** @psalm-suppress UnresolvableInclude */
                include $context->local()->makebasepath('devel', 'hack.php');
                break;

            case 'test': # generate a test page
                $context->local()->message(\Framework\Local::ERROR, 'Error 1');
                $context->local()->message(\Framework\Local::ERROR, 'Error 2');
                $context->local()->message(\Framework\Local::WARNING, 'Warning 1');
                $context->local()->message(\Framework\Local::WARNING, 'Warning 2');
                $context->local()->message(\Framework\Local::MESSAGE, 'Message 1');
                $context->local()->message(\Framework\Local::MESSAGE, 'Message 2');
                $tpl = '@devel/test.twig';
                break;

            case 'fail': # this lets you test error handling
                $x = 2 / 0;
                break;

            case 'throw': # this lets you test exception handling
                throw new \Exception('Unhandled Exception Test');

            case 'mail' : # this lets you test email sending
                /** @psalm-suppress PossiblyNullPropertyFetch */
                $foo = mail($context->user()->email, 'test', 'test');
                $context->local()->message(\Framework\Local::MESSAGE, 'sent');
                break;
            }
            return $tpl;
        }

    }
?>
