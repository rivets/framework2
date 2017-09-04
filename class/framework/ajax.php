<?php
    namespace Framework;

    use \R as R;

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
 * @copyright 2014-2017 Newcastle University
 */
/**
 * Handle Ajax operations in this class
 */
    class Ajax
    {
        use \Utility\Singleton;
/**
 * @var array Allowed operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static $ops = array(
            'addcontext'    => array(TRUE, [['Site', 'Admin']]),
            'addform'       => array(TRUE, [['Site', 'Admin']]),
            'addpage'       => array(TRUE, [['Site', 'Admin']]),
            'addrole'       => array(TRUE, [['Site', 'Admin']]),
            'adduser'       => array(TRUE, [['Site', 'Admin']]),
            'confvalue'     => array(TRUE, [['Site', 'Admin']]),
            'delbean'       => array(TRUE, [['Site', 'Admin']]),
            'deluser'       => array(TRUE, [['Site', 'Admin']]),
            'newconf'       => array(TRUE, [['Site', 'Admin']]),
            'toggle'        => array(TRUE, [['Site', 'Admin']]),
            'update'        => array(TRUE, [['Site', 'Admin']]),
        );
/**
 * Add a User
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function adduser($context)
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
 * Add a Form
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addform($context)
        {
            $fdt = $context->formdata();
            $p = R::dispense('form');
            $p->name = $fdt->mustpost('name');
            $p->method = $fdt->mustpost('method');
            $p->multipart = $fdt->mustpost('multipart');
            R::store($p);
            echo $p->getID();
        }
/**
 * Add a Page
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addpage($context)
        {
            $fdt = $context->formdata();
            $p = R::dispense('page');
            $p->name = $fdt->mustpost('name');
            $p->kind = $fdt->mustpost('kind');
            $p->source = $fdt->mustpost('source');
            $p->active = $fdt->mustpost('active');
            $p->admin = $fdt->mustpost('admin');
            $p->needlogin = $fdt->mustpost('login');
            $p->mobileonly = $fdt->mustpost('mobile');
            $p->devel = $fdt->mustpost('devel');
            R::store($p);
            echo $p->getID();
        }
/**
 * Add a Rolename
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function addrole($context)
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
        private function addcontext($context)
        {
            $p = R::dispense('rolecontext');
            $p->name = $context->formdata()->mustpost('name');
            $p->fixed = 0;
            R::store($p);
            echo $p->getID();
        }
/**
 * Change a config value
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function confvalue($context)
        {
            $fdt = $context->formdata();
            $v = R::findOne('fwconfig', 'name=?', array($fdt->mustpost('name')));
            if (!is_object($v))
            {
                $context->web()->bad();
            }
            $v->value = $fdt->mustpost('value');
            R::store($v);
        }
/**
 * Add a new  config value
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function newconf($context)
        {
            $fdt = $context->formdata();
            $v = R::findOne('fwconfig', 'name=?', array($fdt->mustpost('name')));
            if (is_object($v))
            {
                $context->web()->bad();
            }
	    $v = R::dispense('fwconfig');
	    $v->name = $fdt->mustpost('name');
            $v->value = $fdt->mustpost('value');
            R::store($v);
        }
/**
 * Delete a bean
 *
 * The type of bean to be deleted is part of the message
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function delbean($context)
        {
            $fdt = $context->formdata();
            R::trash($context->load($fdt->mustpost('bean'), $fdt->mustpost('id'), Context::R400));
        }
/**
 * Delete a User
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        private function deluser($context)
        {
            R::trash($context->load('user', $context->formdata()->mustpost('id'), Context::R400));
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
        private function toggle($context)
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
        private function update($context)
        {
            $fdt = $context->formdata();

            $bn = $context->load($fdt->mustpost('bean'), $fdt->mustpost('id'), Context::R400);
            $field = $fdt->mustpost('name');
            $bn->$field = $fdt->mustpost('value');
            R::store($bn);
        }
/**
 * Handle AJAX operations
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public function handle($context)
        {
            $fdt = $context->formdata();
            if (($lg = $fdt->get('login', '')) !== '')
            { # this is a parsley generated username check call
                if (R::count('user', 'login=?', array($lg)) > 0)
                {
                    return $context->web()->notfound(); // error if it exists....
                }
            }
            else
            {
                $op = $fdt->mustpost('op');
                if (isset(self::$ops[$op]))
                { # a valid operation
                    $curop = self::$ops[$op];
                    if ($curop[0])
                    { # this operation requires a logged in user
                        $context->mustbeuser();
                    }
                    //if ($curop[1])
                    //{ # this operation needs admin privileges
                    //    $context->mustbeadmin();
                    //}
                    //if ($curop[2])
                    //{ # this operation needs developer privileges
                    //    $context->mustbedeveloper();
                    //}
                    foreach ($curop[1] as $rcs)
                    {
                        if (!$context->user()->hasrole($rcs[0], $rcs[1]))
                        {
                            $context->web()->noaccess();
                        }
                    }
                    $this->{$op}($context);
                }
                else
                { # return a 400
                    $context->web()->bad();
                }
            }
        }
    }
?>
