<?php
/**
 * Contains definition of Local class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2019 Newcastle University
 */
    namespace Framework;

    use \Config\Config as Config;
    use \Config\Framework as FW;
    use \Framework\Web\Web as Web;
    use \Framework\Web\StatusCodes as StatusCodes;
/**
 * This is a class that maintains values about the local environment and does error handling
 *
 * Template rendering is done in here also so TWIG is initialised in this class. This allows TWIG
 * to be used for things like generating nice offline pages.
 *
 */
    class Local
    {
        use \Framework\Utility\Singleton;

        const ERROR     = 0;        # 'fwerrmessage';
        const WARNING   = 1;        # 'fwwarnmessage';
        const MESSAGE   = 2;        # 'fwmessage';
/**
 * @var array Contains string names for the message constants - used for Twig variables
 */
        private  static $msgnames  = ['fwerrmessage', 'fwwarnmessage', 'fwmessage'];
/**
 * @var	string		The absolute path to the site directory
 */
        private $basepath = '';
/**
 * @var	string		The name of the site directory
 */
        private $basedname	= '';

/**
 * @var	bool		If TRUE then ignore trapped errors
 */
        private $errignore	= FALSE;	# needed for checking preg expressions....
/**
 * @var	bool		Set to TRUE if an error was trapped and ignored
 */
        private $wasignored	= FALSE;
/**
 * @var array		A list of errors that have been emailed to the user. Only send a message once.
 */
        private $senterrors	= [];
/**
 * @var	bool		If TRUE then we are doing debugging
 */
        private $debug		= FALSE;
/**
 * @var bool         If TRUE then we are handling an error
 */
        private $error          = FALSE;
/**
 * @var	bool		If TRUE then we are in developer mode
 */
        private $devel		= FALSE;
/**
 * @var	bool		If TRUE then we are in ajax code and so error reporting is different
 */
        private $ajax		= FALSE;
/**
 * @var	array		An array of email addresses for system administrators
 */
        private $sysadmin	= [Config::SYSADMIN];
/**
 * @var	?object		the Twig renderer
 */
        private $twig		= NULL;
/**
 * @var	array		Key/value array of data to pass into template renderer
 */
        private $tvals		= [];
/**
 * @var array           Stash away messages so that messages.twig works
 */
        private $messages       = [[], [], []];
/**
 * @var string          Backtrace info - only used with errors
 */
        private $back       = '';
/**
 * @var array           Config values from database
 */
        private $fwconfig       = [];
/**
 * See if there are any messages and add them into the Twig values
 * and then clear the messages array.
 *
 * @return void
 */
        private function addmessages() : void
        {
            foreach ($this->messages as $ix => $vals)
            {
                $this->addval(self::$msgnames[$ix], $vals);
            }
            $this->clearmessages();
        }
/**
 * Rewrite error string
 *
 * @return string
 */
        private function eRewrite() : string
        {
            return '<pre>'.str_replace(',[', ',<br/>&nbsp;&nbsp;&nbsp;&nbsp;[', str_replace(PHP_EOL, '<br/>'.PHP_EOL, htmlentities($this->back))).'</pre>';
        }
/**
 * Tell sysadmin there was an error
 *
 * @param string	 $msg	An error messager
 * @param int|string $type	An error type
 * @param string 	 $file	file in which error happened
 * @param int    	 $line	Line at which it happened
 *
 * @return string
 */
        private function telladmin(string $msg, $type, string $file, int $line) : string
        {
            $this->error = TRUE; // flag that we are handling an error
            $ekey = $file.' / '.$line.' / '.$type.' / '.$msg;
            if (!isset($this->senterrors[$ekey]))
            {
                $this->senterrors[$ekey] = TRUE;
                if (isset($_GET['fwtrace']))
                {
                    $this->debug = TRUE;
                    ob_start();
                    debug_print_backtrace($_GET['fwtrace'], $_GET['fwdepth'] ?? 0);
                    $this->back = ob_get_clean(); # will get used later in make500
                }
                if (Config::USEPHPM || ini_get('sendmail_path') !== '')
                {
                    try
                    {
                        $mail = new \Framework\Utility\FMailer;
                        $mail->setFrom(Config::SITENOREPLY);
                        $mail->addReplyTo(Config::SITENOREPLY);
                        foreach ($this->sysadmin as $em)
                        {
                            $mail->addAddress($em);
                        }
                        $mail->Subject = Config::SITENAME.' '.date('c').' System Error - '.$msg.' '.$ekey;
                        $mail->msgHTML($this->eRewrite());
                        $mail->AltBody= 'Type : '.$type.PHP_EOL.$file.' Line '.$line.PHP_EOL.$this->back;
                        $mail->send();
                    }
                    catch (\Exception $e)
                    {
                        $ekey .= $this->eRewrite();
                    }
                }

            }
            return $ekey;
        }
/**
 * Generate a 500 and possibly an error page
 *
 * @param $ekey    string    Error information string
 *
 * @return void
 */
        private function make500(string $ekey) : void
        {
            if (!headers_sent())
            { # haven't generated any output yet.
                if ($this->devel || !$this->ajax)
                { # not in an ajax page so try and send a pretty error
                    $str = '<p>'.$ekey.'</p>'.($this->debug && $this->back !== '' ? $this->eRewrite() : 'There has been an internal error');
                    if (!$this->ajax && is_object($this->twig))
                    { # we have twig so render a nice page
                        Web::getinstance()->sendstring($this->getrender('@error/500.twig', ['errdata' => $str]), Web::HTMLMIME, StatusCodes::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    else
                    { # no twig or ajax so just dump
                        Web::getinstance()->internal($str);
                    }
                }
                else
                {
                    header(StatusCodes::httpHeaderFor(StatusCodes::HTTP_INTERNAL_SERVER_ERROR));
                }
            }
        }
/**
 * Shutdown function - this is used to catch certain errors that are not otherwise trapped and
 * generate a clean screen as well as an error report to the developers.
 *
 * It also closes the RedBean connection
 */
        public function shutdown() : void
        {
            if ($error = error_get_last())
            { # are we terminating with an error?
                if (isset($error['type']) && ($error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR))
                { # tell the developers about this
                    $ekey = $this->telladmin(
                        $error['message'],
                        $error['type'],
                        $error['file'],
                        $error['line']
                    );
                    $this->make500($ekey);
                }
                else
                {
                    echo '<h2>There has been a system error</h2>';
                }
            }
            \R::close(); # close RedBean connection
        }
/**
 * Deal with untrapped exceptions - see PHP documentation
 *
 * @param \Exception	$e
 */
        public function exceptionHandler($e) : void
        {
            if ($this->error)
            { // try and ignore errors within errors
                return;
            }
            $ekey = $this->telladmin(
                get_class($e).': '.$e->getMessage(),
                0,
                $e->getFile(),
                $e->getLine()
            );
            $this->make500($ekey);
            exit;
            /* NOT REACHED */
        }
/**
 * Called when a PHP error is detected - see PHP documentation for details
 *
 * Note that we can chose to ignore errors. At the moment his is a fairly rough mechanism.
 * It could be made more subtle by allowing the user to specifiy specific errors to ignore.
 * However, exception handling is a much much better way of dealing with this kind of thing
 * whenever possible.
 *
 * @param int           $errno
 * @param string	$errstr
 * @param string	$errfile
 * @param int   	$errline
 *
 * @return bool
 */
        public function errorHandler(int $errno, string $errstr, string $errfile, int $errline) : bool
        {
            if ($this->errignore)
            { # wanted to ignore this so just return
                $this->wasignored = TRUE; # remember we did ignore though
                return TRUE;
            }
            if ($this->error)
            { // already handling an error so just carry on
                return TRUE;
            }
            $ekey = $this->telladmin(
                'Error '.$errno.' '.$errstr,
                $errno,
                $errfile,
                $errline
            );
            if ($this->debug || in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]))
            { # this is an internal error or we are debugging, so we need to stop
                $this->make500($ekey);
                exit;
            }
/*
 * If we get here it's a warning or a notice, so we arent stopping
 *
 * Change this to an exit if you don't want to continue on any errors
 */
            return TRUE;
        }
/**
 * Handle an expectation failure
 *
 * @param string    $file    File name
 * @param int       $line      Line number in file
 * @param string    $message    Message
 */
            public function assertFail($file, $line, $message) : void
            {
                $ekey = $this->telladmin(
                    'Assertion Failure: '.$message,
                    0,
                    $file,
                    $line
                );
                $this->make500($ekey);
            }
/**
 * Allow system to ignore errors
 *
 * This always clears the wasignored flag
 *
 * @param bool       	$ignore	If TRUE then ignore the error otherwise stop ignoring
 *
 * @return bool	The last value of the wasignored flag
 */
        public function eignore(bool $ignore)
        {
            $this->errignore = $ignore;
            $wi = $this->wasignored;
            $this->wasignored = FALSE;
            return $wi;
        }
/**
 * Join the arguments with DIRECTORY_SEPARATOR to make a path name
 *
 * @return string
 */
        public function makepath()
        {
            return implode(DIRECTORY_SEPARATOR, func_get_args());
        }
/**
 * Join the arguments with DIRECTORY_SEPARATOR to make a path name and prepend the path to the base directory
 *
 * @return string
 */
        public function makebasepath()
        {
            return $this->basedir().DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, func_get_args());
        }
