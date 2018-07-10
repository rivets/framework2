<?php
/**
 * A model class for the RedBean object Page
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2018 Newcastle University
 *
 */
    namespace Model;
    
    use \Framework\SiteAction as SiteAction;
    use \Support\Context as Context;
/**
 * A class implementing a RedBean model for Page beans
 */
/**
 * @var string   The type of the bean that stores roles for this page
 */
    class Page extends \RedBeanPHP\SimpleModel
    {
        private $roletype = 'pagerole';

        use \Framework\HandleRole;
/**
 * @var Array   Key is name of field and the array contains flags for checks
 */
        private static $editfields = [
            'name'          => [TRUE, FALSE],
            'kind'          => [TRUE, FALSE],
            'source'        => [TRUE, FALSE],
            'active'        => [TRUE, TRUE],
            'mobileonly'    => [TRUE, TRUE],
            'needlogin'     => [TRUE, TRUE],
        ];
/**
 * Check user can access the page
 *
 * @param object    $context    The context object
 *
 * @return boolean
 */
        public function check($context)
        {
            if ($this->bean->needlogin)
            {
                if (!$context->hasuser())
                { # not logged in
                    $context->divert('/login?page='.urlencode($context->local()->debase($_SERVER['REQUEST_URI'])));
                    /* NOT REACHED */
                }
                $match = \R::getCell('select count(p.id) = 0 OR (count(p.id) = count(r.id) and count(p.id) != 0) from user as u inner join role as r on u.id = r.user_id inner join '.
                    '(select * from pagerole where page_id=?) as p on p.rolename_id = r.rolename_id and p.rolecontext_id = r.rolecontext_id where u.id=?',
                    [$this->bean->getID(), $context->user()->getID()]);
                if (!$match ||                                          // User does not have all the required roles
                    ($this->bean->mobileonly && !$context->hastoken()))	// not mobile and logged in
                {
                    $context->web()->sendstring($context->local()->getrender('@error/403.twig'), \Framework\Web\Web::HTMLMIME, \Framework\Web\StatusCodes::HTTP_FORBIDDEN);
                    exit;
                }
           }
        }
/**
 * Make a twig file if we have permission
 *
 * @param string    $page   The name of the page
 * @param string    $name   The name of the twig
 *
 * @return void
 */
        private static function maketwig($context, $page, $name)
        {
            if (preg_match('%@content/(.*)%'. $name, $m))
            {
                $name = $m[1];
            }
            elseif (preg_match('%@([a-z]+)/(.*)%'. $name, $m))
            {
                $name = 'framework/'.$m[1]/$m[2];
            }
            $name = preg_replace('/(\.twig)*$/', '.twig', $name); // this adds a missing .twig and removes extra .twigs .....
            $file = $context->local()->makebasepath('twigs', $name);
            if (!file_exists($file))
            { // make the file
                $fd = fopen($file, 'w');
                if ($fd !== FALSE)
                {
                    fwrite($fd,'{% set noajax = FALSE %}
{% extends \'@content/page.twig\' %}

{# this brings in some useful macros for making forms
{% import \'@util/formmacro.twig\' as f %}
#}

{# this brings in some useful macros for making bootstrap modals
{% import \'@util/modalmacro.twig\' as f %}
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

{# If you include this, then the navigation bar in @util/page.twig will **NOT** appear
{% block navigation %}
{% endblock navigation %}
#}

{#
    Edit the file navbar.twig to change the appearance of the
    navigation bar. It is included by default from @util/page.twig
#}

{# uncomment this and delete header block to remove the <header> tag altogether
{% block pageheader %}
{% endblock pageheader %}
#}

{#
    If you have a standard header for all (most) pages then put the
    content in the file header.twig. It is included by @util/page.twig by
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
    content in the file footer.twig. It is included by @util/page.twig by
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
 * This will be called from ajax.php
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public static function add($context)
        {
            $fdt = $context->formdata();
            $p = \R::dispense('page');
            $p->name = $fdt->mustpost('name');
            $p->kind = $fdt->mustpost('kind');
            $p->source = $fdt->mustpost('source');
            $p->active = $fdt->mustpost('active');
            $p->needlogin = $fdt->mustpost('login');
            $p->mobileonly = $fdt->mustpost('mobile');
            \R::store($p);

            try
            {
                foreach ($fdt->posta('context') as $ix => $cid)
                { // context, role, start, end, otherinfo
                    if ($cid !== '')
                    {
                        $p->addrolebybean(
                            $context->load('rolecontext', $cid, Context::RTHROW),
                            $context->load('rolename', $fdt->mustpost(['role', $ix], Context::RTHROW)),
                            $fdt->mustpost(['otherinfo', $ix], Context::RTHROW),
                            $fdt->mustpost(['start', $ix], Context::RTHROW),
                            $fdt->mustpost(['end', $ix], Context::RTHROW)
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
 * A class that contains code to handle any requests for  /'.$tl.'/
 */
/**
 * Support /'.$tl.'/
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
                    self::maketwig($context, $tl, $tl.'.twig');
                    break;
                case SiteAction::TEMPLATE:
                    self::maketwig($context, $p->name, $p->source);
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
                \R::trash($p);
                $context->web()->bad($e->getmessage());
            }
        }
/**
 * Handle an edit form for this page
 *
 * @param object   $context    The context object
 *
 * @return array [TRUE if error, [error messages]]
 */
        public function edit($context)
        {
            $emess = [];
            $fdt = $context->formdata();
            foreach (self::$editfields as $fld => $flags)
            { // might need more fields for different applications
                $val = $fdt->post($fld, $flags[1] ? 0 : ''); # might be a flag
                if ($flags[0] && $val === '')
                { // this is an error as this is a required field
                    $emess = [$fld.' is required'];

                }
                elseif ($val != $this->bean->$fld)
                {
                    $this->bean->$fld = $val;
                }
            }
            \R::store($this->bean);

            $this->editroles($context);
            $admin = $this->hasrole('Site', 'Admin');
            if (is_object($devel = $this->hasrole('Site', 'Developer')) && !is_object($admin))
            { // if we need developer then we also need admin
                $admin = $this->addrole('Site', 'Admin', '-', $devel->start, $devel->end);
            }
            if (is_object($admin) && !$this->bean->needlogin)
            { // if we need admin then we also need login!
                $this->bean->needlogin = 1;
                \R::store($this->bean);
            }
            return [!empty($emess), $emess];
        }
    }
?>
