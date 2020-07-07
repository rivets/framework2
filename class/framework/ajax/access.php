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

    use \Config\Framework as FW;
    use \R;
    use \Support\Context;
/**
 * Handle Ajax operations in this class
 */
    class Access
    {
/**
 * @var array<array> Permissions array for bean acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $beanperms = [
            [
                [[FW::FWCONTEXT, FW::ADMINROLE]],
                [
                    FW::PAGE        => [],
                    FW::USER        => [],
                    FW::CONFIG      => [],
                    FW::FORM        => [],
                    FW::FORMFIELD   => [],
                    FW::PAGEROLE    => [],
                    FW::ROLECONTEXT => [],
                    FW::ROLENAME    => [],
                    FW::TABLE       => [],
                ],
            ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * @var array<string> Permissions array for creating an audit log.
 */
        private static $audit = [
//          ['BeanName'...]]
        ];
/**
 * @var array<array> Permissions array for creating RedBean shares. This helps allow non-site admins use the AJAX bean functions
 */
        private static $sharedperms = [
            [ [[FW::FWCONTEXT, FW::ADMINROLE]], [] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * @var array<array> Permissions array for toggle acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $toggleperms = [
            [
                [[FW::FWCONTEXT, FW::ADMINROLE]],
                [
                    FW::PAGE => [],
                    FW::USER => [],
                    FW::CONFIG => [],
                    FW::FORM => [],
                    FW::FORMFIELD => [],
                    FW::ROLECONTEXT => [],
                    FW::ROLENAME => [],
                    FW::TABLE => [],
                ],
            ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * @var array<array> Permissions array for table acccess.
 */
        private static $tableperms = [
            [
                [[FW::FWCONTEXT, FW::ADMINROLE]],
                [FW::CONFIG, FW::FORM, FW::FORMFIELD, FW::PAGE, FW::ROLECONTEXT, FW::ROLENAME, FW::TABLE, FW::USER],
            ],
//          [ [Roles], ['Table Name'...]]]    table name == bean name of course.
        ];
/**
 * @var array<array> Permissions array for tablesearch acccess.
 */
        private static $tablesearchperms = [
            [
                [[FW::FWCONTEXT, FW::ADMINROLE]],
                [
                    FW::CONFIG      => [],
                    FW::FORM        => [],
                    FW::FORMFIELD   => [],
                    FW::PAGE        => [],
                    FW::ROLECONTEXT => [],
                    FW::ROLENAME    => [],
                    FW::TABLE       => [],
                    FW::USER        => [],
                ],
            ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * @var array<array> Permissions array for unique acccess. This helps allow non-site admins use the AJAX functions
 */
        private static $uniqueperms = [
            [
                [[FW::FWCONTEXT, FW::ADMINROLE]],
                [ FW::PAGE => ['name'], FW::USER => ['login'], FW::ROLECONTEXT => ['name'], FW::ROLENAME => ['name']],
            ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * @var array<string>   Permissions array for unique access. This helps allow non-site admins use the AJAX functions
 */
        private static $uniquenlperms = [
            FW::USER => ['login'],
// 'bean' => [...fields...], ... // an array of beans and fields that can be accessed
        ];
/**
 * If you are using the pagination or search hinting features of the framework then you need to
 * add some appropriate vaues into these arrays. You do this in support/ajax.php. Not her.
 *
 * The key to both the array fields is the name of the bean type you are working with.
 */
/**
 * @var array   Values controlling whether or not pagination calls are allowed
 */
        private static $paging = [
            FW::PAGE  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            FW::USER  => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            // 'beanname' => [TRUE, [['ContextName', 'RoleName']]]
            // TRUE if login needed, an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * @var array<array>   Values controlling whether or not search hint calls are allowed
 */
        private static $hints = [
            // 'beanname' => ['field', TRUE, [['ContextName', 'RoleName']]]
            // name of field being searched, TRUE if login needed, an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
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
 * Check down an array with permissions in the first field and return the first
 * row that is OK
 *
 * @param Context  $context  The context object
 * @param array   $perms    The array with permissions in the first element
 *
 * @throws \Framework\Exception\Forbidden
 * @return array
 */
        final public function findRow(Context $context, array $perms) : array
        {
            $tables = [];
            foreach ($perms as $bpd)
            {
                /** @phpcsSuppress  PHP_CodeSniffer.CodeAnalysis.EmptyStatement */
                try
                {
                    $this->checkPerms($context, $bpd[0]); // make sure we are allowed
                    $tables[] = $bpd[1];
                }
                catch (\Framework\Exception\Forbidden $e)
                {
                    NULL; // void go round and try the next item in the array
                }
            }
            if (empty($tables))
            {
                throw new \Framework\Exception\Forbidden('Permission Denied');
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
 * @param array   $beans
 * @param string  $bean
 * @param string  $field
 * @param bool    $idok    Allow the id field
 *
 * @throws \Framework\Exception\Forbidden
 * @return bool
 */
        final public function beanCheck(array $beans, string $bean, string $field, bool $idok = FALSE) : bool
        {
            $this->fieldExists($bean, $field, $idok);
            if (!isset($beans[$bean]) || (!empty($beans[$bean]) && !in_array($field, $beans[$bean])))
            { // no permission to update this field or it doesn't exist
                throw new \Framework\Exception\Forbidden('Permission denied: '.$bean.'::'.$field);
            }
            return TRUE;
        }
/**
 * Add pagination or searching tables
 *
 * @param array     $paging     Values for pagination - see above for format
 * @param array     $hints      Values for hints - see above for format
 *
 * @return void
 */
        final public function pageOrHint(array $paging, array $hints) : void
        {
            self::$paging = array_merge(self::$paging, $paging);
            self::$hints = array_merge(self::$paging, $hints);
        }
/**
 * Add bean permissions to allow non site/admins to use the functions
 *
 * @param array    $bean
 * @param array    $toggle
 * @param array    $table
 * @param array    $audit
 * @param array    $tsearch
 *
 * @return void
 */
        final public function beanAccess(array $bean, array $toggle, array $table, array $audit, array $tsearch, array $uniquenl) : void
        {
            self::$beanperms = array_merge(self::$beanperms, $bean);
            self::$toggleperms = array_merge(self::$toggleperms, $toggle);
            self::$tableperms = array_merge(self::$tableperms, $table);
            self::$audit = array_merge(self::$audit, $audit);
            self::$tablesearchperms = array_merge(self::$tablesearchperms, $tsearch);
            self::$uniquenlperms = array_merge(self::$uniquenlperms, $uniquenl);
        }
/**
 * Check that user has the permissions that are specified in an array
 *
 * @param Context   $context  The Context bject
 * @param array     $perms    The permission array
 *
 * @throws \Framework\Exception\Forbidden
 * @return void
 * @psalm-suppress PossiblyNullReference
 */
        final public function checkPerms(Context $context, array $perms) : void
        {
            $user = $context->user();
            assert(!is_null($user)); // must have a user when checking
            foreach ($perms as $rcs)
            {
                if (is_array($rcs[0]))
                { // this is an OR
                    foreach ($rcs as $orv)
                    {
                        if (is_object($user->hasrole($orv[0], $orv[1])))
                        {
                            continue 2;
                        }
                    }
                    throw new \Framework\Exception\Forbidden('Permission denied');
                }
                if (!is_object($user->hasrole($rcs[0], $rcs[1])))
                {
                    throw new \Framework\Exception\Forbidden('Permission denied');
                }
            }
        }
    }
?>