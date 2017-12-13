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
 * @copyright 2014-2017 Newcastle University
 */
    namespace Framework;

    use \R as R;
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
 * Make a twig file if we have permission
 *
 * @param string    $page   The name of the page
 * @param string    $name   The name of the twig
 *
 * @return void
 */
        private function maketwig($context, $page, $name)
        {
            $file = $context->local()->makebasepath('twigs', $name);
            if (!file_exists($file))
            { // make the file
                $fd = fopen($file, 'w');
                if ($fd !== FALSE)
                {
                    fwrite($fd,'{% extends \'page.twig\' %}

{# this brings in some useful macros for making forms
{% import \'form.twig\' as f %}
#}

{# put a string in this block that will appear as the title of the page
{% block title %}
{% endblock title %}
#}

{% block links %}
{# <link> for non-css and non-type things#}
{% endblock links %}

{% block type %}
{# <link> for webfonts #}
{% endblock type %}


{% block css %}
{# <link> for any other CSS files you need #}
{% endblock css %}

{% block scripts %}
{# <script src=""></script> for any other JS files you need #}
{% endblock scripts %}

{% block setup %}
{# Any javascript you need that is NOT run on load goes in this block. NB you don\'t need <script></script> tags  here #}
{% endblock setup %}

{% block onload %}
{# Any javascript you need that MUST run on load goes in this block. NB you don\'t need <script></script> tags  here #}
{% endblock onload %}

{# If you include this, then the navigation bar in page.twig will **NOT** appear
{% block navigation %}
{% endblock navigation %}
#}

{#
    Edit the file navbar.twig to change the appearance of the
    navigation bar. It is included by default from page.twig
#}

{# uncomment this and delete header block to remove the <header> tag altogether
{% block pageheader %}
{% endblock pageheader %}
#}

{#
    If you have a standard header for all (most) pages then put the
    content in the file header.twig. It is included by page.twig by
    default. You then don\'t need to have a header block either.
#}

{% block header %}
    <article class="col-md-12">
        <h1 class="cntr">'.strtoupper($page).'</h1>
    </article>
{% endblock header %}

{% block main %}
    <section class="row">
        <article class="ml-auto col-md-8 mr-auto">
            <p>Coming soon</p>
        </article>
    </section>
{% endblock main %}

{# uncomment this  and delete footer block to remove the <footer> tag altogether
{% block pagefooter %}
{% endblock pagefooter %}
#}

{#
    If you have a standard footer for all (most) pages then put the
    content in the file footer.twig. It is included by page.twig by
    default. You then don\'t need to have a footer block either.
#}

{% block footer %}
{% endblock footer %}
');
                    fclose($fd);
                }
            }
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
            \Framework\Debug::vdump($_POST);
            $fdt = $context->formdata();
            $p = R::dispense('page');
            $p->name = $fdt->mustpost('name');
            $p->kind = $fdt->mustpost('kind');
            $p->source = $fdt->mustpost('source');
            $p->active = $fdt->mustpost('active');
            $p->needlogin = $fdt->mustpost('login');
            $p->mobileonly = $fdt->mustpost('mobile');
            R::store($p);

            try
            {
                foreach ($fdt->posta('context') as $ix => $cid)
                { // context, role, start, end, otherinfo
                    if ($cid !== '')
                    {
                        $start = $fdt->mustpost(['start', $ix], Context::RTHROW);
                        $end = $fdt->mustpost(['end', $ix], Context::RTHROW);
                        $p->addrolebybean(
                            $context->load('rolecontext', $cid, Context::RTHROW),
                            $context->load('rolename', $fdt->mustpost(['role', $ix], Context::RTHROW)),
                            $fdt->mustpost(['otherinfo', $ix], Context::RTHROW),
                            ($start === '' || strtolower($start) == 'now') ? $context->utcnow() : $start,
                            ($end === '' || strtolower($end) == 'never') ? NULL : $end
                        );
                    }
                }
                $local = $context->local();
                switch ($p->kind)
                {
                case SiteAction::OBJECT:
                    $tl = strtolower($p->source);
                    $src = preg_replace('/\\\\/', DIRECTORY_SEPARATOR, $tl).'.php';
                    $file = $local->makebasepath('class', $src);
                    if (!file_exists($file))
                    { // make the file
                        $fd = fopen($file, 'w');
                        if ($fd !== FALSE)
                        {
                            fwrite($fd, '<?php
/**
 * A class that contains code to handle any requests for  /'.$tl.'
 */
/**
 * Support // or /home/'.$tl.'
 */
    class '.$p->source.' extends \\Framework\\Siteaction
    {
/**
 * Handle '.$tl.' operations /
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
        public function handle($context)
        {
            return \''.$tl.'.twig\';
        }
    }
?>');
                            fclose($fd);
                        }
                    }
                    $this->maketwig($context, $tl, $tl.'.twig');
                    break;
                case SiteAction::TEMPLATE:
                    $this->maketwig($context, $p->name, $p->source);
                    break;
                case SiteAction::REDIRECT:
                case SiteAction::REHOME:
                case SiteAction::XREDIRECT:
                case SiteAction::XREHOME:
                    break;
                }
                echo $p->getID();
            }
            catch (Exception $e)
            { // clean up the page we made above. This will cascade deleete any pageroles that might have been created
                R::trash($p);
                $context->web()->bad($e->getmessage());
            }
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
 * Add an operation
 *
 * @param string    $function   The name of a function
 * @param array     $perms      [TRUE if login needed, [roles needed]] where roles are ['context', 'role']
 *
 * @return void
 */
        public function operation($function, $perms)
        {
            self::$ops[$function] = $perms;
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
                    foreach ($curop[1] as $rcs)
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
                            }
                        }
                        else
                        {
                            if ($context->user()->hasrole($rcs[0], $rcs[1]) === FALSE)
                            {
                                $context->web()->noaccess();
                            }
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
