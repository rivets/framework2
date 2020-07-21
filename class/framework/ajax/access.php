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
    namespace Framework\Ajax;

    use \Framework\Exception\Forbidden;
    use \Support\Context;
/**
 * Handle Ajax operations in this class
 */
    class Access
    {
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
        final public function fieldExists(string $type, string $field, bool $idok = FALSE) : bool
        {
            if (!\Support\SiteInfo::hasField($type, $field) || (!$idok && $field === 'id'))
            {
                throw new \Framework\Exception\BadValue('Bad field: '.$field);
                /* NOT REACHED */
            }
            return TRUE;
        }
/**
 * Check down an array with permissions and return the first row that is OK
 *
 * @param Context  $context  The context object
 * @param array    $perms    The array with permission info ([login?, [roles...], [fields...]
 *
 * @throws Forbidden
 * @return array
 */
        public function findRow(Context $context, array $permissions) : array
        {
            $tables = [];
            foreach ($permissions as $bpd)
            {
                /** @phpcsSuppress  PHP_CodeSniffer.CodeAnalysis.EmptyStatement */
                try
                {
                    $this->checkPerms($context, $bpd[1]); // make sure we are allowed
                    $tables[] = $bpd[2];
                }
                catch (Forbidden $e)
                {
                    NULL; // void go round and try the next item in the array
                }
            }
            if (empty($tables))
            {
                throw new Forbidden('Permission Denied');
            }
/**
 * Need to merge all the tables together. We can't use array_merge
 * since empty elements imply all fields and array_merge would overwrite empties.
 *
 * @todo Revisit the table design to be able to use some of the array functions
 */
            $merged = [];
            foreach ($tables as $t)
            {
                foreach ($t as $k => $v)
                {
                    if (isset($merged[$k]))
                    {
                        if (!empty($merged[$k]))
                        {
                            $merged[$k] = empty($v) ? [] : array_merge($merged[$k], $v);
                        }
                    }
                    else
                    {
                        $merged[$k] = $v;
                    }
                }
            }
            return $merged;
        }
/**
 * Check if a bean/field combination is allowed and the field exists and is not id
 *
 * @param array<string>   $beans
 * @param string          $bean
 * @param string          $field
 * @param bool            $idok    Allow the id field
 *
 * @throws Forbidden
 * @return bool
 */
        final public function beanCheck(array $beans, string $bean, string $field, bool $idok = FALSE) : bool
        {
            $this->fieldExists($bean, $field, $idok);
            if (!isset($beans[$bean]) || (!empty($beans[$bean]) && !in_array($field, $beans[$bean])))
            { // no permission to update this field or it doesn't exist
                throw new Forbidden('Permission denied: '.$bean.'::'.$field);
            }
            return TRUE;
        }
/**
 * Check if a bean/field combination is allowed and the field exists and is not id
 *
 * @param Context $context  The contetx object
 * @param string  $array    The array to look in
 * @param string  $bean     The bean type
 * @param string  $field    The field in the bean
 * @param bool    $idok     Allow the id field
 *
 * @throws Forbidden
 * @return bool
 */
        final public function beanFindCheck(Context $context, array $array, string $bean, string $field, bool $idok = FALSE) : bool
        {
            return $this->beanCheck($this->findRow($context, $array), $bean, $field, $idok);
        }
/**
 * Check that user has the permissions that are specified in an array
 *
 * @param Context   $context  The Context bject
 * @param array     $pairs    The permission array
 *
 * @throws Forbidden
 * @return void
 * @psalm-suppress PossiblyNullReference
 */
        final public function checkPerms(Context $context, array $pairs) : void
        {
            $user = $context->user();
            assert(!is_null($user)); // must have a user when checking
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
    }
?>