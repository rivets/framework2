<?php
/**
 * Base class for AJax operations
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2020-2024 Newcastle University
 * @package Framework\Framework\Ajax
 */
    namespace Framework\Ajax;

    use \Framework\Exception\Forbidden;
    use \Support\Context;
/**
 * Ajax operation base class
 */
    abstract class Ajax
    {
        protected \Framework\Ajax $controller;
        protected Context $context;
/**
 * Constructor
 */
        public function __construct(Context $context, \Framework\Ajax $controller)
        {
            $this->context = $context;
            $this->controller = $controller;
            [$login, $perms] = $this->requires();
            if ($login && !$context->hasUser())
            {
                throw new Forbidden('Access denied');
            }
            $this->checkPerms($context->user(), $perms);
        }
/**
 * Check that a bean has a field. Do not allow id field to be manipulated unless flag is set.
 *
 * @param string $type   The type of bean
 * @param string $field  The field name
 * @param bool   $idok   Allow the id field to be tested
 *
 * @throws \Framework\Exception\BadValue
 */
        final protected function fieldExists(string $type, string $field, bool $idok = FALSE) : bool
        {
            if (!\Support\SiteInfo::hasField($type, $field) || (!$idok && $field === 'id'))
            {
                throw new \Framework\Exception\BadValue('Bad field: '.$field);
            }
            return TRUE;
        }
/**
 * Check access to a bean
 *
 * @throws Forbidden
 */
        final protected function checkAccess(?\RedBeanPHP\OODBBean $user, array $permissions, string $beanType, string $field = '', bool $idOK = FALSE) : void
        {
            if (isset($permissions[$beanType]))
            { // there are some permissions
                $access = $permissions[$beanType];
                if (\is_object($user) || !$access[0])
                { // either we have a user or no login required
                    $checks = \count($access) == 2 ? $access[1] : [ [$access[1], $access[2]] ];
                    foreach ($checks as $check)
                    {
                        $this->checkPerms($user, $check[0]); // check user plays the right roles
                        if ($field === '' || empty($check[1]) || (\in_array($field, $check[1]) && ($field != 'id' || $idOK)))
                        {
                            return;
                        }
                    }
                }
            }
            throw new Forbidden('Permission denied: '.$beanType);
        }
/**
 * Check that user has the permissions that are specified in an array
 *
 * @throws Forbidden
 * @psalm-suppress PossiblyNullReference
 */
        private function checkPerms(?\RedBeanPHP\OODBBean $user, array $pairs) : void
        {
            if (!empty($pairs) && $user === NULL)
            { // you can't have permissions without a user
                throw new Forbidden('Permission denied');
            }
            foreach ($pairs as $role)
            {
                if (\is_array($role[0]))
                { // $this is an OR
                    if (!\array_reduce($role, function(bool $carry, array $pair) use ($user) {
                        return $carry ? TRUE : \is_object($user->hasRole($pair[0], $pair[1]));
                    }, FALSE))
                    {
                        throw new Forbidden('Permission denied');
                    }
                }
                elseif (!\is_object($user->hasRole($role[0], $role[1])))
                {
                    throw new Forbidden('Permission denied');
                }
            }
        }
/**
 * Check URL string for n parameter values and pull them out
 *
 * Returns the parameter values in an array indexed from 0 with last parameter, anything left in an array
 * The value in $rest[0] is assumed to be an opcode so we always start at $rest[1]
 *
 * @param int $count  The number of parameters to check for
 *
 * @throws \Framework\Exception\ParameterCount
 */
        protected function restCheck(int $count) : array
        {
            $rest = $this->context->rest();
            if (\count($rest) <= $count) // there is always the AJAX op in there as well as its parameters
            {
                throw new \Framework\Exception\ParameterCount('Missing parameter');
            }
            $res = \array_slice($rest, 1, $count);
            $res[] = \array_slice($rest, $count + 1); // return anything left - there might be optional parameters.
            return $res;
        }
/**
 * Return permission requirements
 */
        public function requires() : array
        {
            return [TRUE, []]; // default to requiring login but no specific context/role
        }
/**
 * Handle AJAX operations
 */
        abstract public function handle() : void;
    }
?>