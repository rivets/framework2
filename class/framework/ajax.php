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
            'bean'          => [TRUE,   [['Site', 'Admin']]],
            'config'        => [TRUE,   [['Site', 'Admin']]],
            'hints'         => [FALSE,  []], // permission checks are done in the hints function
            'logincheck'    => [FALSE,  []], // used by registration page
            'pagecheck'     => [TRUE,   [['Site', 'Admin']]],
            'paging'        => [FALSE,  []], // permission checks are done in the paging function
            'pwcheck'       => [TRUE,   []],
            'table'         => [TRUE,   [['Site', 'Admin']]],
            'tablecheck'    => [TRUE,   [['Site', 'Admin']]],
            'toggle'        => [TRUE,   [['Site', 'Admin']]],
            'update'        => [TRUE,   [['Site', 'Admin']]],
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
 * Add a User
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function adduser(Context $context)
        {
            $now = $context->utcnow(); # make sure time is in UTC
            $fdt = $context->formdata();
            $u = R::dispense('user');
            $u->login = $fdt->mustpost('login');
            $u->email = $fdt->mustpost('email');
            $u->active = 1;
            $u->confirm = 1;
            $u->joined = $now;
            R::store($u);
            $u->setpw($fdt->mustpost('password'));
            if ($fdt->post('admin', 0) == 1)
            {
                $u->addrole('Site', 'Admin', '', $now);
            }
            if ($fdt->post('devel', 0) == 1)
            {
                $u->addrole('Site', 'Developer', '', $now);
            }
            echo $u->getID();
        }
/**
 * Add a Rolename
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addrolename(Context $context)
        {
            $p = R::dispense('rolename');
            $p->name = $context->formdata()->mustpost('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
/**
 * Add a Rolecontext
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addrolecontext(Context $context)
        {
            $p = R::dispense('rolecontext');
            $p->name = $context->formdata()->mustpost('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
/**
 * Check URL string for n values and pull them out
 *
 * The value in $rest[0] is the opcode so we always start at $rest[1]
 *
 * @param object        $context    The context object
 * @param int           $count      The number to check for
 *
 * @return array
 */
        protected function restcheck(Context $context, int $count) : array
        {
            $values = [];
            $rest = $context->rest();
            foreach (range(1, $count) as $ix)
            {
                if (($val = $rest[$ix] ?? '') === '')
                {
                    $context->web()->bad();
                }
                $values[] = $val;
            }
            return $values;
        }
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
            list($name) = $this->restcheck($context, 1);
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
            $fdt = $context->formdata();
            $type = $fdt->mustpost('bean');
            $field = $fdt->mustpost('field');

            $bn = $context->load($type, $fdt->mustpost('id'), Context::R400);
            if ($type === 'user' && ctype_upper($field[0]))
            { # not simple toggling...
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
            $rest = $context->rest();
            $bean = $rest[1];
            switch ($_SERVER['REQUEST_METHOD'])
            {
            case 'POST': // make a new one /ajax/bean/KIND/
                $class = REDBEAN_MODEL_PREFIX.$bean;
                if (method_exists($class, 'add'))
                {
                    $class::add($context);
                }
                elseif (!method_exists($this, 'add'.$bean))
                {
                    $context->web()->bad();
                }
                else
                {
                    $this->{'add'.$bean}($context);
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
                {
                    $context->web()->bad();
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
            $rest = $context->rest();
            $bean = $rest[1];
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
            $rest= $context->rest();
            list($name) = $this->restcheck($context, 1);
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
            $rest= $context->rest();
            list($name) = $this->restcheck($context, 1);
            $tb = \R::inspect();
            if (isset($tb[strtolower($rest[1])]))
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
 * Check that the caller is allowed to perform the operation.
 *
 * @param object   $context  The Context Object
 * @param boolean  $login    If TRUE Then user must be logged in.
 * @param array    $perms    As specified for the various arrays defined above
 *
 * @return void  Does not return if user is not allowed.
 */
        private function checkPerms(Context $context, bool $login, array $perms)
        {
            if ($login)
            { # this operation requires a logged in user
                $context->mustbeuser(); // will not return if there is no user
            }
            foreach ($perms as $rcs)
            {
                if (is_array($rcs[0]))
                { // this is an OR
                    $ok = FALSE;
                    foreach ($rcs as $orv)
                    {
                        if ($context->user()->hasrole($orv[0], $orv[1]) !== FALSE)
                        {
                            $ok = TRUE;
                            break;
                        }
                    }
                    if (!$ok)
                    {
                        $context->web()->noaccess();
                        /* NOT REACHED */
                    }
                }
                else
                {
                    if ($context->user()->hasrole($rcs[0], $rcs[1]) === FALSE)
                    {
                        $context->web()->noaccess();
                        /* NOT REACHED */
                    }
                }
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
                    $this->checkPerms($context, self::$restops[$op][0], self::$restops[$op][1]);
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
