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

    use \Support\Context;
/**
 * Handle Ajax operations in this class
 */
    abstract class Ajax
    {
        use \Framework\Utility\Singleton;
/**
 * @var array<array> Allowed Framework operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static $restops = [
            'bean',
            'config',
            'hints',
            'paging',
            'pwcheck',
            'shared',
            'table',
            'tablecheck',
            'tablesearch',
            'toggle',
            'unique',
            'uniquenl',
        ];
/**
 * Return the log requirements array from the child
 *
 * @param string $bean  The name of a bean
 *
 * @return bool
 */
        final public function log(string $bean) : bool
        {
            return in_array($bean, static::$log);
        }
/**
 * Return the permission requirements array from the child
 *
 * @param string $which The permissions required
 *
 * @return array<string>
 */
        final public function permissions(string $which, array $system = []) : array
        {
            return array_merge(static::$permissions[$which], $system);
        }
/**
 * Handle AJAX operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return void
 */
        public function handle(Context $context) : void
        {
            $rest = $context->rest();
            $op = $rest[0];
            $class = "\\Ajax\\".$op;
            if (in_array($op, self::$restops))
            { # a Framework Ajax operation
                $class = '\Framework'.$class;
            }
            elseif (!class_exists($class))
            { # not a developer provided ajax op
                $context->web()->bad('No such operation');
                /* NOT REACHED */
            }
            try
            {
                (new $class($context, $this))->handle($context);
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
?>