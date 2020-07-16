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

    use \Config\Framework as FW;
    use \Framework\Ajax\BeanLog;
    use \R;
    use \Support\Context;
/**
 * Handle Ajax operations in this class
 */
    class Ajax
    {
        use \Framework\Utility\Singleton;
/**
 * @var array<array> Allowed operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static $restops = [
            'bean'          => [TRUE,   []], // permission checks are done in the bean function
            'config'        => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            'hints'         => [FALSE,  []], // permission checks are done in the hints function
            'paging'        => [FALSE,  []], // permission checks are done in the paging function
            'pwcheck'       => [TRUE,   []], // permission checks are done in the pwcheck function
            'shared'        => [TRUE,   []], // permission checks are done in the shared function
            'table'         => [TRUE,   []], // permission checks are done in the table function
            'tablecheck'    => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            'tablesearch'   => [TRUE,   [[FW::FWCONTEXT, FW::ADMINROLE]]],
            'toggle'        => [TRUE,   []], // permission checks are done in the toggle function
            'unique'        => [TRUE,   []], // test if a bean field value is unique
            'uniquenl'      => [FALSE,  []], // unique test with no login - used at least by user registration form
        ];
/**
 * @var array<string> Search ops
 */
        private static $searchops = ['', '=', '!=', 'like', 'contains', '>', '>=', '<', '<=', 'regexp', 'is NULL', 'is not NULL'];
/**
 * @var \Framwork\Ajax\Access
 */
        protected $access;
/**
 * Config value operation
 *
 * @internal
 * @param \Support\Context    $context    The context object for the site
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function config(Context $context) : void
        {
            [$name] = $context->restcheck(1);
            $v = R::findOne(FW::CONFIG, 'name=?', [$name]);
            switch ($context->web()->method())
            {
            case 'POST':
                if (is_object($v))
                {
                    throw new \Framework\Exception\BadValue('Item already exists');
                }
                $fdt = $context->formdata('post');
                $v = R::dispense(FW::CONFIG);
                $v->name = $name;
                $v->value = $fdt->mustFetch('value');
                $v->type = $fdt->mustFetch('type');
                R::store($v);
                break;
            case 'PATCH':
            case 'PUT':
                if (!is_object($v))
                {
                    throw new \Framework\Exception\BadValue('No such item');
                }
                $v->value = $context->formdata('put')->mustFetch('value');
                R::store($v);
                break;
            case 'DELETE':
                if (!is_object($v))
                {
                    throw new \Framework\Exception\BadValue('No such item');
                }
                R::trash($v);
                break;
            case 'GET':
                if (!is_object($v))
                {
                    throw new \Framework\Exception\BadValue('No such item');
                }
                echo $v->value;
                break;
            default:
                throw new \Framework\Exception\BadOperation($context->web()->method().' is not supported');
            }
        }
/**
 * Toggle a flag field in a bean
 *
 * Note that for Roles the toggling is more complex and involves role removal/addition rather than
 * simply changing a value.
 *
 * @internal
 * @param Context   $context    The context object for the site
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function toggle(Context $context) : void
        {
            $rest = $context->rest();
            if (count($rest) > 2)
            {
                [$type, $bid, $field] = $context->restcheck(3);
            }
            else // this is legacy
            {
                $fdt = $context->formdata('post');
                $type = $fdt->mustFetch('bean');
                $field = $fdt->mustFetch('field');
                $bid = $fdt->mustFetch('id');
            }
            $this->access->beanFindCheck($context, 'toggleperms', $type, $field);
            $bn = $context->load($type, (int) $bid);
            if ($type === 'user' && ctype_upper($field[0]) && $context->hasadmin())
            { # not simple toggling... and can only be done by the Site Administrator
                if (is_object($bn->hasrole(FW::FWCONTEXT, $field)))
                {
                    $bn->delrole(FW::FWCONTEXT, $field);
                }
                else
                {
                    $bn->addrole(FW::FWCONTEXT, $field, '', $context->utcnow());
                }
            }
            else
            {
                $bn->$field = $bn->$field == 1 ? 0 : 1;
                R::store($bn);
            }
        }
/**
 * Carry out operations on beans
 *
 * @internal
 * @param \Support\Context    $context The context object
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function bean(Context $context) : void
        {
            $beans = $this->access->findRow($context, 'beanperms');
            $rest = $context->rest();
            $bean = $rest[1];
            if (!isset($beans[$bean]))
            {
                throw new \Framework\Exception\Forbidden('Permission denied: '.$bean);
            }
            $log = in_array($bean, self::$audit);
            $method = $context->web()->method();
            /** @psalm-suppress UndefinedConstant */
            $class = REDBEAN_MODEL_PREFIX.$bean;
            /**
             * @psalm-suppress RedundantCondition
             * @psalm-suppress ArgumentTypeCoercion
             */
            if (method_exists($class, 'canAjaxBean'))
            {
                /** @psalm-suppress InvalidStringClass */
                $class::canAjaxBean($context, $method);
            }
            switch ($method)
            {
            case 'POST': // make a new one /ajax/bean/KIND/
                /**
                 * @psalm-suppress RedundantCondition
                 * @psalm-suppress ArgumentTypeCoercion
                 */
                if (method_exists($class, 'add'))
                {
                    /** @psalm-suppress InvalidStringClass */
                    $id = $class::add($context)->getID();
                    if ($log)
                    {
                        BeanLog::mklog($context, BeanLog::CREATE, $bean, $id, '*', NULL);
                    }
                    echo $id;
                }
                else
                { // operation not supported
                    throw new \Framework\Exception\BadOperation('Cannot add a '.$bean);
                }
                break;
            case 'PATCH':
            case 'PUT': // update a field   /ajax/bean/KIND/ID/FIELD/[FN]
                [$bean, $id, $field, $more] = $context->restcheck(3);
                $this->access->beanCheck($beans, $bean, $field);
                $bn = $context->load($bean, (int) $id, TRUE);
                $old = $bn->$field;
                $bn->$field = empty($more) ? $context->formdata('put')->mustFetch('value') : $bn->{$more[0]}($context->formdata('put')->mustFetch('value'));
                R::store($bn);
                if ($log)
                {
                    BeanLog::mklog($context, BeanLog::UPDATE, $bean, $bn->getID(), $field, $old);
                }
                break;
            case 'DELETE': // /ajax/bean/KIND/ID/
                $id = $rest[2] ?? 0; // get the id from the URL
                if ($id <= 0)
                {
                    throw new \Framework\Exception\BadValue('Missing value');
                }
                $bn = $context->load($bean, (int) $id);
                if ($log)
                {
                    BeanLog::mklog($context, BeanLog::DELETE, $bean, (int) $id, '*', json_encode($bn->export()));
                }
                /**
                 * @psalm-suppress RedundantCondition
                 * @psalm-suppress ArgumentTypeCoercion
                 */
                if (method_exists($class, 'delete')) // call the clean-up function if it has one
                {
                    $bn->delete($context);
                }
                R::trash($bn);
                break;
            case 'GET':
            default:
                throw new \Framework\Exception\BadOperation($method.' not supported');
            }
        }
