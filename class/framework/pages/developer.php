<?php
/**
 * Contains definition of abstract Developer class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2017 Newcastle University
 */
    namespace Framework\Pages;
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
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    if ($context->hasdeveloper())
	    {
            $tpl = 'support/devel.twig';
            $rest = $context->rest();
            switch ($rest[0])
            {
                    case 'hack': # execute some code.
                        \R::freeze(FALSE); // turn off freezing so that you can fiddle with the database....
                        include $context->local()->makebasepath('devel', 'hack.php');
                        break;
    
                    case 'fail': # this lets you test error handling
                        $x = 2 / 0;
                        break;
    
                    case 'throw': # this lets you test exception handling
                        throw new \Exception('Unhandled Exception Test');
    
            case 'mail' : # this lets you test email sending
                $foo = mail($context->user()->email, 'test', 'test');
                $context->local()->message(\Framework\Local::MESSAGE, 'sent');
                break;
/*
            case 'errlog' : # this will show you the contents of the PHP error log file.
                $context->local()->addval('errlog', file_get_contents(Config::PHPLOG));
                exit;
    
            case 'clearlog' :
                fclose(fopen(Config::PHPLOG, 'w'));
                $context->local()->message(Local::MESSAGE, 'Log Cleared');
                break;
*/
            }
	    }
	    else
	    {
                $context->web()->noaccess();
	    }
	    return $tpl;
	}

    }
?>
