<?php
/**
 * Contains definition of ErrorHandler class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020-2022 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

/**
 * Class for error handling
 */
    class ErrorHandler extends ErrorHandlerBase
    {
/**
 * Generate a message page for early failures
 *
 * @param string    $title      Page title and heading
 * @param string    $msg        The message to be displayed
 * @param bool      $tellAdmin  If TRUE then mail admin
 */
        public function earlyFail(string $title, string $msg, bool $tellAdmin) : void
        {
            if ($tellAdmin)
            {
                $this->tellAdmin($title.' - '.$msg, 'Error', 'local.php', 0);
            }
            if ($this->local->hasRenderer())
            { // we have twig so can render a template
                $this->local->render('@admin/msgpage.twig', ['title' => $title, 'msg' => $msg]);
            }
            else
            { // generate a very simple page...
                echo '<!doctype html><html><head><title>'.$title.'</title></head><body><h1>'.$title.'</h1><p>'.$msg.'</p></body></html>';
            }
            exit;
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
            { // are we terminating with an error?
                if (isset($error['type']) && ($error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR))
                { // tell the developers about this
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
            if (class_exists('\R'))
            { // in case error happens before RedBean is loaded...
                \R::close(); // close RedBean connection
            }
        }
/**
 * Deal with untrapped exceptions - see PHP documentation
 */
        public function exceptionHandler(\Throwable $e) : void
        {
            if ($this->error)
            { // try and ignore errors within errors
                return;
            }
            $this->back = $e->getTraceAsString();
            $ekey = $this->tellAdmin(
                $e::class.': '.$e->getMessage(),
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
 */
        public function errorHandler(int $errno, string $errstr, string $errfile, int $errline) : bool
        {
            if ($this->errignore)
            { // wanted to ignore this so just return
                $this->wasignored = TRUE; // remember we did ignore though
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
            if ($errno != E_DEPRECATED && ($this->debug || in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])))
            { // this is an internal error or we are debugging, so we need to stop
                $this->make500($ekey);
                exit;
                /* NOT REACHED */
            }
/*
 * If we get here it's a warning or a notice, so we aren't stopping
 *
 * Change this to an exit if you don't want to continue on any errors
 */
            return TRUE;
        }
    }
?>