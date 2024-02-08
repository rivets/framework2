<?php
/**
 * Class for handling AJAX calls invoked from ajax.php.
 *
 * It assumes that RESTful ajax calls are made to {{base}}/ajax and that
 * the first part of the URL after ajax is an opcode that defines what is to be done.
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2014-2024 Newcastle University
 * @package Framework
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
 * @var array Allowed Framework operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static array $restops = [
            'bean'          => Ajax\Bean::class,
            'config'        => Ajax\Config::class,
            'fwtest'        => Ajax\FWTest::class,
            'hints'         => Ajax\Hints::class,
            'paging'        => Ajax\Paging::class,
            'pwcheck'       => Ajax\PwCheck::class,
            'shared'        => Ajax\Shared::class,
            'table'         => Ajax\Table::class,
            'tablecheck'    => Ajax\TableCheck::class,
            'tablesearch'   => Ajax\TableSearch::class,
            'toggle'        => Ajax\Toggle::class,
            'unique'        => Ajax\Unique::class,
            'uniquenl'      => Ajax\UniqueNl::class,
            'upload'        => Ajax\Upload::class,
        ];
/**
 * Return the log requirements array from the child
 *
 * @param string $beanType  The name of a bean
 */
        final public function log(string $beanType) : bool
        {
            /** @phpstan-ignore-next-line */
            return \in_array($beanType, static::$log); // @phan-suppress-current-line PhanUndeclaredStaticProperty
        }
/**
 * Return the permission requirements array from the child
 *
 * @param string $which   The permissions required
 * @param array  $system  Permissions to add
 *
 * @return array<string>
 */
        final public function permissions(string $which, array $system = []) : array
        {
            if (isset(static::$fwPermissions[$which]))
            {
                return \array_merge(static::$fwPermissions[$which], $system);  // @phan-suppress-current-line PhanUndeclaredStaticProperty
            }
            return $system;
        }
/**
 * Handle AJAX operations
 *
 * @phpcsSuppress NunoMaduro.PhpInsights.Domain.CyclomaticComplexityIsHigh
 */
        public function handle(Context $context) : void
        {
            $rest = $context->rest();
            $op = $rest[0];
            if (isset(self::$restops[$op]))
            { // a Framework Ajax operation
                $class = self::$restops[$op];
            }
            else
            {
                $class = '\\Ajax\\'.$op;
                if (!\class_exists($class))
                { // not a developer provided ajax op
                    $context->web()->bad('No such operation');
                    /* NOT REACHED */
                }
            }
            try
            {
                (new $class($context, $this))->handle();
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
            catch(\Throwable $e)
            { // any other exception - this will be a framework internal error
                $context->web()->internal($e->getMessage());
            }
        }
    }
?>