/**
 * Return a path to the assets directory suitable for use in links
 *
 * @return string
 */
        public function assets()
        {
            return $this->base().'/assets'; # for HTML so the / is OK to use here
        }
/**
 * Return a filesystem path to the assets directory
 *
 * @return string
 */
        public function assetsdir()
        {
            return $this->basedir().DIRECTORY_SEPARATOR.'assets';
        }
/**
 * Return a named config bean
 *
 * @param string       $name  The name of the item
 *
 * @return ?object
 */
        public function config(string $name) : ?object
        {
            return $this->fwconfig[$name] ?? NULL;
        }
/**
 * Return a named config bean value
 *
 * @param string       $name  The name of the item
 *
 * @return string
 */
        public function configval(string $name) : string
        {
            return $this->fwconfig[$name]->value ?? '';
        }
/**
 * Return all the config values
 *
 * @return array
 */
        public function allconfig() : array
        {
            return $this->fwconfig;
        }
/**
 * Initialise twig template engine
 *
 * @param bool       	$cache	if TRUE then enable the TWIG cache
 *
 * @return void
 */
        public function setuptwig(bool $cache = FALSE) : void
        {
            $twigdir = $this->makebasepath('twigs');
            $loader = new \Twig\Loader\FilesystemLoader($twigdir);
            foreach (['admin', 'devel', 'edit', 'error', 'users', 'util', 'view'] as $tns)
            {
                    $loader->addPath($twigdir.'/framework/'.$tns, $tns);
            }
            foreach (['content', 'info', 'surround'] as $tns)
            {
                $loader->addPath($twigdir.'/'.$tns, $tns);
            }
            foreach (['util'] as $tns)
            {
                    $loader->addPath($twigdir.'/vue/framework/'.$tns, 'vue'.$tns);
            }
            foreach (['content'] as $tns)
            {
                $loader->addPath($twigdir.'/vue/'.$tns, 'vue'.$tns);
            }
            $this->twig = new \Twig\Environment(
                $loader,
                ['cache' => $cache ? $this->makebasepath('twigcache') : FALSE]
            );
/*
 * A set of basic values that get passed into the TWIG renderer
 *
 * Add new key/value pairs to this array to pass values into the twigs
 */
            $this->twig->addGlobal('base', $this->base());
            $this->twig->addGlobal('assets', $this->assets());
            foreach (self::$msgnames as $mn)
            {
                $this->twig->addGlobal($mn, []);
            }
            $this->tvals = [];
        }
