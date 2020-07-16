<?php
/**
 * Contains definition of ErrorHandler class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Support;

    use \Config\Config;
    use \Framework\Web\Web;
/**
 * Class for error handling
 */
    class ErrorHandler
    {
        private static $tellfields = [
            'REQUEST_URI',
            'HTTP_REFERER',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
            'REQUEST_METHOD',
            'REQUEST_SCHEME',
            'QUERY_STRING',
            'HTTP_COOKIE',
            'HTTP_USER_AGENT',
        ];
/**
 * @var bool    If TRUE then ignore trapped errors
 */
        private $errignore      = FALSE;    # needed for checking preg expressions....
/**
 * @var bool    Set to TRUE if an error was trapped and ignored
 */
        private $wasignored     = FALSE;
/**
 * @var array    A list of errors that have been emailed to the user. Only send a message once.
 */
        private $senterrors     = [];
/**
 * @var bool   If TRUE then we are handling an error
 */
        private $error          = FALSE;
/**
 * @var string    Backtrace info - only used with errors
 */
        private $back           = '';
/**
 * @var \Framework\Local
 */
        private $local          = NULL;
/**
 * @var bool
 */
        private $devel          = FALSE;
/**
 * @var bool
 */
        private $ajax           = FALSE;
/**
 * @var bool
 */
        private $debug          = FALSE;
/**
 * Constructor
 *
 * @param bool $devel
 * @param \Framework\Local
 */
        public function __construct(bool $devel, bool $ajax, \Framework\Local $local)
        {
            $this->local = $local;
            $this->devel = $devel;
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
        }
/**
 * Allow system to ignore errors
 *
 * This always clears the wasignored flag
 *
 * @param bool    $ignore    If TRUE then ignore the error otherwise stop ignoring
 *
 * @return bool    The last value of the wasignored flag
 */
        public function eIgnore(bool $ignore) : bool
        {
            $this->errignore = $ignore;
            $wi = $this->wasignored;
            $this->wasignored = FALSE;
            return $wi;
        }
/**
 * Put the system into debugging mode
 *
 * @return void
 * @psalm-suppress PossiblyNullReference
 */
        public function enableDebug() : void
        {
            $this->debug = TRUE;
        }
/**
 * Rewrite error string
 *
 * @param string $origin HTTP details
 *
 * @return string
 */
        private function eRewrite(string $origin = '') : string
        {
            return '<pre>'.
                str_replace(PHP_EOL, '<br/>'.PHP_EOL, htmlentities($origin)).
                str_replace(',[', ',<br/>&nbsp;&nbsp;&nbsp;&nbsp;[', str_replace(PHP_EOL, '<br/>'.PHP_EOL, htmlentities($this->back))).'</pre>';
        }
/**
 * Tell sysadmin there was an error
 *
 * @param string        $msg    An error messager
 * @param int|string    $type   An error type
 * @param string        $file   file in which error happened
 * @param int           $line    Line at which it happened
 *
 * @return string
 */
        public function tellAdmin(string $msg, $type, string $file, int $line) : string
        {
            $this->error = TRUE; // flag that we are handling an error
            $ekey = $file.' | '.$line.' | '.$type.' | '.$msg;
            $subject = Config::SITENAME.' '.date('c').' System Error - '.$msg.' '.$ekey;
            $origin = $subject.PHP_EOL.PHP_EOL;
            foreach (self::$tellfields as $fld)
            {
                if (isset($_SERVER[$fld]))
                {
                    $origin .= $fld.': '.$_SERVER[$fld].PHP_EOL;
                }
            }
            if (!isset($this->senterrors[$ekey]))
            {
                $this->senterrors[$ekey] = TRUE;
                if (isset($_GET['fwtrace']))
                {
                    $this->debug = TRUE;
                    ob_start();
                    debug_print_backtrace($_GET['fwtrace'], $_GET['fwdepth'] ?? 0);
                    $this->back .= ob_get_clean(); # will get used later in make500
                }
                /** @psalm-suppress RedundantCondition */
                if (Config::USEPHPM || ini_get('sendmail_path') !== '')
                {
                    $err = $this->local->sendmail([Config::SYSADMIN], $subject,
                        $this->eRewrite($origin), $origin.PHP_EOL.'Type : '.$type.PHP_EOL.$file.' Line '.$line.PHP_EOL.$this->back,
                        ['from' => Config::SITENOREPLY]);
                    if ($err !== '')
                    {
                        $ekey .= $this->eRewrite($origin);
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
                    $str = '<p>'.$ekey.'</p>'.($this->debug && $this->back !== '' ? $this->eRewrite() : '');
                    if (!$this->ajax && $local->hasTwig())
                    { # we have twig so render a nice page
                        Web::getinstance()->sendstring($this->local->getrender('@error/500.twig', ['errdata' => $str]), Web::HTMLMIME, StatusCodes::HTTP_INTERNAL_SERVER_ERROR);
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
                    $ekey = $this->tellAdmin(
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
 * @param \Throwable    $e
 */
        public function exceptionHandler(\Throwable $e) : void
        {
            if ($this->error)
            { // try and ignore errors within errors
                return;
            }
            $this->back = $e->getTraceAsString();
            $ekey = $this->tellAdmin(
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
 * @param int       $errno
 * @param string    $errstr
 * @param string    $errfile
 * @param int       $errline
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
            $ekey = $this->tellAdmin(
                'Error '.$errno.' '.$errstr,
                $errno,
                $errfile,
                $errline
            );
            if ($this->debug || in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]))
            { # this is an internal error or we are debugging, so we need to stop
                $this->make500($ekey);
                exit;
                /* NOT REACHED */
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
 *
 * @return void
 */
        public function assertFail($file, $line, $message) : void
        {
            $ekey = $this->tellAdmin('Assertion Failure: '.$message, 0, $file, $line);
            $this->make500($ekey);
            exit;
            /* NOT REACHED */
        }
    }
?>