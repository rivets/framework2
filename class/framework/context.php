<?php
    namespace Framework;

    use Config\Config as Config;
    use Framework\Web\Web as Web;

/**
 * Contains the definition of the Context class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2017 Newcastle University
 */
/**
 * A class that stores various useful pieces of data for access throughout the rest of the system.
 */
    class Context
    {
        use \Utility\Singleton;
/**
 * The name of the authentication token field.
 */
        const TOKEN 	        = 'X-APPNAME-TOKEN';
/**
 * The key used to encode the token validation
 */
        const KEY	        = 'Some string of text.....';
/**
 * Value indicating to generate a 400 output from load function when id does not exist
 */
        const R400              = 0;
/**
 * Value indicating to generate a NULL return from load function when id does not exist
 */
        const RNULL             = 1;
/**
 * Value indicating to throw an error from load function when id does not exist
 */
        const RTHROW            = 2;
/**
 * @var object		NULL or an object decribing the current logged in User (if we have logins at all)
 */
        protected $luser	= NULL;
/**
 * @var integer		Counter used for generating unique ids
 */
        protected $idgen        = 0;
/**
 * @var string		The first component of the current URL
 */
        protected $reqaction	= 'home';
/**
 * @var array		The rest of the current URL exploded at /
 */
        protected $reqrest	= [];
/**
 * @var boolean		True if authenticated by token
 */
	protected $tokauth	= FALSE;
 /**
 * @var array		A cache for rolename beans
 */
        protected $roles        = [];
 /**
 * @var array		A cache for rolecontext beans
 */
        protected $contexts     = [];
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
 * @return array
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
 * @return object
 */
        public function user()
        {
            return $this->luser;
        }
/**
 * Return TRUE if the user in the parameter is the same as the current user
 *
 * @param object    $user
 *
 * @return boolean
 */
        public function sameuser($user)
        {
            return $this->hasuser() && $this->user()->equals($user);
        }
/**
 * Do we have a logged in user?
 *
 * @return boolean
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
            return $this->hasuser() && $this->user()->isadmin();
        }
/**
 * Do we have a logged in developer user?
 *
 * @return boolean
 */
        public function hasdeveloper()
        {
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
 */
        public function mustbeuser()
        {
            if (!$this->hasuser())
            {
                $this->web()->noaccess();
            }
        }
/**
 * Check for an admin and 403 if not
 */
        public function mustbeadmin()
        {
            if (!$this->hasadmin())
            {
                $this->web()->noaccess();
            }
        }
/**
 * Check for an developer and 403 if not
 */
        public function mustbedeveloper()
        {
            if (!$this->hasdeveloper())
            {
                $this->web()->noaccess();
            }
        }
/*
 ***************************************
 * Miscellaneous utility functions
 ***************************************
 */
/**
 * Generates a new, unique, sequential id value
 *
 * @param string	$id The prefix for the id
 *
 * @return string
 */
        public function newid($str = 'id')
        {
            $this->idgen += 1;
            return $str.$this->idgen;
        }
/**
 * Find a rolename bean
 *
 * @param string    $name   A Role name
 *
 * @return object
 */
        public function rolename($name)
        {
            if (!isset($this->roles[$name]))
            {
                $this->roles[$name] = \R::findOne('rolename', 'name=?', [$name]);
            }
            return $this->roles[$name];
        }
/**
 * Find a rolecontext bean
 *
 * @param string    $name   A Role Context
 *
 * @return object
 */
        public function rolecontext($name)
        {
            if (!isset($this->roles[$name]))
            {
                $this->contexts[$name] = \R::findOne('rolecontext', 'name=?', [$name]);
            }
            return $this->contexts[$name];
        }
/**
 * Check to see if there is a session and return a specific value from it if it exists
 *
 * @param string	$var	The variable name
 * @param boolean	$fail	If TRUE then exit with an error returnif the value  does not exist
 *
 * @return mixed
 */
        public function sessioncheck($var, $fail = TRUE)
        {
            if (isset($_COOKIE[ini_get('session.name')]))
            {
                session_start();
                if (isset($_SESSION[$var]))
                {
                    return $_SESSION[$var];
                }
            }
            if ($fail)
            {
                $this->web()->noaccess();
            }
            return NULL;
        }
/**
 * Generate a Location header for within this site
 *
 * @param string		$where		The page to divert to
 * @param boolean		$temporary	TRUE if this is a temporary redirect
 * @param string		$msg		A message to send
 * @param boolean		$nochange	If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 *
 * @return void NEVER returns
 */
        public function divert($where, $temporary = TRUE, $msg = '', $nochange = FALSE)
        {
            $this->web()->relocate($this->local()->base().$where, $temporary, $msg, $nochange);
        }

/**
 * Load a bean or fail with a 400 error
 *
 * @param string	$table	    A bean type name
 * @param integer	$id	    A bean id
 * @param integer       $onerror    A flag indicating what to do on error (see constants above)
 *
 * @throws  Exception when asked to by the "onerror" value
 *
 * @return object
 */
        public function load($bean, $id, $onerror = self::R400)
        {
            $foo = \R::load($bean, $id);
            if ($foo->getID() == 0)
            { # a bean with that id does not exist
                switch ($onerror)
                {
                case self::R400:
                    $this->web()->bad($bean.' '.$id);

                case self::RNULL:
                    return NULL;

                case self::RTHROW:
                    throw new Exception('No such bean');

                default:
                    throw new Exception('Weird error');
                }
            }
            return $foo;
        }
/**
 * Return the local object
 *
 * @return object
 */
        public function local()
        {
            return Local::getinstance();
        }
/**
 * Return the Formdata object
 *
 * @return object
 */
        public function formdata()
        {
            return Formdata::getinstance();
        }
/**
 * Return the Web object
 *
 * @return object
 */
        public function web()
        {
            return Web::getinstance();
        }
/**
 * Return an iso formatted time for NOW  in UTC
 *
 * @return string
 */
        public function utcnow()
        {
            return \R::isodatetime(time() - date('Z'));
        }
/**
 * Return an iso formatted time in UTC
 *
 * @param string       $datetime
 *
 * @return string
 */
        public function utcdate($datetime)
        {
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
 * @param boolean	$local	The singleton local object
 *
 * @return object
 */
        public function setup()
        {
            $this->luser = $this->sessioncheck('user', FALSE); # see if there is a user variable in the session....
            foreach (getallheaders() as $k => $v)
            {
                if (self::TOKEN === strtoupper($k))
                {
                    $tok = JWT::decode($v, self::KEY);
                    $this->luser = $this->load('user', $tok->sub);
                    $this->tokauth = TRUE;
                    break;
                }
            }
            if (isset($_SERVER['REDIRECT_URL']) && !preg_match('/index.php/', $_SERVER['REDIRECT_URL']))
            {
/**
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