/**
 * Calls a user defined function with the twig object as a parameter.
 * The user can then add extensions, filters etc.
 *
 * @param callable     $fn      A user defined function
 *
 * @return void
 */
        public function extendtwig(callable $fn) : void
        {
            $fn($this->twig);
        }
/**
 * Return TRUE if Twig is enabled
 *
 * @return bool
 */
        public function hastwig()
        {
            return is_object($this->twig);
        }
/**
 * Render a twig and return the string - do nothing if the template is the empty string
 *
 * @param string	$tpl	The template
 * @param array	        $vals	Values to set for the twig
 *
 * @return string
 */
        public function getrender(string $tpl, array $vals = [])
        {
            if ($tpl === '')
            { // no template so no output
                return '';
            }
            $this->addmessages(); # add in any messages
            $this->addval($vals); # set up any values that have been passed
            /** @psalm-suppress PossiblyNullReference */
            return $this->twig->render($tpl, $this->tvals);
        }
/**
 * Render a twig - do nothing if the template is the empty string
 *
 * @param string	$tpl	The template
 * @param array	        $vals	Values to set for the twig
 */
        public function render(string $tpl, array $vals = []) : void
        {
            if ($tpl !== '')
            {
                Web::getinstance()->sendstring($this->getrender($tpl, $vals), 'text/html; charset="utf-8"');
            }
        }
