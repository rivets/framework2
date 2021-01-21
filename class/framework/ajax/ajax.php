<?php
/**
 * Base class for AJax operations
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemAjax
 */
    namespace Framework\Ajax;

    use \Framework\Exception\Forbidden;
    use \Support\Context;
/**
 * Ajax operation base class
 */
    abstract class Ajax
    {
/**
 * @var Ajax
 */
        protected $controller;
/**
 * @var Context
 */
        protected $context;
/**
 * Constructor
 */
        public function __construct(Context $context, \Support\Ajax $controller)
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
 * Check that a bean has a field. Do not allow id field to be manipulated.
 *
 * @param string    $type    The type of bean
 * @param string    $field   The field name
 * @param bool      $idok    Allow the id field
 *
 * @throws \Framework\Exception\BadValue
 * @return bool
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
 * @param ?\RedBeanPHP\OODBBean  $user
 * @param array                 $permissions
 * @param string                $bean
 * @param string                $field
 *
 * @throws Forbidden
 * @return void
 */
        final protected function checkAccess(?\RedBeanPHP\OODBBean $user, array $permissions, string $bean, string $field = '', bool $idOK = FALSE) : void
        {
            if (isset($permissions[$bean]))
            { // there are some permissions
                $access = $permissions[$bean];
                if (is_object($user) || !$access[0])
                { // either we have a user or no login required
                    $checks = count($access) == 2 ? $access[1] : [ [$access[1], $access[2]] ];
                    foreach ($checks as $check)
                    {
                        $this->checkPerms($user, $check[0]); // check user plays the right roles
                        if ($field === '' || empty($check[1]) || (in_array($field, $check[1]) && ($field != 'id' || $idOK)))
                        {
                            return;
                        }
                    }
                }
            }
            throw new Forbidden('Permission denied: '.$bean);
        }
/**
 * Check that user has the permissions that are specified in an array
 *
 * @param ?\RedBeanPHP\OODBBean  $user   The current user or NULL
 * @param array                  $pairs  The permission array
 *
 * @throws Forbidden
 * @return void
 * @psalm-suppress PossiblyNullReference
 */
        private function checkPerms(?\RedBeanPHP\OODBBean $user, array $pairs) : void
        {
            if (!empty($pairs) && $user == NULL)
            { // you can't have permissions without a user
                throw new Forbidden('Permission denied');
            }
            foreach ($pairs as $rcs)
            {
                if (is_array($rcs[0]))
                { // this is an OR
                    foreach ($rcs as $orv)
                    {
                        if (is_object($user->hasRole($orv[0], $orv[1])))
                        {
                            continue 2;
                        }
                    }
                    throw new Forbidden('Permission denied');
                }
                if (!is_object($user->hasRole($rcs[0], $rcs[1])))
                {
                    throw new Forbidden('Permission denied');
                }
            }
        }
/**
 * Check URL string for n parameter values and pull them out
 *
 * The value in $rest[0] is assumed to be an opcode so we always start at $rest[1]
 *
 * @param int   $count  The number to check for
 *
 * @throws \Framework\Exception\ParameterCount
 *
 * @return array The parameter values in an array indexed from 0 with last parameter, anything left in an array
 */
        protected function restCheck(int $count) : array
        {
            $rest = $this->context->rest();
            if (count($rest) <= $count) // there is always the AJAX op in there as well as its parameters
            {
                throw new \Framework\Exception\ParameterCount();
            }
            $res = array_slice($rest, 1, $count);
            $res[] = array_slice($rest, $count+1); // return anything left - there might be optional parameters.
            return $res;
        }
/**
 * Return permission requirements
 *
 * @return array
 */
        public function requires()
        {
            return [TRUE, []]; // default to requiring login but no specific context/role
        }
/**
 * Handle AJAX operations
 *
 * @return void
 */
        abstract public function handle() : void;
    }
?>