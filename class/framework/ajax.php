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
            'config'        => [TRUE,   [['Site', 'Admin']]],
            'hints'         => [FALSE,  []], // permission checks are done in the hints function
            'logincheck'    => [FALSE,  []], // used by registration page
            'pagecheck'     => [TRUE,   [['Site', 'Admin']]],
            'paging'        => [FALSE,  []], // permission checks are done in the paging function
            'pwcheck'       => [TRUE,   []], // permission checks are done in the table function
            'table'         => [TRUE,   []],
            'tablecheck'    => [TRUE,   [['Site', 'Admin']]],
            'toggle'        => [TRUE,   []], // permission checks are done in the toggle function
            'update'        => [TRUE,   [['Site', 'Admin']]],
        ];
/**
 * Permissions array for bean acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $beanperms = [
            [ [['Site', 'Admin']], ['page' => [], 'user' => [], 'fwconfig' => [], 'form' => [], 'formfield' => [], 'rolecontext' => [], 'rolename' => [], 'table' => []] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * Permissions array for toggle acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $toggleperms = [
            [ [['Site', 'Admin']], ['page' => [], 'user' => [], 'fwconfig' => [], 'form' => [], 'formfield' => [], 'rolecontext' => [], 'rolename' => [], 'table' => []] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * Permissions array for table acccess. This helps allow non-site admins use the AJAX bean functions
 */
        private static $tableperms = [
            [ [['Site', 'Admin']], ['page', 'user', 'fwconfig', 'form', 'formfield', 'rolecontext', 'rolename', 'table'] ],
//          [ [Roles], ['BeanName' => [FieldNames - all if empty]]]]
        ];
/**
 * If you are using the pagination or search hinting features of the framework then you need to
 * add some appropriate vaues into these arrays.
 *
 * The key to both the array fields is the name of the bean type you are working with.
 */
