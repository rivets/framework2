<?php
/**
 * Class for handling AJAX calls invoked from ajax.php. You could integrate the
 * AJAX handling calls into the normal index.php RESTful route, but sometimes
 * keeping them separate is a good thing to do.
 *
 * It assumes that ajax calls are made to {{base}}/ajax.php via a POST and that
 * they have at least a parameter called 'op' that defines what is to be done.
 *
 * Of course, this is entirely arbitrary and you can do whatever you want!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2018 Newcastle University
 */
    namespace Framework;

    use \R as R;
    use \Support\Context as Context;
    use \Config\Config as Config;
    use \Support\Formdata as FormData;
/**
 * Handle Ajax operations in this class
 */
    class Ajax
    {
        use \Framework\Utility\Singleton;
/**
 * @var array Allowed operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static $restops = [
            'bean'          => [TRUE,   []], // permission checks are done in the bean function
            'config'        => [TRUE,   [[Config::FWCONTEXT, Config::ADMINROLE]]],
            'hints'         => [FALSE,  []], // permission checks are done in the hints function
            'paging'        => [FALSE,  []], // permission checks are done in the paging function
            'pwcheck'       => [TRUE,   []], // permission checks are done in the table function
            'table'         => [TRUE,   []],
            'tablecheck'    => [TRUE,   [[Config::FWCONTEXT, Config::ADMINROLE]]],
            'toggle'        => [TRUE,   []], // permission checks are done in the toggle function
            'unique'        => [TRUE,   []], // test if a bean field value is unique
            'uniquenl'      => [FALSE,  []], // uique test with no login - used at least by user registration form
            'update'        => [TRUE,   [[Config::FWCONTEXT, Config::ADMINROLE]]],
        ];
/**
 * Permissions array for bean acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $beanperms = [
            [ [[Config::FWCONTEXT, Config::ADMINROLE]], [ 'page' => [], 'user' => [], 'fwconfig' => [], 'form' => [], 'formfield' => [], 'rolecontext' => [], 'rolename' => [], 'table' => []] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * Permissions array for toggle acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $toggleperms = [
            [ [[Config::FWCONTEXT, Config::ADMINROLE]], [ 'page' => [], 'user' => [], 'fwconfig' => [], 'form' => [], 'formfield' => [], 'rolecontext' => [], 'rolename' => [], 'table' => []] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * Permissions array for table acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $tableperms = [
            [ [[Config::FWCONTEXT, Config::ADMINROLE]], [ 'fwconfig', 'form', 'formfield', 'page', 'rolecontext', 'rolename', 'table', 'user'] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * Permissions array for unique acccess. This helps allow non-site admins use the AJAX functions
 */
        private static $uniqueperms = [
            [ [[Config::FWCONTEXT, Config::ADMINROLE]], [ 'page' => ['name'], 'user' => ['login'], 'rolecontext' => ['name'], 'rolename' => ['name']] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * Permissions array for unique acccess. This helps allow non-site admins use the AJAX functions
 */
        private static $uniquenlperms = [
            [ 'user' => ['login'] ],
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
            'page'  => [TRUE,   [[Config::FWCONTEXT, Config::ADMINROLE]]],
            'user'  => [TRUE,   [[Config::FWCONTEXT, Config::ADMINROLE]]],
            // 'beanname' => [TRUE, [['ContextName', 'RoleName']]]
            // TRUE if login needed, an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * @var array   Values controlling whether or not search hint calls are allowed
 */
        private static $hints = [
            // 'beanname' => ['field', TRUE, [['ContextName', 'RoleName']]]
            // name of field being searched, TRUE if login needed, an array of roles required in form [['context name', 'role name']...] (can be empty)
        ];
/**
 * Config value operation
 *
 * @internal
 * @param object	$context	The context object for the site
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 *
 * @return void
 */
        private final function config(Context $context)
        {
            $rest = $context->rest();
            list($name) = $context->restcheck(1);
            $v = R::findOne('fwconfig', 'name=?', [$name]);
            $fdt = $context->formdata();
            switch ($context->web()->method())
            {
            case 'POST':
                if (is_object($v))
                {
                    throw new \Framework\Exception\BadValue('Item already exists');
                }
                $v = R::dispense('fwconfig');
                $v->name = $name;
                $v->value = $fdt->mustpost('value');
                $v->type = $fdt->mustpost('type');
                R::store($v);
                break;
            case 'PATCH':
            case 'PUT':
                if (!is_object($v))
                {
                    throw new \Framework\Exception\BadValue('No such item');
                }
                $v->value = $fdt->mustput('value');
                R::store($v);
                break;
            case 'DELETE':
                R::trash($v);
                break;
            case 'GET':
                echo $v->value;
                break;
            default:
                throw new \Framework\Exception\BadOperation($context->web()->method().' is not supported');
            }
        }
/**
 * Check that a bean has a field. Do not allow id field to be manipulated.
 *
 * @param string    $type    The type of bean
 * @param string    $field   The field name
 *
 * @throws \Framework\Exception\BadValue
 * @return boolean
 */
        private function fieldExists(string $type, string $field)
        {
            $fds = \R::inspect($type);
            if ($field == 'is' || !isset($fds[$field]))
            {
                throw new \Framework\Exception\BadValue('Bad field: '.$field);
            }
        }
/**
 * Check down an array with permissions in the first field and return the first
 * row that is OK
 *
 * @internal
 * @param object  $context  The context object
 * @param array   $perms    The array with permissions in the first element
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return array
 */
        protected final function findrow(Context $context, $perms)
        {
            foreach ($perms as $bpd)
            {
                try
                {
                    $this->checkPerms($context, $bpd[0]); // make sure we are allowed
                    return $bpd[1];
                }
                catch (\Framework\Exception\Forbidden $e)
                {
                    // void go round and try the next item in the array
                }
            }
            throw new \Framework\Exception\Forbidden('Permission Denied');
        }
/**
 * Toggle a flag field in a bean
 *
 * Note that for Roles the toggling is more complex and involves role removal/addition rather than
 * simply changing a value.
 *
 * @internal
 * @param object	$context	The context object for the site
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        private final function toggle(Context $context)
        {
            $beans = $this->findRow($context, self::$toggleperms);
            $rest = $context->rest();
            if (count($rest) > 2)
            {
                list($type, $bid, $field) = $context->restcheck(3);
            }
            else // this is legacy
            {
                $bean = $rest[1];
                $fdt = $context->formdata();
                $type = $fdt->mustpost('bean');
                $field = $fdt->mustpost('field');
                $bid = $fdt->mustpost('id');
            }

            $bn = $context->load($type, $bid);
            if ($type === 'user' && ctype_upper($field[0]) && $context->hasadmin())
            { # not simple toggling... and can only be done by the Site Administrator
                if (is_object($bn->hasrole(Config::FWCONTEXT, $field)))
                {
                    $bn->delrole(Config::FWCONTEXT, $field);
                }
                else
                {
                    $bn->addrole(Config::FWCONTEXT, $field, '', $context->utcnow());
                }
            }
            else
            {
                $this->fieldExists($type, $field); // make sure the bean has this field....
                $bn->$field = $bn->$field == 1 ? 0 : 1;
                R::store($bn);
            }
        }
/**
 * Update a field in a bean
 *
 * @internal
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private final function update(Context $context)
        {
            $fdt = $context->formdata();

            $type = $fdt->mustpost('bean');
            $field = $fdt->mustpost('name');
            $this->fieldExists($type, $field);
            $bn = $fdt->mustpostbean('id', $type);
            $bn->$field = $fdt->mustpost('value');
            R::store($bn);
        }
/**
 * Check if a bean/field combination is allowed and the field exists and is not id
 *
 * @internal
 * @param array   $beans
 * @param string  $bean
 * @param string  $field
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return boolean or error out
 */
        protected function beanCheck($beans, $bean, $field)
        {
            $this->fieldExists($bean, $field);
            if (!isset($beans[$bean]) || (!empty($beans[$bean]) && !in_array($field, $beans[$bean])))
            { // no permission to update this field
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
            return TRUE;
        }
/**
 * Carry out operations on beans
 *
 * @internal
 * @param object    $context The context object
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        private final function bean(Context $context)
        {
            $beans = $this->findRow($context, self::$beanperms);
            $rest = $context->rest();
            $bean = $rest[1];
            if (!isset($beans[$bean]))
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
            switch ($context->web()->method())
            {
            case 'POST': // make a new one /ajax/bean/KIND/
                $class = REDBEAN_MODEL_PREFIX.$bean;
                if (method_exists($class, 'add'))
                {
                    $class::add($context);
                }
                else
                { // operation not supported
                    throw new \Framework\Exception\BadOperation('Cannot add a '.$bean);
                }
                break;
            case 'PATCH':
            case 'PUT': // update a field   /ajax/bean/KIND/ID/FIELD/
                list($bean, $id, $field) = $context->restcheck(3);
                $this->beanCheck($beans, $bean, $field);
                $bn = $context->load($bean, $id, TRUE);
                $bn->$field = $context->formdata()->mustput('value');
                R::store($bn);
                break;
            case 'DELETE': // /ajax/bean/KIND/ID/
                $id = $rest[2] ?? 0; // get the id from the URL
                if ($id <= 0)
                {
                    throw new \Framework\Exception\BadValue('Missing value');
                }
                R::trash($context->load($bean, $id));
                break;
            case 'GET':
            default:
                throw new \Framework\Exception\BadOperation($context->web()->method().' not supported');
            }
        }
/**
 * Carry out operations on tables
 *
 * @internal
 * @param object    $context The context object
 *
 * @throws \Framework\Exception\Forbidden
 * @throws \Framework\Exception\BadOperation
 *
 * @return void
 */
        private final function table(Context $context)
        {
            $tables = $this->findRow($context, self::$tableperms);
            $rest = $context->rest();
            $table = $rest[1];
            $method = $context->web()->method();
            if (!in_array($table, $tables))
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
            switch ($method)
            {
            case 'POST': // make a new one
            case 'PATCH':
            case 'PUT': // add a field
            case 'DELETE':
            case 'GET':
            default:
                throw new \Framework\Exception\BadOperation('Operation not supported');
            }
        }
/**
 * Get a page of bean values
 *
 * @internal
 * @param object	$context	The context object for the site
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        private final function paging(Context $context)
        {
            $fdt = $context->formdata();
            $bean = $fdt->mustget('bean');
            if (isset(self::$paging[$bean]))
            { // pagination is allowed for this bean
                $this->checkPerms($context, self::$paging[$bean][0], self::$paging[$bean][1]); // make sure we are allowed
                $order = $fdt->get('order', '');
                $page = $fdt->mustget('page');
                $pagesize = $fdt->mustget('pagesize');
                $res = \Support\Siteinfo::getinstance()->fetch($bean, ($order !== '' ? ('order bye '.$order) : ''), [], $page, $pagesize);
                $context->web()->sendJSON($res);
            }
            else
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
        }
/**
 * Get serach hints for a bean
 *
 * @internal
 * @param object	$context	The context object for the site
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        private final function hints(Context $context)
        {
            $fdt = $context->formdata();
            $bean = $fdt->mustget('bean');
            if (isset(self::$hints[$bean]))
            { // hinting is allowed for this bean
                $this->checkPerms($context, self::$hints[$bean][1], self::$hints[$bean][2]); // make sure we are allowed
                $field = self::$hints[$bean][0];
                if ($field == '*')
                { # the call must specify the field
                    $field = $fdt->mustget('field');
                }
                $this->fieldExists($bean, $field); // checks field exists - this implies the the field value is not dangerous to pass directly into the query,
                $order = $fdt->get('order', '');
                $search = $fdt->mustget('search');
                $res = \Support\Siteinfo::getinstance()->fetch($bean, $field.' like ?'.($order !== '' ? (' order bye '.$order) : ''), [$search]);
                $context->web()->sendJSON($res);
            }
            else
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
        }
/**
 * Add an operation
 *
 * @param string    $function   The name of a function
 * @param array     $perms      [TRUE if login needed, [roles needed]] where roles are ['context', 'role']
 *
 * @return void
 */
        public final function operation(string $function, array $perms)
        {
            self::$restops[$function] = $perms;
        }
/**
 * Add pagination or searching tables
 *
 * @param array     $paging     Values for pagination - see above for format
 * @param array     $hints      Values for hints - see above for format
 *
 * @return void
 */
        public final function pageOrHint(array $paging, array $hints)
        {
            self::$paging = array_merge(self::$paging, $paging);
            self::$hints = array_merge(self::$paging, $hints);
        }
/**
 * Add bean permissions to allow non site/admins to use the functions
 *
 * @param array     $bean      
 * @param array     $toggle      
 * @param array     $table     
 *
 * @return void
 */
        public final function beanAccess(array $bean, array $toggle, array $table)
        {
            self::$beanperms = array_merge(self::$beanperms, $bean);
            self::$toggleperms = array_merge(self::$toggleperms, $toggle);
            self::$tableperms = array_merge(self::$tableperms, $table);
        }
/**
 * Do a database check for uniqueness
 *
 * @param object    $context  The Context object
 * @param string    $bean     The kind of bean
 * @param string    $field    The field to check
 * @param string    $value    The value to check
 *
 * @return void
 */
        protected final function uniqCheck(Context $context, string $bean, string $field, string $value)
        {
            if (R::count($bean, preg_replace('/[^a-z0-9_]/i', '', $field).'=?', [$value]) > 0)
            {
                $context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
/**
 * Do a parsley uniqueness check
 *
 * @internal
 * @param object    $context
 *
 * @return void
 */
        private function unique(Context $context)
        {
            $beans = $this->findrow($context, self::$uniqueperms);
            list($bean, $field, $value) = $context->restcheck(3);
            $this->beanCheck($beans, $bean, $field);
            $this->uniqCheck($context, $bean, $field, $value);
        }
/**
 * Do a parsley uniqueness check
 *
 * @internal
 * @param object    $context
 *
 * @return void
 */
        private function uniquenl(Context $context)
        {
            list($bean, $field, $value) = $context->restcheck(3);
            $this->beanCheck(self::$uniquenlperms, $bean, $field);
            $this->uniqCheck($context, $bean, $field, $value);
        }
/**
 * Do a parsley table check
 *
 * @internal
 * @param object    $context
 *
 * @return void
 */
        private function tablecheck(Context $context)
        {
            list($name) = $context->restcheck(1);
            $tb = \R::inspect();
            if (in_array(strtolower($name), $tb))
            {
                $context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
/**
 * Do a password verification
 *
 * @internal
 * @param object    $context
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        private function pwcheck(Context $context)
        {
            if (($pw = $context->formdata()->get('pw', '')) === '' || !$context->user()->pwok($pw))
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
        }
/**
 * Check that user has the permissions specified in an array
 *
 * @internal
 * @param object    $context  The Context bject
 * @param array     $perms    The permission array
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        protected final function checkPerms(Context $context, array $perms)
        {
            foreach ($perms as $rcs)
            {
                if (is_array($rcs[0]))
                { // this is an OR
                    foreach ($rcs as $orv)
                    {
                        if ($context->user()->hasrole($orv[0], $orv[1]) !== FALSE)
                        {
                            continue 2;
                        }
                    }
                    throw new \Framework\Exception\Forbidden('Permission denied');
                }
                elseif ($context->user()->hasrole($rcs[0], $rcs[1]) === FALSE)
                {
                    throw new \Framework\Exception\Forbidden('Permission denied');
                }
            }
        }
/**
 * Check that the caller is allowed to perform the operation.
 *
 * @internal
 * @param object   $context  The Context Object
 * @param boolean  $login    If TRUE Then user must be logged in.
 * @param array    $perms    As specified for the various arrays defined above
 *
 * @return boolean  Does not return if user is not allowed.
 */
        private function checkLogin(Context $context, bool $login, array $perms) : bool
        {
            if ($login)
            { # this operation requires a logged in user
                $context->mustbeuser(); // will not return if there is no user
            }
            try
            {
                $this->checkPerms($context, $perms);
                return TRUE;
            }
            catch (\Framework\Exception\Forbidden $e)
            {
                return FALSE;
            }
        }
/**
 * Handle AJAX operations
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public function handle(Context $context)
        {
            if ($context->action() == 'ajax')
            { # REST style AJAX call
                $rest = $context->rest();
                $op = $rest[0];
                if (isset(self::$restops[$op]))
                { # a valid operation
                    try
                    {
                        $this->checkLogin($context, self::$restops[$op][0], self::$restops[$op][1]);
                        $this->{$op}($context);
                        return;
                    }
                    catch(\Framework\Exception\Forbidden $e)
                    {
                        $context->web()->noaccess($e->getMessage());
                    }
                    catch(\Framework\Exception\BadValue |
                          \Framework\Exception\BadOperation |
                          \Framework\Exception\MissingBean |
                          \Framework\Exception\ParameterCount $e)
                    {
                        $context->web()->bad($e->getMessage());
                    }
                    catch(\Exception $e)
                    { // any other exception - this will be a framework internal error
                        $context->web()->internal($e->getMessage());
                    }
                }
            }
            $context->web()->bad('No such operation');
            /* NOT REACHED */
        }
    }
?>
