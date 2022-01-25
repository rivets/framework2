<?php
/**
 * Contains the definition of the Context class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2022 Newcastle University
 * @package Framework\Framework\Support
 */
    namespace Framework\Support;

    use \Config\Config;
    use \Config\Framework as FW;
    use \RedBeanPHP\OODBBean;
/**
 * A class that stores various useful pieces of data for access throughout the rest of the system.
 *
 * This is a base class for the derived class \Framework\Context which adds
 * more features. This exists simply to reduce the overall complexity/density of the code rather
 * than to provide any other possibilities.
 *
 * Data that is local to the installation should be handled by the \Framework\Local class.
 */
    class ContextBase
    {
        use \Framework\Utility\Singleton;

/** @var ?OODBBean NULL or an object decribing the current logged in User (if we have logins at all) */
        protected ?OODBBean $luser        = NULL;
/** @var string The first component of the current URL */
        protected string $reqaction    = 'home';
/** @var array<string> The rest of the current URL exploded at / */
        protected array $reqrest      = [''];
/** @var bool True if authenticated by JWT token */
        protected bool $tokenAuth    = FALSE;
/** @var array<OODBBean>            A cache for rolename beans */
        protected array $roles        = [];
/** @var array<OODBBean>            A cache for rolecontext beans */
        protected array $contexts     = [];
/** @var array<array<string>>                   A cache for JS ons */
        protected array $ons          = [];
/** @var array<\Framework\FormData\Base>        FormData handler cache */
        protected array $getters      = [];
/*
 ***************************************
 * URL and REST support functions
 ***************************************
 */
/**
 * Return the main action part of the URL as set by .htaccess
 *
 * The framework trats a URL as /action/rest... The action returned is always in lowercase.
 */
        public function action() : string
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
        public function rest() : array
        {
            return $this->reqrest;
        }
/**
 ***************************************
 * User related functions
 ***************************************
 */
/**
 * Return the current logged in user bean, if any
 */
        public function user() : ?OODBBean
        {
            return $this->luser;
        }
/**
 * Do we have a logged in user?
 */
        public function hasUser() : bool
        {
            return \is_object($this->luser);
        }
/**
 * Find out if this was validated using a JWT token, if so, it is (probably) coming from a device not a browser
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
 * ON JavaScript on tags is saved up so that it can all be generated into a single block
 * that can be hashed/nonced so that CSP does not complain.
 *
 * @param $id The id for the tag
 * @param $on The type of on (e.g. click, load etc.)
 * @param $fn The code to be executed
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function saveOn(string $id, string $on, string $fn) : void
        {
            $this->ons[$id][$on] = $fn;
        }
/**
 * Get the JS for onloading the ons
 *
 * This generates a block of vanilla JavaScript that will set up all the necessary in conditions.
 *
 * @psalm-suppress PossiblyUnusedMethod
 * @phpcsSuppress PhpCs.StringNotation.SingleQuoteFixer
 */
        public function getOns() : string
        {
            $res = '';
            foreach ($this->ons as $id => $conds)
            {
                foreach ($conds as $on => $fn)
                {
                    $res .= "document.getElementById('".$id."').addEventListener('".$on."', ".$fn.');'.PHP_EOL;
                }
            }
            return $res;
        }
/**
 * Helper function for role/context names
 */
        private function getRCName(string $bean, string $holder, string $name) : OODBBean
        {
            if (!isset($this->{$holder}[$name]))
            {
                if (!is_object($bn = \R::findOne($bean, 'name=?', [$name])))
                {
                    throw new \Framework\Exception\InternalError('Missing role name: '.$name);
                }
                $this->{$holder}[$name] = $bn;
            }
            return $this->{$holder}[$name];
        }
/**
 * Find a rolename bean
 *
 * This will load the name cache if needed and then return the relevant bean (if it exists). The
 * existence test could be replaced by an assert if you really wanted.
 *
 * @param  $name   A Role name
 *
 * @throws \Framework\Exception\InternalError
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function roleName(string $name) : OODBBean
        {
            return $this->getRCName(FW::ROLENAME, 'roles', $name);
        }
/**
 * Find a rolecontext bean
 *
 * This will load the name cache if needed and then return the relevant bean (if it exists). The
 * existence test could be replaced by an assert if you really wanted.
 *
 * @param $name   A Context name
 *
 * @throws \Framework\Exception\InternalError
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function roleContext(string $name) : OODBBean
        {
            return $this->getRCName(FW::ROLECONTEXT, 'contexts', $name);
        }
/**
 * Load a bean that must exist, otherwise throw an exception.
 *
 * R::load returns a new bean with id 0 if the given id does not exist. This function throws an exception
 * if that happens as it is assumed that the bean must exist.
 *
 * @param $bean       A bean type name
 * @param $id         A bean id
 * @param $forupdate  If TRUE then use loadforupdate
 * @param $msg        A custom error message
 *
 * @throws  \Framework\Exception\MissingBean
 */
        public function load(string $bean, int $id, bool $forupdate = FALSE, string $msg = '') : OODBBean
        {
            $foo = $forupdate ? \R::loadForUpdate($bean, $id) : \R::load($bean, $id);
            if ($foo->getID() == 0)
            {
                throw new \Framework\Exception\MissingBean($msg !== '' ? $msg : 'Missing '.$bean);
            }
            return $foo;
        }
/**
 * Return the Local singleton
 *
 * @psalm-suppress MoreSpecificReturnType
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
        public function local() : \Framework\Local
        {
            return \Framework\Local::getInstance(); // @phan-suppress-current-line PhanTypeMismatchReturn
        }
/**
 * Return a Formdata object
 *
 * @param $which The formdata object needed - get, post, put, file, cookie
 *
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
        public function formData(string $which) : \Framework\FormData\Base
        {
            $which = strtolower($which);
            if (!isset($this->getters[$which]))
            {
                $class = '\Framework\FormData\\'.ucfirst($which);
                $this->getters[$which] = new $class();
            }
            return $this->getters[$which];
        }
/**
 * Return the Web singleton
 *
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
        public function web() : \Framework\Web\Web
        {
            return \Framework\Web\Web::getInstance(); // @phan-suppress-current-line PhanTypeMismatchReturn
        }
/*
 ***************************************
 * Setup the Context - the constructor is hidden in Singleton
 ***************************************
 */
/**
 * Look for a mobile access JWT token
 *
 * @internal
 *
 * @throws \Framework\Exception\InternalError
 */
        private function mtoken() : void
        {
            // This has to be a loop as we have no guarantees of the case of the keys in the returned array.
            $auth = \array_filter(\getallheaders(), static fn($key) => FW::AUTHTOKEN === \strtoupper($key), \ARRAY_FILTER_USE_KEY);
            if (!empty($auth))
            { // we have mobile authentication in use
                try
                {
                    /** @psalm-suppress UndefinedClass - the JWT code is not included in the psalm tests at the moment */
                    $tok = \Framework\Utility\JWT\JWT::decode(\array_shift($auth), FW::AUTHKEY);
                }
                catch (\Throwable $e)
                { // token error of some kind so return no access.
                    $this->web()->noaccess($e->getMessage());
                    /* NOT REACHED */
                }
                if (is_object($this->luser))
                {
                    if ($this->luser->getID() != $tok->sub)
                    {
                        throw new \Framework\Exception\InternalError('User conflict');
                    }
                }
                else
                {
                    $this->luser = $this->load(FW::USER, $tok->sub);
                }
                $this->tokenAuth = TRUE;
            }
        }
/**
 * Initialise the context and return self
 */
        public function setup() : ContextBase
        {
            \ini_set('session.use_only_cookies', '1'); // make sure PHP is set to make sessions use cookies only
            \ini_set('session.use_trans_sid', '0');   // this helps a bit towards making session hijacking more difficult
            \ini_set('session.cookie_httponly', '1');     // You can get rid of these calls if you know your php.ini is set up correctly
            if (isset($_COOKIE[Config::SESSIONNAME]))
            { // see if there is a userID variable in the session....
                /** @psalm-suppress UnusedFunctionCall */
                \session_start(['name' => Config::SESSIONNAME]);
                if (isset($_SESSION['userID']))
                { // there is a user id in the session so load the relevant user bean
                    $this->luser =  $this->load(FW::USER, $_SESSION['userID']);
                }
                else
                { // something not right so kill session and the session cookie
                    \session_destroy();
                    $params = \session_get_cookie_params();
                    \setcookie(\session_name(), '', \time() - 42000,
                        $params['path'], $params['domain'], $params['secure'], $params['httponly']
                    );
                }
            }
            $this->mtoken();

            $req = \array_filter(\explode('/', $this->web()->request()), static fn($val) => $val !== ''); // array_filter removes empty elements - trailing / or multiple /
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
                $bsplit = \array_filter(\explode('/', $this->local()->base()), static fn($val) => $val !== '');
                $req = \array_slice($req, \count($bsplit));
            }
            if (!empty($req))
            { // there was something after the domain name so split it into action and rest...
                $this->reqaction = \strtolower(\array_shift($req));
                $this->reqrest = empty($req) ? [''] : $req;  // there may only have been an action
            }
            return $this;
        }
    }
?>