/**
 * Carry out operations on RB shared lists
 *
 * @internal
 * @param \Support\Context    $context The context object
 *
 * @throws \Framework\Exception\BadOperation
 * @throws \Framework\Exception\BadValue
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function shared(Context $context) : void
        {

            [$b1, $id1, $b2, $id2] = $context->restcheck(4);
            $bn1 = $context->load($b1, (int) $id1);
            $bn2 = $context->load($b2, (int) $id2);
            $beans = $this->access->findRow($context, 'sharedperms');
/**
 * @todo This check is not right as the array format is slightly different for sharedperms
 *       Fix when this gets properly implemented.
 */
            $this->access->beanCheck($beans, $bn1->getMeta('type'), '');
            $this->access->beanCheck($beans, $bn2->getMeta('type'), '');
            switch ($context->web()->method())
            {
            case 'POST': // make a new share /ajax/shared/KIND1/id1/KIND2/id2
                $bn1->noload()->{'shared'.ucfirst($b2).'List'}[] = $bn2;
                \R::store($bn1);
                break;
            case 'DELETE': // /ajax/shared/KIND1/id1/KIND2/id2
                unset($bn1->{'shared'.ucfirst($b2).'List'}[$bn2->getID()]);
                \R::store($bn1);
                break;
            case'PUT':
            case 'PATCH':
            case 'GET':
            default:
                throw new \Framework\Exception\BadOperation($context->web()->method().' not supported');
            }
        }
