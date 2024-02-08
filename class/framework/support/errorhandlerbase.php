<?php
/**
 * Contains definition of ErrorHandler class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Config\Config;
    use \Framework\Web\StatusCodes;
    use \Framework\Web\Web;
/**
 * Class for error handling
 */
    class ErrorHandlerBase
    {
/**
 * @var array<string> A list of $_SERVER fields to report to the user in an error message
 */
        private static array $tellfields = [
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
        protected bool $errignore      = FALSE;    // If TRUE then ignore trapped errors, needed for checking preg expressions....
        protected bool $wasignored     = FALSE; // Set to TRUE if an error was trapped and ignored
        protected array $senterrors    = []; // A list of errors that have been emailed to the user. Only send a message once.
        protected bool $error          = FALSE; // If TRUE then we are handling an error
        protected string $back         = ''; // backtrace info - only used with errors
        protected bool $debug          = FALSE;
/**
 * Constructor
 *
 * @param \Framework\Local $local
 * @param bool $devel
 * @param bool $ajax
 */
        public function __construct(protected \Framework\Local $local, protected bool $devel = FALSE, protected bool $ajax = FALSE)
        {
 /*
 * Set up all the system error handlers
 */
            /** @psalm-suppress ArgumentTypeCoercion */
            \set_exception_handler([$this, 'exceptionHandler']); // @phan-suppress-current-line PhanUndeclaredMethodInCallable
            \set_error_handler([$this, 'errorHandler']); // @phan-suppress-current-line PhanUndeclaredMethodInCallable
            /** @psalm-suppress InvalidArgument - psalm doesnt have the right spec for this function */
            /** @psalm-suppress ArgumentTypeCoercion */
            \register_shutdown_function([$this, 'shutdown']); // @phan-suppress-current-line PhanUndeclaredMethodInCallable
            if ($devel)
            { // set up expectation handling if in developer mode
                if (\ini_get('zend.assertions') != 1)
                {
                    \ini_set('zend.assertions', '1');
                }
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
 */
        protected function eRewrite(string $origin = '') : string
        {
            return '<pre>'.
                \str_replace(PHP_EOL, '<br/>'.PHP_EOL, \htmlentities($origin)).
                \str_replace(',[', ',<br/>&nbsp;&nbsp;&nbsp;&nbsp;[', \str_replace(PHP_EOL, '<br/>'.PHP_EOL, \htmlentities($this->back))).'</pre>';
        }
/**
 * Tell sysadmin there was an error
 *
 * @param string        $msg    An error messager
 * @param int|string    $type   An error type
 * @param string        $file   file in which error happened
 * @param int           $line    Line at which it happened
 */
        protected function tellAdmin(string $msg, $type, string $file, int $line) : string
        {
            $this->error = TRUE; // flag that we are handling an error
            $ekey = $file.' | '.$line.' | '.$type.' | '.$msg;
            $subject = Config::SITENAME.' '.\date('c').' System Error - '.$msg.' '.$ekey;
            $origin = $subject.\PHP_EOL.\PHP_EOL;
            foreach (self::$tellfields as $fld)
            {
                if (isset($_SERVER[$fld]))
                {
                    $origin .= $fld.': '.$_SERVER[$fld].\PHP_EOL;
                }
            }
            if (!isset($this->senterrors[$ekey]))
            {
                $this->senterrors[$ekey] = TRUE;
                if (isset($_GET['fwtrace']))
                {
                    $this->debug = TRUE;
                    \ob_start();
                    \debug_print_backtrace((int) $_GET['fwtrace'], (int) $_GET['fwdepth'] ?? 0);
                    $this->back .= \ob_get_clean(); // will get used later in make500
                    if (isset($_GET['fwdump']))
                    { // save the error message to a file in /debug
                        Debug::show($this->back);
                    }
                }
                /** @psalm-suppress RedundantCondition */
                if (Config::USEPHPM || \ini_get('sendmail_path') !== '')
                {
                    $err = $this->local->sendmail([Config::SYSADMIN], $subject,
                        $this->eRewrite($origin), $origin.\PHP_EOL.'Type : '.$type.\PHP_EOL.$file.' Line '.$line.\PHP_EOL.$this->back,
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
 * @param string $ekey    Error information string
 */
        protected function make500(string $ekey) : void
        {
            if (!\headers_sent())
            { // haven't generated any output yet.
                if ($this->devel || !$this->ajax)
                { // not in an ajax page so try and send a pretty error
                    $str = '<p>'.$ekey.'</p>'.($this->debug && $this->back !== '' ? $this->eRewrite() : '');
                    if (!$this->ajax && $this->local->hasRenderer())
                    { // we have twig so render a nice page
                        \Framework\Dispatch::basicSetup(\Support\Context::getinstance(), 'error');
                        $this->local->render('@error/500.twig', ['errdata' => $str], Web::HTMLMIME, StatusCodes::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    else
                    { // no twig or ajax so just dump
                        Web::getinstance()->internal($str);
                    }
                }
                else
                {
                    header(StatusCodes::httpHeaderFor(StatusCodes::HTTP_INTERNAL_SERVER_ERROR));
                }
            }
        }
    }
?>