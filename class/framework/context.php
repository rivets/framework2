<?php
/**
 * Contains the definition of the Context class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2019 Newcastle University
 */
    namespace Framework;

    use Config\Config as Config;
    use Config\Framework as FW;
    use Framework\Web\Web as Web;
/**
 * A class that stores various useful pieces of data for access throughout the rest of the system.
 */
    class Context
    {
        use \Framework\Utility\Singleton;
/**
 * The name of the authentication token field.
 */
        private const TOKEN 	    = 'X-APPNAME-TOKEN';
/**
 * The key used to encode the token validation
 */
        private const KEY	        = 'Some string of text.....';

/** @var ?object		NULL or an object decribing the current logged in User (if we have logins at all) */
        protected $luser	= NULL;
/** @var integer	Counter used for generating unique ids */
        protected $idgen        = 0;
/** @var string		The first component of the current URL */
        protected $reqaction	= 'home';
/** @var string[]		The rest of the current URL exploded at / */
        protected $reqrest	= [];
/** @var boolean    True if authenticated by token */
        protected $tokauth	= FALSE;
/** @var \RedBeanPHP\OODBBean[]		A cache for rolename beans */
        protected $roles        = [];
/** @var \RedBeanPHP\OODBBean[]		A cache for rolecontext beans */
        protected $contexts     = [];
/** @var \RedBeanPHP\OODBBean[]		A cache for JS ons beans */
        protected $ons          = [];
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
 * @return string[]
 */
        public function rest()
        {
            return $this->reqrest;
        }
/**
 * Check URL string for n parameter values and pull them out
 *
 * The value in $rest[0] is assumed to be an opcode so we always start at $rest[1]
 *
 * @param integer   $count  The number to check for
 *
 * @return string[] The parameter values in an array indexed from 0
 */
        public function restcheck(int $count) : array
        {
            if (count($this->reqrest) <= $count)
            {
                throw new \Framework\Exception\ParameterCount();
            }
            $res = array_slice($this->reqrest, 1, $count);
            $res[] = array_slice($this->reqrest, $count+1); // return anything left - there might be optional parameters.
            return $res;
        }
/**
 ***************************************
 * User related functions
 ***************************************
 */
/**
 * Return the current logged in user if any
 *
 * @return ?object
 */
        public function user() : ?object
        {
            return $this->luser;
        }
/**
 * Return TRUE if the user in the parameter is the same as the current user
 *
 * @param object    $user
 *
 * @return bool
 */
        public function sameuser($user)
        {
             /** @psalm-suppress PossiblyNullReference */
            return $this->hasuser() && $this->user()->equals($user);
        }
/**
 * Do we have a logged in user?
 *
 * @return bool
 */
        public function hasuser()
        {
            return is_object($this->luser);
        }
/**
 * Do we have a logged in admin user?
 *
 * @return boolean
 */
        public function hasadmin()
        {
            /** @psalm-suppress PossiblyNullReference */
            return $this->hasuser() && $this->user()->isadmin();
        }
/**
 * Do we have a logged in developer user?
 *
 * @return boolean
 */
        public function hasdeveloper()
        {
            /** @psalm-suppress PossiblyNullReference */
            return $this->hasuser() && $this->user()->isdeveloper();
        }
/**
 * Find out if this was validated using a token, if so, it is coming from a device not a browser
 *
 * @return boolean
 */
	public function hastoken()
	{
	    return $this->tokauth;
	}
/**
 * Check for logged in and 403 if not
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        public function mustbeuser() : void
        {
            if (!$this->hasuser())
            {
                throw new \Framework\Exception\Forbidden('Must be logged in');
            }
        }
/**
 * Check for an admin and 403 if not
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        public function mustbeadmin() : void
        {
            if (!$this->hasadmin())
            {
                throw new \Framework\Exception\Forbidden('Must be an admin');
            }
        }
/**
 * Check for an developer and 403 if not
 *
 * @throws \Framework\Exception\Forbidden
 *
 * @return void
 */
        public function mustbedeveloper() : void
        {
            if (!$this->hasdeveloper())
            {
                throw new \Framework\Exception\Forbidden('Must be a developer');;
            }
        }
/*
 ***************************************
 * Miscellaneous utility functions
 ***************************************
 */
/**
 * Set up pagination data
 */
        public function setpages() : void
        {
            $form = $this->formdata();
            $this->local()->addval([
                'page'      => $form->filterget('page', 1, FILTER_VALIDATE_INT), // just in case there is any pagination going on
                'pagesize'  => $form->filterget('pagesize', 10, FILTER_VALIDATE_INT)
            ]);
        }
/**
 * Generates a new, unique, sequential id value
 *
 * @param string	$str The prefix for the id
 *
 * @return string
 */
        public function newid(string $str = 'id')
        {
            $this->idgen += 1;
            return $str.$this->idgen;
        }
/**
 * Save values into the on cache
 *
 * @param string   $id
 * @param string   $on
 * @param string   $fn
 */
        public function saveon($id, $on, $fn)
        {
            $this->ons[$id][$on] = $fn;
        }
/**
 * Get the KS for onloading the ons
 *
 * @return string
 */
        public function getons()
        {
            $res = '';
            foreach ($this->ons as $id => $conds)
            {
                $xres = '';
                foreach ($conds as $on => $fn)
                {
                    $xres .= ".on('".$on."', ".$fn.")";
                }
                $res = $res. "$('#".$id."')".$xres.";\n";
            }
            return $res;
        }
/**
 * Find a rolename bean
 *
 * @param string    $name   A Role name
 *
 * @return ?\RedBeanPHP\OODBBean
 */
        public function rolename(string $name) : ?\RedBeanPHP\OODBBean
        {
            if (!isset($this->roles[$name]))
            {
                $this->roles[$name] = \R::findOne(FW::ROLENAME, 'name=?', [$name]);
            }
            return $this->roles[$name];
        }
/**
 * Find a rolecontext bean
 *
 * @param string    $name   A Role Context
 *
 * @return ?\RedBeanPHP\OODBBean
 */
        public function rolecontext(string $name) : ?\RedBeanPHP\OODBBean
        {
            if (!isset($this->roles[$name]))
            {
                $this->contexts[$name] = \R::findOne(FW::ROLECONTEXT, 'name=?', [$name]);
            }
            return $this->contexts[$name];
        }
/**
 * Check to see if there is a session and return a specific value from it if it exists
 *
 * @param string	$var	The variable name
 * @param bool       	$fail	If TRUE then exit with an error return if the value  does not exist
 *
 * @return mixed
 */
        public function sessioncheck(string $var)
        {
            if (isset($_COOKIE[Config::SESSIONNAME]))
            {
                session_start(['name' => Config::SESSIONNAME]);
                if (isset($_SESSION[$var]))
                {
                    return $_SESSION[$var];
                }
            }
            return NULL;
        }
/**
 * Generate a Location header for within this site
 *
 * @param string		$where		The page to divert to
 * @param bool       		$temporary	TRUE if this is a temporary redirect
 * @param string		$msg		A message to send
 * @param bool       		$nochange	If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 * @param bool       		$use303  	If TRUE then 303 will be used instead of 307
 *
 * @psalm-return never-return
 * @return void
 */
        public function divert(string $where, bool $temporary = TRUE, string $msg = '', bool $nochange = FALSE, bool $use303 = FALSE)
        {
            $this->web()->relocate($this->local()->base().$where, $temporary, $msg, $nochange, $use303);
            /* NOT REACHED */
        }
/**
 * Load a bean
 *
 * @param string	$bean	    A bean type name
 * @param int    	$id	        A bean id
 * @param boolean   $forupdate  If TRUE then use loadforupdate
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
 */
        public function local() : \Framework\Local
        {
            return \Framework\Local::getinstance();
        }
/**
 * Return the Formdata object
 *
 * @return \Support\FormData
 */
        public function formdata() : \Support\FormData
        {
            return \Support\FormData::getinstance();
        }
/**
 * Return the Web object
 *
 * @return \Framework\Web\Web
 */
        public function web() : \Framework\Web\Web
        {
            return \Framework\Web\Web::getinstance();
        }
/**
 * Return an iso formatted time for NOW  in UTC
 *
 * @return string
 */
        public function utcnow() : string
        { /** @psalm-suppress InvalidOperand */
            return \R::isodatetime(time() - date('Z'));
        }
/**
 * Return an iso formatted time in UTC
 *
 * @param string       $datetime
 *
 * @return string
 */

        public function utcdate(string $datetime) : string
        { /** @psalm-suppress InvalidOperand */
            return \R::isodatetime(strtotime($datetime) - date('Z'));
        }
/*
 ***************************************
 * Setup the Context - the constructor is hidden in Singleton
 ***************************************
 */
/**
 * Initialise the context and return self
 *
 * @return \Framework\Context
 */
        public function setup() : \Framework\Context
        {
            $this->luser = $this->sessioncheck('user'); # see if there is a user variable in the session....
            foreach (getallheaders() as $k => $v)
            {
                if (self::TOKEN === strtoupper($k))
                { // we have mobile authentication in use
                    try
                    {
                        /** @psalm-suppress UndefinedClass - the JWT code is not included in the psalm tests at the moment */
                        $tok = \Framework\Utility\JWT\JWT::decode($v, self::KEY);
                        $this->luser = $this->load('user', $tok->sub);
                    }
                    catch (\Exception $e)
                    { // token error of some kind so return no access.
                        $this->web()->noaccess();
                        /* NOT REACHED */
                    }
                    $this->tokauth = TRUE;
                    break;
                }
            }
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
            { # there is a query string so get rid it of it from the URI
                list($uri) = explode('?', $uri);
            }
            $req = array_filter(explode('/', $uri)); # array_filter removes empty elements - trailing / or multiple /
/*
 * If you know that the base directory is empty then you can delete the next test block.
 *
 * You can also optimise out the loop if you know how deep you are nested in sub-directories
 *
 * The code here is to make it easier to move your code around within the hierarchy. If you don't need
 * this then optimise the hell out of it.
 */
            if ($this->local()->base() !== '')
            { # we are in at least one sub-directory
                $bsplit = array_filter(explode('/', $this->local()->base()));
                foreach (range(1, count($bsplit)) as $c)
                {
                    array_shift($req); # pop off the directory name...
                }
            }
            if (!empty($req))
            { # there was something after the domain name so split it into action and rest...
                $this->reqaction = strtolower(array_shift($req));
                $this->reqrest = empty($req) ? [''] : array_values($req);
            }

            return $this;
        }
    }
?>