/**
 * Add a value into the values stored for rendering the template
 *
 * @param string|array	$vname		The name to be used inside the twig or an array of value pairs
 * @param mixed		    $value		The value to be stored or "" if an array in param 1
 * @param bool          $tglobal    If TRUE add this as a twig global variable
 *
 * @return void
 */
        public function addval($vname, $value = '', $tglobal = FALSE) : void
        {
            if (is_array($vname))
            {
                foreach ($vname as $key => $aval)
                {
                    if ($tglobal)
                    {
                        $this->twig->addGlobal($key, $aval);
                    }
                    else
                    {
                        $this->tvals[$key] = $aval;
                    }
                }
            }
            elseif ($tglobal)
            {
                $this->twig->addGlobal($vname, $value);
            }
            else
            {
                $this->tvals[$vname] = $value;
            }
        }
/**
 * Add a message into the messages stored for rendering the template
 *
 * The currently supported values for kind are :
 *
 *      \Framework\Local\ERROR
 *      \Framework\Local\WARNING
 *      \Framework\Local\MESSAGE
 *
 * To have your Twig deal with these you need
 *
 * {% include '@util/message.twig %}
 *
 * somewhere in the relevant twig (usually at the top of the main body)
 *
 * @param int   	$kind		The kind of message
 * @param mixed		$value		The value to be stored or an array of values
 *
 * @return void
 */
        public function message(int $kind, $value) : void
        {
            if (is_array($value))
            {
                $this->messages[$kind] = array_merge($this->messages[$kind], $value);
            }
            else
            {
                $this->messages[$kind][] = $value;
            }
        }
/**
 * Clear out messages
 *
 * @param string    $kind   Either empty for all messages or a specific kind
 *
 * @return void
 */
        public function clearmessages(string $kind = '') : void
        {
            if ($kind === '')
            {
                $this->messages = [[], [], []];
            }
            else
            {
                $this->messages[$kind] = [];
            }
        }
/**
 * Return the name of the directory for this site
 *
 * @return string
 */
        public function base()
        {
            return $this->basedname;
        }
/**
 * Return the path to the directory for this site
 *
 * @return string
 */
        public function basedir()
        {
            return $this->basepath;
        }
/**
 * Remove the base component from a URL
 *
 * Note that this will fail if the base name contains a '#' character!
 * The installer tests for this and issues an error when run.
 *
 * @param string        $url
 *
 * @return string
 */
        public function debase(string $url)
        {
            if ($this->base() !== '')
            {
                $url = preg_replace('#^'.$this->base().'#', '', $url);
            }
            return $url;
        }
