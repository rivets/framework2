<?php
/**
 * A model class for the RedBean object Page
 *
 * This is a Framework system class - do not edit!
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017-2020 Newcastle University
 * @package Framework
 * @subpackage SystemModel
 */
    namespace Model;

    use \Config\Framework as FW;
    use \Framework\Dispatch;
    use \Framework\Exception\BadValue;
    use \Support\Context;
/**
 * A class implementing a RedBean model for Page beans
 * @psalm-suppress UnusedClass
 */
    class Page extends \RedBeanPHP\SimpleModel
    {
/**
 * @var string   The type of the bean that stores roles for this page
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private $roletype = FW::PAGEROLE;
/**
 * @var Array   Key is name of field and the array contains flags for checkboxes
 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements
 */
        private static $editfields = [
            'name'          => [TRUE, FALSE],
            'kind'          => [TRUE, FALSE],
            'source'        => [TRUE, FALSE],
            'active'        => [TRUE, TRUE],
            'mobileonly'    => [TRUE, TRUE],
            'needlogin'     => [TRUE, TRUE],
            'needajax'      => [TRUE, TRUE],
            'needfwutils'   => [TRUE, TRUE],
            'needparsley'   => [TRUE, TRUE],
            'neededitable'  => [TRUE, TRUE],
        ];

        use \ModelExtend\FWEdit;
        use \Framework\Support\HandleRole;
        use \ModelExtend\MakeGuard;
/**
 * Function called when a page bean is updated - do error checking in here
 *
 * @throws \Framework\Exception\BadValue
 * @return void
 */
        public function update() : void
        {
            $this->bean->name = strtolower($this->bean->name);
            if (!preg_match('/^[a-z][a-z0-9]*/', $this->bean->name))
            {
                throw new BadValue('Invalid page name');
            }
            \Framework\Dispatch::check($this->bean->kind, $this->bean->source);
        }
/**
 * Check user can access the page - does not return if they cannot
 *
 * @param \Support\Context    $context    The context object
 *
 * @psalm-suppress PossiblyNullReference - we know we have a user when we call context->user
 *
 * @return void
 */
        public function check(Context $context) : void
        {
            if ($this->bean->needlogin)
            {
                if (!$context->hasuser())
                { // not logged in
                    $context->divert('/login/?goto='.urlencode($context->local()->debase($_SERVER['REQUEST_URI'])), TRUE, 'You must login');
                    /* NOT REACHED */
                }
                if (\R::count(FW::PAGEROLE, 'page_id=?', [$this->bean->getID()]) > 0)
                { // there are roles to check
                    $match = \R::getCell('select count(p.id) = count(r.id) and count(p.id) != 0 from '.FW::USER.
                        ' as u inner join role as r on u.id = r.'.FW::USER.'_id inner join (select * from '.
                        FW::PAGEROLE.' where '.FW::PAGE.'_id=?) as p on p.'.FW::ROLENAME.'_id = r.'.FW::ROLENAME.
                        '_id and p.'.FW::ROLECONTEXT.'_id = r.'.FW::ROLECONTEXT.'_id where u.id=?',
                        [$this->bean->getID(), $context->user()->getID()]);
                    if (!$match ||                                          // User does not have all the required roles
                        ($this->bean->mobileonly && !$context->hasToken())) // not mobile and logged in
                    {
                        \Framework\Dispatch::basicSetup($context, 'error');
                        $context->web()->sendString($context->local()->getRender('@error/403.twig'), \Framework\Web\Web::HTMLMIME, \Framework\Web\StatusCodes::HTTP_FORBIDDEN);
                        exit;
                    }
                }
            }
        }
/**
 * Make a twig file if we have permission
 *
 * @param \Support\Context    $context    The Context object
 * @param string    $name       The name of the twig
 *
 * @return void
 */
        private static function maketwig(Context $context, array $name) : void
        {
            $file = $context->local()->makebasepath('twigs', ...$name);
            if (!file_exists($file))
            { // make the file
                $fd = fopen($file, 'w');
                if ($fd !== FALSE)
                {
                    fwrite($fd, file_get_contents($context->local()->makebasepath('twigs', 'content', 'sample.txt')));
                    fclose($fd);
                }
            }
        }
/**
 * Add a Page
 *
 * This will be called from ajax.php
 *
 * @param Context   $context    The context object for the site
 *
 * @return \RedBeanPHP\OODBBean
 */
        public static function add(Context $context) : \RedBeanPHP\OODBBean
        {
            $fdt = $context->formdata('post');
            $p = \R::dispense('page');
            foreach (['name', 'kind', 'source'] as $fld)
            { // mandatory
                $p->{$fld} = $fdt->mustFetch($fld);
            }
            foreach (['active', 'needlogin', 'mobileonly', 'needajax', 'needfwutils', 'needparsley', 'neededitable'] as $fld)
            { // optional flags
                $p->{$fld} = $fdt->fetch($fld, 0);
            }
            try
            {
                \R::store($p);
                foreach ($fdt->fetchArray('context') as $ix => $cid)
                { // context, role, start, end, otherinfo
                    if ($cid !== '')
                    {
                        $p->addRoleByBean(
                            $context->load(FW::ROLECONTEXT, $cid),                         // the context id
                            $fdt->mustFetchBean(['role', $ix], FW::ROLENAME),   // the rolename id
                            $fdt->mustFetch(['otherinfo', $ix]),
                            $fdt->mustFetch(['start', $ix]),
                            $fdt->mustFetch(['end', $ix])
                        );
                    }
                }
                $local = $context->local();
                switch ($p->kind)
                {
                case Dispatch::OBJECT:
                    if (!preg_match('/\\\\/', $p->source))
                    { // no namespace so put it in \Pages
                        $p->source = '\\Pages\\'.$p->source;
                        \R::store($p);
                    }
                    $tl = strtolower($p->source);
                    $tspl = explode('\\', $p->source);
                    $base = array_pop($tspl);
                    $lbase = strtolower($base);
                    $namespace = implode('\\', array_filter($tspl));
                    $src = preg_replace('/\\\\/', DIRECTORY_SEPARATOR, $tl).'.php';
                    $file = $local->makebasepath('class', $src);
                    if (!file_exists($file))
                    { // make the file
                        $fd = fopen($file, 'w');
                        if ($fd !== FALSE)
                        {
                            fwrite($fd, '<?php
/**
 * A class that contains code to handle any requests for  /'.$p->name.'/
 *
 * @author Your Name <Your@email.org>
 * @copyright year You
 * @package Framework
 * @subpackage UserPages
 */
    namespace '.$namespace.';

    use \\Support\\Context as Context;
/**
 * Support /'.$p->name.'/
 */
    class '.$base.' extends \\Framework\\Siteaction
    {
/**
 * Handle '.$p->name.' operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return string|array   A template name
 */
        public function handle(Context $context)
        {
            return \'@content/'.$lbase.'.twig\';
        }
    }
?>');
                            fclose($fd);
                        }
                    }
                    self::maketwig($context, ['content', $lbase.'.twig']);
                    break;
                case Dispatch::TEMPLATE:
                    if (!preg_match('/\.twig$/', $p->source))
                    { // doesn't end in .twig
                        $p->source .= '.twig';
                    }
                    else
                    { // sometimes there are extra .twig extensions...
                        $p->source = preg_replace('/(\.twig)+$/', '.twig', $p->source); // this removes extra .twigs .....
                    }
                    if (!preg_match('/^@/', $p->source))
                    { // no namespace so put it in @content
                        $p->source = '@content/'.$p->source;
                        $name = ['content', $p->source];
                        \R::store($p);
                    }
                    elseif (preg_match('%^@content/(.*)%', $p->source, $m))
                    { // this is in the User twig content directory
                        $name = ['content', $m[1]];
                    }
                    elseif (preg_match('%@([a-z]+)/(.*)%', $name, $m))
                    { // this is using a system twig
                        $name = ['framework', $m[1], $m[2]];
                    }
                    self::maketwig($context, $name);
                    break;
                case Dispatch::REDIRECT:
                case Dispatch::REHOME:
                case Dispatch::XREDIRECT:
                case Dispatch::XREHOME:
                    break;
                }
                return $p;
            }
            catch (\Exception $e)
            { // clean up the page we made above. This will cascade delete any pageroles that might have been created
                \R::trash($p);
                throw $e; // throw it up to the handlers above
            }
        }
/**
 * Setup for an edit
 *
 * @param Context    $context  The context object
 *
 * @return void
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function startEdit(Context $context, array $rest) : void
        {
        }
/**
 * Handle an edit form for this page
 *
 * @param Context   $context    The context object
 *
 * @return array [TRUE if error, [error messages]]
 */
        public function edit(Context $context) : array
        {
            $emess = $this->dofields($context->formdata('post'));

            $this->editroles($context);
            $admin = $this->hasrole(FW::FWCONTEXT, FW::ADMINROLE);
            if (is_object($devel = $this->hasrole(FW::FWCONTEXT, FW::DEVELROLE)) && !is_object($admin))
            { // if we need developer then we also need admin
                $admin = $this->addrole(FW::FWCONTEXT, FW::ADMINROLE, '-', $devel->start, $devel->end);
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