/**
 * Carry out operations on tables
 *
 * @internal
 * @param \Support\Context   $context The context object
 *
 * @throws \Framework\Exception\Forbidden
 * @throws \Framework\Exception\BadOperation
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function table(Context $context) : void
        {
            if (!$context->hasadmin())
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
                /* NOT REACHED */
            }
            $rest = $context->rest();
            if (count($rest) < 2)
            {
                throw new \Framework\Exception\BadValue('No table name');
                /* NOT REACHED */
            }
            $table = strtolower($rest[1]);
            $method = $context->web()->method();
            if ($method == 'POST')
            {
                if (\Support\SiteInfo::tableExists($table))
                {
                    throw new \Framework\Exception\Forbidden('Table exists');
                    /* NOT REACHED */
                }
                if (!preg_match('/[a-z][a-z0-9]*/', $table))
                {
                    throw new \Framework\Exception\BadValue('Table name should be alphanumeric');
                    /* NOT REACHED */
                }
                $fdt = $context->formdata('post');
                $bn = \R::dispense($table);
                foreach ($fdt->fetchArray('field') as $ix => $fname)
                {
                    $fname = strtolower($fname);
                    if (preg_match('/[a-z][a-z0-9]*/', $fname))
                    {
                        $bn->$fname = $fdt->fetch(['sample', $ix], '');
                    }
                }
                \R::store($bn);
                \R::trash($bn);
                \R::exec('truncate `'.$table.'`');
            }
            else
            {
                if (\Support\SiteInfo::isFWTable($table))
                {
                    throw new \Framework\Exception\Forbidden('Permission Denied');
                    /* NOT REACHED */
                }
                switch ($method)
                {
                case 'DELETE':
                    try
                    {
                        \R::exec('drop table `'.$table.'`');
                    }
                    catch (\Exception $e)
                    {
                        throw new \Framework\Exception\Forbidden($e->getMessage());
                        /* NOT REACHED */
                    }
                    break;
                case 'PATCH':
                case 'PUT': // change a field
                    $value = $context->formdata('put')->mustFetch('value');
                    $f1 = $rest[2];
                    if (\Support\SiteInfo::hasField($table, $f1))
                    {
                        throw new \Framework\Exception\BadValue('Bad field name');
                        /* NOT REACHED */
                    }
                    switch ($rest[3])
                    {
                    case 'name':
                        if (\Support\SiteInfo::hasField($table, $value))
                        {
                            throw new \Framework\Exception\BadValue('Field already exists');
                            /* NOT REACHED */
                        }
                        $f2 = $value;
                        $fields = \R::inspect($table);
                        $type = $fields[$f1];
                        break;
                    case 'type':
                        $f2 = $f1;
                        $type = $value;
                        break;
                    default:
                        throw new \Framework\Exception\BadValue('No such change');
                        /* NOT REACHED */
                    }
                    \R::exec('alter table `'.$table.'` change `'.$f1.'` `'.$f2.'` '.$type);
                    break;
                case 'GET':
                default:
                    throw new \Framework\Exception\BadOperation('Operation not supported');
                    /* NOT REACHED */
                }
            }
        }
