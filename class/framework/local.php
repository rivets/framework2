<?php
/**
 * Contains definition of Local class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2017 Newcastle University
 */
    namespace Framework;

    use Config\Config as Config;
    use Framework\Web\Web as Web;
/**
 * This is a class that maintains values about the local environment and does error handling
 *
 * Template rendering is done in here also so TWIG is initialised in this class. This allows TWIG
 * to be used for things like generating nice offline pages.
 *
 */
    class Local
    {
        use \Utility\Singleton;

        const ERROR     = 'errmessage';
        const WARNING   = 'warnmessage';
        const MESSAGE   = 'message';

/**
 * @var	string		The absolute path to the site directory
 */
        private $basepath;
/**
 * @var	string		The name of the site directory
 */
        private $basedname	= '';

/**
 * @var	boolean		If TRUE then ignore trapped errors
 */
        private $errignore	= FALSE;	# needed for checking preg expressions....
/**
 * @var	boolean		Set to TRUE if an error was trapped and ignored
 */
        private $wasignored	= FALSE;
/**
 * @var array		A list of errors that have been emailed to the user. Only send a message once.
 */
        private $senterrors	= [];
/**
 * @var	boolean		If TRUE then we are doing debugging
 */
        private $debug		= FALSE;
/**
 * @var	boolean		If TRUE then we are in developer mode
 */
        private $devel		= FALSE;
/**
 * @var	boolean		If TRUE then we are in ajax code and so error reporting is different
 */
        private $ajax		= FALSE;
/**
 * @var	array		An array of email addresses for system administrators
 */
        private $sysadmin	= [Config::SYSADMIN];
/**
 * @var	object		the Twig renderer
 */
        private $twig		= NULL;
/**
 * @var	array		Key/value array of data to pass into template renderer
 */
        private $tvals		= [];
/**
 * @var array           Stash away messages so that messages.twig works
 */
        private $messages       = [];
/**
 * @var string          Backtrace info - only used with errors
 */
        private $back;
/**
 * See if there are any messages and add them into the Twig values
 * and then clear the messages array.
 *
 * @return void
 */
        private function addmessages()
        {
            foreach ($this->messages as $name => $vals)
            {
                $this->addval($name, $vals);
            }
            $this->clearmessages();
        }
/**
 * Tell sysadmin there was an error
 *
 * @param string	$msg	An error messager
 * @param string	$type	An error type
 * @param string 	$file	file in which error happened
 * @param string	$line	Line at which it happened
 *
 * @return void
 */
        private function telladmin($msg, $type, $file, $line)
        {
            $ekey = $file.'/'.$line.'/'.$type.'/'.$msg;
            if (!isset($this->senterrors[$ekey]))
            {
                ob_start();
                debug_print_backtrace();
                $this->back = ob_get_clean(); # will get used later in make500
                if (Config::USEPHPM || ini_get('sendmail_path') !== '')
                {
                    $mail = new \Utility\FMailer;
                    $mail->setFrom(Config::SITENOREPLY);
                    $mail->addReplyTo(Config::SITENOREPLY);
                    foreach ($this->sysadmin as $em)
                    {
                        $mail->addAddress($em);
                    }
                    $mail->Subject = Config::SITENAME.' '.date('c').' System Error - '.$msg;
                    $mail->msgHTML('<pre>'.str_replace(',[', ',<br/>&nbsp;&nbsp;&nbsp;&nbsp;[', str_replace(PHP_EOL, '<br/>'.PHP_EOL, htmlentities($this->back))).'</pre>');
                    $mail->AltBody= 'Type : '.$type.PHP_EOL.$file.' Line '.$line.PHP_EOL.$this->back;
                    $mail->send();
                }
                $this->senterrors[$ekey] = TRUE;
            }
        }
/**
 * Generate a 500 and possibly an error page
 *
 * @return void
 */
        private function make500()
        {
            if (!headers_sent())
            { # haven't generated any output yet.
                if (!$this->ajax)
                { # not in an ajax page so try and send a pretty error
                    $str = $this->debug ? '<pre>'.str_replace(',[', ',<br/>&nbsp;&nbsp;&nbsp;&nbsp;[', str_replace(PHP_EOL, '<br/>'.PHP_EOL, htmlentities($this->back))).'</pre>' :
                        'There has been an internal error';
                    if (is_object($this->twig))
                    { # we have twig so render a nice page
                        $this->addval('message', $str);
                        Web::getinstance()->sendstring($this->getrender('error/500.twig'), Web::HTMLMIME);
                    }
                    else
                    { # no twig so just dump
                        Web::getinstance()->internal($str);
                    }
                }
                else
                {
                    header('HTTP/1.1 500 Internal Server Error');
                }
            }
        }
/**
 * Shutdown function - this is used to catch certain errors that are not otherwise trapped and
 * generate a clean screen as well as an error report to the developers.
 *
 * It also closes the RedBean connection
 */
        public function shutdown()
        {
            if ($error = error_get_last())
            { # are we terminating with an error?
                if (isset($error['type']) && ($error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR))
                { # tell the developers about this
                    $this->telladmin(
                	$error['message'],
                        $error['type'],
                        $error['file'],
                	$error['line']
                    );
                    $this->make500();
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
 * @param Exception	$e
 */
        public function exception_handler($e)
        {
            $this->telladmin(
                $e->getMessage().' ',
                get_class($e),
                $e->getFile(),
                $e->getLine()
            );
            $this->make500();
            exit;
        }
/**
 * Called when a PHP error is detected - see PHP documentation for details
 *
 * Note that we can chose to ignore errors. At the moment his is a fairly rough mechanism.
 * It could be made more subtle by allowing the user to specifiy specific errors to ignore.
 * However, exception handling is a much much better way of dealing with this kind of thing
 * whenever possible.
 *
 * @param integer	$errno
 * @param string	$errstr
 * @param string	$errfile
 * @param integer	$errline
 * @param string	$errcontext
 *
 * @return boolean
 */
        public function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
        {
            if ($this->errignore)
            { # wanted to ignore this so just return
                $this->wasignored = TRUE; # remember we did ignore though
                return TRUE;
            }
            $this->telladmin(
                $errno.' '.$errstr,
                'Error',
                $errfile,
                $errline
            );
            if ($this->debug || in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]))
            { # this is an internal error or we are debugging, so we need to stop
                $this->make500();
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
 * Allow system to ignore errors
 *
 * This always clears the wasignored flag
 *
 * @param boolean	$ignore	If TRUE then ignore the error otherwise stop ignoring
 *
 * @return boolean	The last value of the wasignored flag
 */
        public function eignore($ignore)
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
 * Initialise twig template engine
 *
 * @param boolean	$cache	if TRUE then enable the TWIG cache
 *
 * @return void
 */
        public function setuptwig($cache = FALSE)
        {
            $this->twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem($this->makebasepath('twigs')),
                ['cache' => $cache ? $this->makebasepath('twigcache') : FALSE]
            );
/*
 * A set of basic values that get passed into the TWIG renderer
 *
 * Add new key/value pairs to this array to pass values into the twigs
 */
            $this->twig->addGlobal('base', $this->base());
            $this->twig->addGlobal('assets', $this->assets());
            $this->tvals = [];
        }
/**
 * Calls a user defined function with the twig object as a parameter.
 * The user can then add extensions, filters etc.
 *
 * @param function     $fn      A user defined function
 *
 * @return void
 */
        public function extendtwig($fn)
        {
            $fn($this->twig);
        }
/**
 * Return TRUE if Twig is enabled
 *
 * @return boolean
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
        public function getrender($tpl, $vals = [])
        {
            if ($tpl === '')
            { // no template so no output
                return '';
            }
            $this->addmessages(); # add in any messages
            $this->addval($vals); # set up any values that have been passed
            return $this->twig->render($tpl, $this->tvals);
        }
/**
 * Render a twig - do nothing if the template is the empty string
 *
 * @param string	$tpl	The template
 * @param array	        $vals	Values to set for the twig
 */
        public function render($tpl, $vals = [])
        {
            if ($tpl !== '')
            {
                Web::getinstance()->sendstring($this->getrender($tpl, $vals), 'text/html; charset="utf-8"');
            }
        }
/**
 * Add a value into the values stored for rendering the template
 *
 * @param string	$vname		The name to be used inside the twig or an array of value pairs
 * @param mixed		$value		The value to be stored or "" if an array in param 1
 *
 * @return void
 */
        public function addval($vname, $value = "")
        {
            if (is_array($vname))
            {
                foreach ($vname as $key => $aval)
                {
                    $this->tvals[$key] = $aval;
                }
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
 *      'errmessage'
 *      'warnmessage'
 *      'message'
 *
 * To have your Twig deal with these you need
 *
 * {% include 'message.twig %}
 *
 * somewhere in the relevant twig (usually at the top of the main body)
 *
 * @param string	$kind		The kind of message
 * @param mixed		$value		The value to be stored
 *
 * @return void
 */
        public function message($kind, $value)
        {
            if (!isset($this->messages[$kind]))
            {
                $this->messages[$kind] = [];
            }
            $this->messages[$kind][] = $value;
        }
/**
 * Clear out messages
 *
 * @param string    $kind   Either empty for all messages or a specific kind
 *
 * @return void
 */
        public function clearmessages($kind = '')
        {
            if ($kind === '')
            {
                $this->messages = [];
            }
            elseif (isset($this->messages[$kind]))
            {
                unset($this->messages[$kind]);
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
        public function debase($url)
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
 */
        public function enabledebug()
        {
            $this->debug = TRUE;
            if ($this->hastwig())
            {
                $this->twig->addExtension(new \Twig_Extension_Debug());
                $this->twig->enableDebug();
            }
        }
/**
 * Set up local information. Returns self
 *
 * The $loadrb parameter simplifies some of the unit testing for this class
 *
 * @param string	$basedir	The full path to the site directory
 * @param boolean	$ajax		If TRUE then this is an AJAX call
 * @param boolean	$devel		If TRUE then we are developing the system
 * @param boolean	$loadtwig	if TRUE then load in Twig.
 * @param boolean	$loadrb		if TRUE then load in RedBean
 *
 * @return object
 */
        public function setup($basedir, $ajax, $devel, $loadtwig, $loadrb = TRUE)
        {
#
# For a fixed place production system you probably just want to replace all the directory munging with constants!
#
            $this->basepath = $basedir;
            $this->devel = $devel;
        #    $bd = $basedir;
        #    $bdr = [];
        #    while ($bd != $_SERVER['DOCUMENT_ROOT'])
        #    {
        #	$pp = pathinfo($bd);
        #	$bd = $pp['dirname'];
        #	$bdr[] = $pp['basename'];
        #    }
            $this->basedname = Config::BASEDNAME;
            $this->ajax = $ajax;
 /*
 * Set up all the system error handlers
 */
            set_exception_handler([$this, 'exception_handler']);
            set_error_handler([$this, 'error_handler']);
            register_shutdown_function([$this, 'shutdown']);

            if ($loadtwig)
            { # we want twig - there are some autoloader issues out there that adding twig seems to fix....
                $this->setuptwig(FALSE);
            }

            $offl = $this->makebasepath('offline');
            if (file_exists($offl))
            { # go offline before we try to do anything else...
                $this->render('support/offline.twig', ['msg' => file_get_contents($offl)]);
                exit;
            }
/*
 * Initialise database access
 */
//	    require_once('rb.php'); # RedBean interface
            class_alias('\RedBeanPHP\R','\R');
            if (Config::DBHOST !== '' && $loadrb)
            { # looks like there is a database configured
                \R::setup('mysql:host='.Config::DBHOST.';dbname='.Config::DB, Config::DBUSER, Config::DBPW); # mysql initialiser
                \R::freeze(!$devel); # freeze DB for production systems
                $twurls = [];
                foreach (\R::findAll('fwconfig') as $cnf)
                {
                    $twurls[$cnf->name] = $cnf['value'];
                }
                if ($loadtwig)
                {
                    $this->twig->addGlobal('fwurls', $twurls); # Package URL values for use in Twigs
                }
	    }
            return $this;
        }
    }
?>