/**
 * Put the system into debugging mode
 *
 * @psalm-suppress PossiblyNullReference
 *
 * @return void
 */
        public function enabledebug() : void
        {
            $this->debug = TRUE;
            if ($this->hastwig())
            { // now we know we have twig - hence suppress above
                $this->twig->addExtension(new \Twig\Extension\DebugExtension());
                $this->twig->enableDebug();
            }
        }
/**
 * Set up local information. Returns self
 *
 * The $loadrb parameter simplifies some of the unit testing for this class
 *
 * @param string	$basedir	The full path to the site directory
 * @param bool       	$ajax		If TRUE then this is an AJAX call
 * @param bool       	$devel		If TRUE then we are developing the system
 * @param bool       	$loadtwig	if TRUE then load in Twig.
 * @param bool       	$loadrb		if TRUE then load in RedBean
 *
 * @return \Framework\Local
 */
        public function setup(string $basedir, bool $ajax, bool $devel, bool $loadtwig, bool $loadrb = TRUE) : \Framework\Local
        {
            $this->devel = $devel;
            $this->basepath = $basedir;
            $this->basedname = Config::BASEDNAME;
/*
 * If you want to be able to move the system arbitrarily you will need
 * the functionality of the next block of code.
 *
 * N.B. This will get confused if there are symbolic links in use!!!!!
 */
        //    $bd = $basedir;
        //    $bdr = [''];
        //    while ($bd != $_SERVER['DOCUMENT_ROOT'])
        //    { // keep stripping of the last component until we get to the document root
        //	$pp = pathinfo($bd);
        //	$bd = $pp['dirname'];
        //	$bdr[] = $pp['basename'];
        //    }
        //    $this->basedname = implode('/', $bdr);
            $this->ajax = $ajax;
 /*
 * Set up all the system error handlers
 */
            /** @psalm-suppress ArgumentTypeCoercion */
            set_exception_handler([$this, 'exceptionHandler']);
            set_error_handler([$this, 'errorHandler']);
            /** @psalm-suppress InvalidArgument - psalm doesnt have the right spec for this function */
            /** @psalm-suppress ArgumentTypeCoercion */
            register_shutdown_function([$this, 'shutdown']);
            if ($devel)
            { // set up expectation handling if in developer mode
                assert_options(ASSERT_ACTIVE, $devel);
                assert_options(ASSERT_WARNING, 0);
                assert_options(ASSERT_QUIET_EVAL, 1);
                assert_options(ASSERT_CALLBACK, [$this, 'assertFail']);
            }

            if ($loadtwig)
            { # we want twig - there are some autoloader issues out there that adding twig seems to fix....
                $this->setuptwig(FALSE);
            }

            $offl = $this->makebasepath('offline');
            if (file_exists($offl))
            { # go offline before we try to do anything else...
                $this->render('@admin/offline.twig', ['msg' => file_get_contents($offl)]);
                exit;
            }
/*
 * Initialise database access
 */
            class_alias('\RedBeanPHP\R','\R');
            /** @psalm-suppress RedundantCondition - the mock config file has this set to a value so this. Ignore this error */
            if (Config::DBHOST !== '' && $loadrb)
            { # looks like there is a database configured
                \R::setup(Config::DBTYPE.':host='.Config::DBHOST.';dbname='.Config::DB, Config::DBUSER, Config::DBPW); # mysql initialiser
                \R::freeze(!$devel); # freeze DB for production systems
                $this->fwconfig = [];
                foreach (\R::findAll(FW::CONFIG) as $cnf)
                {
                    $cnf->value = preg_replace('/%BASE%/', $this->basedname, $cnf->value);
                    $this->fwconfig[$cnf->name] = $cnf;
                }
                if ($loadtwig)
                {
                    /** @psalm-suppress PossiblyNullReference */
                    $this->twig->addGlobal('fwurls', $this->fwconfig); # Package URL values for use in Twigs
                }
            }
            return $this;
        }
    }
?>