/**
 * @var array   Values controlling whether or not pagination calls are allowed
 */
        private static $paging = [
            'page'  => [TRUE,   [['Site', 'Admin']]],
            'user'  => [TRUE,   [['Site', 'Admin']]],
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
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function config(Context $context)
        {
            $rest = $context->rest();
            list($name) = $context->restcheck(1);
            $v = R::findOne('fwconfig', 'name=?', [$name]);
            $fdt = $context->formdata();
            switch ($_SERVER['REQUEST_METHOD'])
            {
            case 'POST':
                if (is_object($v))
                {
                    $context->web()->bad();
                }
                $v = R::dispense('fwconfig');
                $v->name = $name;
                $v->value = $fdt->mustpost('value');
                R::store($v);
                break;
            case 'PATCH':
            case 'PUT':
                if (!is_object($v))
                {
                    $context->web()->bad();
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
                $context->web()->bad();
            }
        }
/**
 * Check down an array with permissions in the first field and return the first
 * row that is OK
 *
 * @param object  $context  The context object
 * @param array   $perms    The array wiht permissions in the first element
 *
 * @return array
 */
        private function findrow(Context $context, $perms)
        {
            foreach ($perms as $bpd)
            {
                if ($this->checkPerms($context, $bpd[0], Context::RBOOL)) // make sure we are allowed
                {
                    return $bpd[1];
                }
            }
            $context->web()->noaccess();
        }
/**
 * Toggle a flag field in a bean
 *
 * Note that for Roles the toggling is more complex and involves role removal/addition rather than
 * simply changing a value.
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function toggle(Context $context)
        {
            $beans = $this->findRow($context, self::$toggleperms);
            $rest = $context->rest();
            if (count($rest) > 2)
            {
                list($dum, $type, $bid, $fld) = $context->restcheck(4);
            }
            else // this is legacy
            {
                $bean = $rest[1];
                $fdt = $context->formdata();
                $type = $fdt->mustpost('bean');
                $field = $fdt->mustpost('field');
                $bid = $fdt->mustpost('id');
            }

            $bn = $context->load($type, $bid, Context::R400);
            if ($type === 'user' && ctype_upper($field[0]) && $context->isadmin())
            { # not simple toggling... and can only be done by the Site Administrator
                if (is_object($bn->hasrole('Site', $field)))
                {
                    $bn->delrole('Site', $field);
                }
                else
                {
                    $bn->addrole('Site', $field, '', $context->utcnow());
                }
            }
            else
            {
                $bn->$field = $bn->$field == 1 ? 0 : 1;
                R::store($bn);
            }
        }
/**
 * Update a field in a bean
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function update(Context $context)
        {
            $fdt = $context->formdata();

            $bn = $context->load($fdt->mustpost('bean'), $fdt->mustpost('id'), Context::R400);
            $field = $fdt->mustpost('name');
            $bn->$field = $fdt->mustpost('value');
            R::store($bn);
        }
/**
 * Carry out operations on beans
 *
 * @param object    $context The context object
 *
 * @return void
 */
        private function bean(Context $context)
        {
            $beans = $this->findRow($context, self::$beanperms);
            $rest = $context->rest();
            $bean = $rest[1];
            if (!isset($beans[$bean]))
            {
                $context->web()->noaccess();
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
                    $context->web()->bad();
                }
                break;
            case 'PATCH':
            case 'PUT': // update a field   /ajax/bean/KIND/ID/FIELD/
                $id = $rest[2] ?? 0; // get the id from the URL
                if ($id <= 0)
                {
                    $context->web()->bad();
                }
                $bn = $context->load($bean, $id);
                $fields = R::inspect($bean); // gets all the fields
                $field = $rest[3] ?? ''; // get the field name from the URL
                if (!isset($fields[$field]))
                { // no such field
                    $context->web()->bad();
                }
                if (!empty($beans[$bean]) && !in_array($field, $beans[$bean]))
                { // no permission to update this field
                    $context->web()->noaccess();
                }
                $bn->$field = $context->formdata()->mustput('value');
                R::store($bn);
                break;
            case 'DELETE': // /ajax/bean/KIND/ID/
                $id = $rest[2] ?? 0; // get the id from the URL
                if ($id <= 0)
                {
                    $context->web()->bad();
                }
                R::trash($context->load($bean, $id));
                break;
            case 'GET':
            default:
                $context->web()->bad();
            }
        }
/**
 * Carry out operations on tables
 *
 * @param object    $context The context object
 *
 * @return void
 */
        private function table(Context $context)
        {
            $tables = $this->findRow($context, self::$tableperms);
            $rest = $context->rest();
            $table = $rest[1];
            if (!in_array($table, $tables))
            {
                $context->web()->noaccess();
            }
            switch ($_SERVER['REQUEST_METHOD'])
            {
            case 'POST': // make a new one
            case 'PATCH':
            case 'PUT': // add a field
            case 'DELETE':
            case 'GET':
            default:
                $context->web()->bad();
            }
        }
/**
 * Get a page of bean values
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function paging(Context $context)
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
                $context->web()->noaccess();
            }
        }
/**
 * Get serach hints for a bean
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function hints(Context $context)
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
                $order = $fdt->get('order', '');
                $search = $fdt->mustget('search');
                $res = \Support\Siteinfo::getinstance()->fetch($bean, $field.' like ?'.($order !== '' ? (' order bye '.$order) : ''), [$search]);
                $context->web()->sendJSON($res);
            }
            else
            {
                $context->web()->noaccess();
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
        public function operation(string $function, array $perms)
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
        public function pageOrHint(array $paging, array $hints)
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
        public function beanAccess(array $bean, array $toggle, array $table)
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
 * @param string    $name     The field to check
 *
 * @return void
 */
        protected function uniqCheck(Context $context, string $bean, string $field)
        {
            list($name) = $context->restcheck(1);
            if (R::count($bean, preg_replace('/[^a-z0-9_]/i', '', $field).'=?', [$name]) > 0)
            {
                $context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
/**
 * Do a parsley login check
 *
 * @param object    $context
 *
 * @return void
 */
        public function logincheck(Context $context)
        {
            $this->uniqCheck($context, 'user', 'login');
        }
/**
 * Do a parsley page check
 *
 * @param object    $context
 *
 * @return void
 */
        public function pagecheck(Context $context)
        {
            $this->uniqCheck($context, 'page', 'name');
        }
/**
 * Do a parsley table check
 *
 * @param object    $context
 *
 * @return void
 */
        public function tablecheck(Context $context)
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
 * @param object    $context
 *
 * @return void
 */
        public function pwcheck(Context $context)
        {
            if (($pw = $context->formdata()->get('pw', '')) !== '')
            {
                if ($context->user()->pwok($pw))
                {
                    return;
                }
            }
            $context->web()->noaccess();
        }
/**
 * Check that user has the permissions specified in an array
 *
 * @param object    $context  The Context bject
 * @param array     $perms    The permission array
 * @param integer   $onerror  What to do if access is forbidden
 *
 * @throws \Framework\Exception\Forbidden
 * @throws \InvalidArgumentException
 *
 * @return boolean or may not return at all
 */
        private function checkPerms(Context $context, array $perms, int $onerror = Context::R400) : bool
        {
            $ok = TRUE;
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
                    $ok = FALSE; // none TRUE so forbidden
                    break;
                }
                elseif ($context->user()->hasrole($rcs[0], $rcs[1]) === FALSE)
                {
                    $ok = FALSE; // not TRUE so forbidden
                    break;
                }
            }
            if (!$ok)
            {
                switch ($onerror)
                {
                case Context::R400:
                    $context->web()->noaccess();
                    /* NOT REACHED */
                case Context::RTHROW:
                   throw new \Framework\Exception\Forbidden();
                case Context::RBOOL:
                    return FALSE;
                default:
                    throw new \InvalidArgumentException('Onerror value');
                }
            }
            return TRUE;
        }
/**
 * Check that the caller is allowed to perform the operation.
 *
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
            return $this->checkPerms($context, $perms, Context::R400);
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
                    $this->checkLogin($context, self::$restops[$op][0], self::$restops[$op][1]);
                    $this->{$op}($context);
                }
                else
                { # return a 400
                    $context->web()->bad();
                    /* NOT REACHED */
                }
            }
            else
            { // for the moment no other options
                $context->web()->bad();
                /* NOT REACHED */
            }
        }
    }
?>
