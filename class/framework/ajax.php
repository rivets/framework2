<?php
/**
 * Class for handling AJAX calls invoked from ajax.php.
 *
 * It assumes that RESTful ajax calls are made to {{base}}/ajax and that
 * the first part of the URL after ajax is an opcode that defines what is to be done.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2020 Newcastle University
 */
    namespace Framework;

    use \Framework\Exception;
    use \Support\Context;
/**
 * Handle Ajax operations in this class
 */
    class Ajax
    {
        use \Framework\Utility\Singleton;
/**
 * @var array<array> Allowed Framework operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static $restops = [
            'bean',          //=> [TRUE,   []], // permission checks are done in the bean function
            'config',        //=> [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            'hints',         //=> [FALSE,  []], // permission checks are done in the hints function
            'paging',        //=> [FALSE,  []], // permission checks are done in the paging function
            'pwcheck',       //=> [TRUE,   []], // permission checks are done in the pwcheck function
            'shared',        //=> [TRUE,   []], // permission checks are done in the shared function
            'table',         //=> [TRUE,   []], // permission checks are done in the table function
            'tablecheck',    //=> [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            'tablesearch',   //=> [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            'toggle',        //=> [TRUE,   []], // permission checks are done in the toggle function
            'unique',        //=> [TRUE,   []], // test if a bean field value is unique
            'uniquenl',      //=> [FALSE,  []], // unique test with no login - used at least by user registration form
        ];
/**
 * Handle AJAX operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return void
 */
        public function handle(Context $context) : void
        {
            if ($context->action() == 'ajax')
            { # REST style AJAX call
                $rest = $context->rest();
                $op = $rest[0];
                $class = "\\Ajax\\".$op;;
                if (isset(self::$restops[$op]))
                { # a valid Framework Ajax operation
                    $class = '\Framework'.$class;
                }
                elseif (!class_exists($class))
                { # not a developer provided ajax op
                    $context->web()->bad('No such operation');
                    /* NOT REACHED */
                }
                try
                {
                    (new $class($context))->handle($context);
                }
                catch(Exception\Forbidden $e)
                {
                    $context->web()->noaccess($e->getMessage());
                }
                catch(Exception\BadValue |
                      Exception\BadOperation |
                      Exception\MissingBean |
                      Exception\ParameterCount $e)
                {
                    $context->web()->bad($e->getMessage());
                }
                catch(\Exception $e)
                { // any other exception - this will be a framework internal error
                    $context->web()->internal($e->getMessage());
                }
            }
        }
    }
?>