/**
 * Search a table
 *
 * @internal
 * @param \Support\Context   $context The context object
 *
 * @throws \Framework\Exception\Forbidden
 * @throws \Framework\Exception\BadOperation
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function tablesearch(Context $context) : void
        {
            [$bean, $field, $op] = $context->restcheck(3);
            $this->access->beanFindCheck($context, 'tablesearchperms', $bean, $field, TRUE); // make sure we are allowed to search this bean/field and that it exists
            $value = $context->formdata('get')->fetch('value', '');
            $incv = ' ?';
            if ($op == '4')
            {
                $value = '%'.$value.'%';
                $op = 'like';
            }
            else
            {
                if ($op == 10 || $op == 11)
                { // no value on a NULL test
                    $incv = '';
                }
                $op = self::$searchops[$op];
            }
            $res = [];
            $fields = array_keys(\R::inspect($bean));
            foreach (\R::find($bean, $field.' '.$op.$incv, [$value]) as $bn)
            {
                $bv = new \stdClass();
                foreach ($fields as $f)
                {
                    $bv->$f = $bn->$f;
                }
                $res[] = $bv;
            }
            $context->web()->sendJSON($res);
        }
/**
 * Get a page of bean values
 *
 * @internal
 * @param Context    $context    The context object for the site
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function paging(Context $context) : void
        {
            $fdt = $context->formdata('get');
            $bean = $fdt->mustFetch('bean');
            if (isset(self::$paging[$bean]))
            { // pagination is allowed for this bean
                $this->access->checkPerms($context, self::$paging[$bean][1]); // make sure we are allowed
                $order = $fdt->fetch('order', '');
                $page = $fdt->mustFetch('page');
                $pagesize = $fdt->mustFetch('pagesize');
                $res = \Support\SiteInfo::getinstance()->fetch($bean, ($order !== '' ? ('order by '.$order) : ''), [], $page, $pagesize);
                $context->web()->sendJSON($res);
            }
            else
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
        }
/**
 * Get search hints for a bean
 *
 * @internal
 * @param Context    $context    The context object for the site
 *
 * @throws \Framework\Exception\Forbidden
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final private function hints(Context $context) : void
        {
            $rest = $context->rest();
            $bean = $rest[1];
            if (isset(self::$hints[$bean]))
            { // hinting is allowed for this bean
                $this->access->checkPerms($context, self::$hints[$bean][2]); // make sure we are allowed
                $field = self::$hints[$bean][0];
                $tix = 2;
                if (is_array($field))
                {
                    $tix = 3;
                    if (!in_array($rest[2], $field))
                    {
                        throw new \Framework\Exception\Forbidden('Acess denied');
                    }
                    $field = $rest[2];
                }
                elseif ($field == '*')
                { # the call must specify the field
                    $field = $rest[2];
                    $tix = 3;
                }
                $obj = TRUE;
                if (isset($rest[$tix]))
                {
                    switch ($rest[$tix])
                    {
                    case 'text':
                        $obj = FALSE;
                        break;
                    }
                }
                $this->access->fieldExists($bean, $field); // checks field exists - this implies the the field value is not dangerous to pass directly into the query,
                $ofield = $field;
                $field = '`'.$field.'`';
                $fdt = $context->formdata('get');
                $order = $fdt->fetch('order', $field);
                if ($order !== $field)
                { // strop the fieldname if it occurs in the order spec
                    $order = preg_replace('/\b'.$ofield.'\b/', $field, $order);
                }
                $limit = $fdt->fetch('limit', 10);
                $search = $fdt->fetch('search', '%');
                $res = [];
                foreach (\Support\SiteInfo::getinstance()->fetch($bean,
                    $field.' like ? group by '.$field.($order !== '' ? (' order by '.$order) : '').($limit !== '' ? (' limit '.$limit) : ''), [$search]) as $bn)
                {
                    $v = new \stdClass();
                    $v->value = $obj ? $bn->getID() : $bn->$ofield;
                    $v->text = $bn->$ofield;
                    $res[] = $v;
                }
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
 * @param string|array<string>     $function   The name of a function or an array of names
 * @param array                     $perms     [TRUE if login needed, [roles needed]] where roles are ['context', 'role']
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        final public function operation($function, array $perms) : void
        {
            if (!is_array($function))
            {
                $function = [$function];
            }
            foreach ($function as $f)
            {
                self::$restops[$f] = $perms;
            }
        }
/**
 * Do a database check for uniqueness
 *
 * @param Context   $context  The Context object
 * @param string    $bean     The kind of bean
 * @param string    $field    The field to check
 * @param string    $value    The value to check
 *
 * @return void
 */
        final protected function uniqCheck(Context $context, string $bean, string $field, string $value) : void
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
 * @param \Support\Context   $context
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function unique(Context $context) : void
        {
            [$bean, $field, $value] = $context->restcheck(3);
            $this->access->beanFindCheck($context, 'uniqueperms', $bean, $field);
            $this->uniqCheck($context, $bean, $field, $value);
        }
/**
 * Do a parsley uniqueness check
 *
 * @internal
 * @param \Support\Context    $context
 *
 * @todo this call ought to be rate limited in some way!
 * @todo Possibly should allow for more than just alphanumeric for non-parsley queries???
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function uniquenl(Context $context) : void
        {
            [$bean, $field, $value] = $context->restcheck(3);
            $this->access->beanCheck(self::$uniquenlperms, $bean, $field);
            $this->uniqCheck($context, $bean, $field, $value);
        }
/**
 * Do a parsley table check
 *
 * @internal
 * @param \Support\Context    $context
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function tablecheck(Context $context) : void
        {
            [$name] = $context->restcheck(1);
            if (\Support\SiteInfo::tableExists($name))
            {
                $context->web()->notfound(); // error if it exists....
                /* NOT REACHED */
            }
        }
/**
 * Do a password verification
 *
 * @internal
 * @param \Support\Context    $context
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 * @psalm-suppress UnusedMethod
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private function pwcheck(Context $context) : void
        {
            /** @psalm-suppress PossiblyNullReference */
            if (($pw = $context->formdata('get')->fetch('pw', '')) === '' || !$context->user()->pwok($pw))
            {
                throw new \Framework\Exception\Forbidden('Permission denied');
            }
        }
/**
 * Check that the caller is allowed to perform the operation.
 *
 * @internal
 * @param \Support\Context  $context  The Context Object
 * @param bool              $login    If TRUE Then user must be logged in.
 * @param array             $perms    As specified for the various arrays defined above
 *
 * @return bool  Does not return if user is not allowed.
 */
        private function checkLogin(Context $context, bool $login, array $perms) : bool
        {
            if ($login)
            { # this operation requires a logged in user
                $context->mustbeuser(); // will not return if there is no user
                /* NOT REACHED */
                try
                {
                    $this->access->checkPerms($context, $perms);
                }
                catch (\Framework\Exception\Forbidden $e)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }
/**
 * Constructor
 */
        public function __construct()
        {
            $this->access = new \Framework\Ajax\Access();
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
            if ($context->action() == 'ajax')
            { # REST style AJAX call
                $this->access = new \Framework\Ajax\Access();
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