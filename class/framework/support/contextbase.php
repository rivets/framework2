<?php
/**
 * Contains the definition of the Context class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Config\Config;
    use \Config\Framework as FW;
/**
 * A class that stores various useful pieces of data for access throughout the rest of the system.
 */
    class ContextBase
    {
        use \Framework\Utility\Singleton;

/** @var ?\RedBeanPHP\OODBBean  NULL or an object decribing the current logged in User (if we have logins at all) */
        protected $luser        = NULL;
/** @var string The first component of the current URL */
        protected $reqaction    = 'home';
/** @var array<string>    The rest of the current URL exploded at / */
        protected $reqrest      = [];
/** @var bool   True if authenticated by token */
        protected $tokenAuth    = FALSE;
/** @var array<\RedBeanPHP\OODBBean>            A cache for rolename beans */
        protected $roles        = [];
/** @var array<\RedBeanPHP\OODBBean>            A cache for rolecontext beans */
        protected $contexts     = [];
/** @var array<array<string>>                   A cache for JS ons */
        protected $ons          = [];
/** @var array<\Framework\FormData\Base>        FormData handler cache */
        protected $getters      = [];
/*
 ***************************************
 * URL and REST support functions
 ***************************************
 */
/**
 * Return the main action part of the URL as set by .htaccess
 *
 * @return string
 */
        public function action()
        {
            return $this->reqaction;
        }
/**
 * Return the part of the URL after the main action as set by .htaccess
 *
 * See setup() below for how the URL is processed to create the result array.
 *
 * Note that if there is nothing after the action in the URL this function returns
 * an array with a single element containing an empty string.
 *
 * @return array<string>
 */
        public function rest()
        {
            return $this->reqrest;
        }
/**
 ***************************************
 * User related functions
 ***************************************
 */
/**
 * Return the current logged in user if any
 *
 * @return ?\RedBeanPHP\OODBBean
 */
        public function user() : ?\RedBeanPHP\OODBBean
        {
            return $this->luser;
        }
/**
 * Do we have a logged in user?
 *
 * @return bool
 */
        public function hasUser() : bool
        {
            return is_object($this->luser);
        }
/**
 * Find out if this was validated using a token, if so, it is coming from a device not a browser
 *
 * @return bool
 */
        public function hasToken() : bool
        {
            return $this->tokenAuth;
        }
/*
 ***************************************
 * Miscellaneous utility functions
 ***************************************
 */
/**
 * Save values into the on cache
 *
 * @param string   $id
 * @param string   $on
 * @param string   $fn
 *
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function saveOn($id, $on, $fn) : void
        {
            $this->ons[$id][$on] = $fn;
        }
/**
 * Get the JS for onloading the ons
 *
 * @return string
 * @psalm-suppress PossiblyUnusedMethod
 * @phpcsSuppress PhpCs.StringNotation.SingleQuoteFixer
 */
        public function getOns()
        {
            $res = '';
            foreach ($this->ons as $id => $conds)
            {
                $xres = '';
                foreach ($conds as $on => $fn)
                {
                    $xres .= ".on('".$on."', ".$fn.')';
                }
                $res .= "$('#".$id."')".$xres.";\n";
            }
            return $res;
        }
/**
 * Find a rolename bean
 *
 * @param string    $name   A Role name
 *
 * @throws \Framework\Exception\InternalError
 *
 * @return \RedBeanPHP\OODBBean
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function roleName(string $name) : \RedBeanPHP\OODBBean
        {
            if (!isset($this->roles[$name]))
            {
                if (!is_object($bn = \R::findOne(FW::ROLENAME, 'name=?', [$name])))
                {
                    throw new \Framework\Exception\InternalError('Missing role name: '.$name);
                }
                $this->roles[$name] = $bn;
            }
            return $this->roles[$name];
        }
/**
 * Find a rolecontext bean
 *
 * @param string    $name   A Role Context
 *
 * @throws \Framework\Exception\InternalError
 *
 * @return \RedBeanPHP\OODBBean
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function roleContext(string $name) : \RedBeanPHP\OODBBean
        {
            if (!isset($this->contexts[$name]))
            {
                if (!is_object($bn = \R::findOne(FW::ROLECONTEXT, 'name=?', [$name])))
                {
                    throw new \Framework\Exception\InternalError('Missing context name: '.$name);
                }
                $this->contexts[$name] = $bn;
            }
            return $this->contexts[$name];
        }
/**
 * Load a bean
 *
 * @param string    $bean       A bean type name
 * @param int       $id         A bean id
 * @param bool      $forupdate  If TRUE then use loadforupdate
 *
 * R::load returns a new bean with id 0 if the given id does not exist.
 *
 * @throws  \Framework\Exception\MissingBean
 * @throws  \InvalidArgumentException - this would be an internal error
 *
 * @return \RedBeanPHP\OODBBean
 */
        public function load(string $bean, int $id, bool $forupdate = FALSE) : \RedBeanPHP\OODBBean
        {
            $foo = $forupdate ? \R::loadforupdate($bean, $id) : \R::load($bean, $id);
            if ($foo->getID() == 0)
            {
                throw new \Framework\Exception\MissingBean('Missing '.$bean);
            }
            return $foo;
        }
/**
 * Return the local object
 *
 * @psalm-suppress MoreSpecificReturnType
 *
 * @return \Framework\Local
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
        public function local() : \Framework\Local
        {
            return \Framework\Local::getInstance();
        }
/**
 * Return a Formdata object
 *
 * @param ?string $which
 *
 * @return object
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
        public function formData(?string $which = NULL) : object
        {
            if ($which == NULL)
            { // this is backward compatibility and will be removed in the future
                return \Framework\Support\FormData::getInstance();
            }
            if (!isset($this->getters[$which]))
            {
                $class = '\Framework\FormData\\'.ucfirst($which);
                $this->getters[$which] = new $class();
            }
            return $this->getters[$which];
        }
/**
 * Return the Web object
 *
 * @return \Framework\Web\Web
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
        public function web() : \Framework\Web\Web
        {
            return \Framework\Web\Web::getInstance();
        }
/*
 ***************************************
 * Setup the Context - the constructor is hidden in Singleton
 ***************************************
 */
/**
 * Look for a mobile access token
 *
 * @internal
 *
 * @return void
 * @throws \Framework\Exception\InternalError
 */
        private function mtoken() : void
        {
            // This has to be a loop as we have no guarantees of the case of the keys in the returned array.
            $auth = array_filter(getallheaders(), static function ($key) {
                return FW::AUTHTOKEN === strtoupper($key);
            }, ARRAY_FILTER_USE_KEY);
            if (!empty($auth))
            { // we have mobile authentication in use
                try
                {
                    /** @psalm-suppress UndefinedClass - the JWT code is not included in the psalm tests at the moment */
                    $tok = \Framework\Utility\JWT\JWT::decode(array_shift($auth), FW::AUTHKEY);
                }
                catch (\Exception $e)
                { // token error of some kind so return no access.
                    $this->web()->noaccess($e->getMessage());
                    /* NOT REACHED */
                }
                if (is_object($this->luser))
                {
                    if ($this->luser->getID() != $tok->sub)
                    {
                        throw new \Framework\Exception\InternalError('User conflict');
                        /* NOT REACHED */
                    }
                }
                else
                {
                    $this->luser = $this->load('user', $tok->sub);
                }
                $this->tokenAuth = TRUE;
            }
        }
/**
 * Initialise the context and return self
 *
 * @return \Framework\Support\ContextBase
 */
        public function setup() : \Framework\Support\ContextBase
        {
            ini_set('session.use_only_cookies', TRUE); // make sure PHP is set to make sessions use cookies only
            ini_set('session.use_trans_sid', FALSE);   // this helps a bit towards making session hijacking more difficult
            ini_set('session.cookie_httponly', 1);
            if (isset($_COOKIE[Config::SESSIONNAME]))
            {# see if there is a user variable in the session....
                /** @psalm-suppress UnusedFunctionCall */
                session_start(['name' => Config::SESSIONNAME]);
                if (isset($_SESSION['user']))
                {
                    $this->luser =  $_SESSION['user'];
                    $this->luser->fresh();
                }
            }
            $this->mtoken();

            if (isset($_SERVER['REDIRECT_URL']) && !preg_match('/index.php/', $_SERVER['REDIRECT_URL']))
            {
/*
 *  Apache v 2.4.17 changed the the REDIRECT_URL value to be a full URL, so we need to strip this.
 *  Older versions will not have this so the code will do nothing.
 */
                $uri = preg_replace('#^https?://[^/]+#', '', $_SERVER['REDIRECT_URL']);
            }
            else
            {
                $uri = $_SERVER['REQUEST_URI'];
            }
            if ($_SERVER['QUERY_STRING'] !== '')
            { // there is a query string so get rid it of it from the URI
                [$uri] = explode('?', $uri);
            }
            $req = array_filter(explode('/', $uri)); // array_filter removes empty elements - trailing / or multiple /
/*
 * If you know that the base directory is empty then you can delete the next test block.
 *
 * You can also optimise out the loop if you know how deep you are nested in sub-directories
 *
 * The code here is to make it easier to move your code around within the hierarchy. If you don't need
 * this then optimise the hell out of it.
 */
            if ($this->local()->base() !== '')
            { // we are in at least one sub-directory
                $bsplit = array_filter(explode('/', $this->local()->base()));
                foreach (range(1, count($bsplit)) as $c)
                {
                    array_shift($req); // pop off the directory name...
                }
            }
            if (!empty($req))
            { // there was something after the domain name so split it into action and rest...
                $this->reqaction = strtolower(array_shift($req));
                $this->reqrest = empty($req) ? [''] : array_values($req);
            }
            return $this;
        }
    }
?>