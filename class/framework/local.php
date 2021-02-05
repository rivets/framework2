<?php
/**
 * Contains definition of Local class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 */
    namespace Framework;

    use \Config\Config;
    use \Config\Framework as FW;
    use \Framework\Web\Web;
    use \R;
/**
 * This is a class that maintains values about the local environment and does error handling
 *
 * Template rendering is done in here also so TWIG is initialised in this class. This allows TWIG
 * to be used for things like generating nice offline pages.
 */
    class Local
    {
        use \Framework\Utility\Singleton;

        public const ERROR     = 0;        // 'fwerrmessage';
        public const WARNING   = 1;        // 'fwwarnmessage';
        public const MESSAGE   = 2;        // 'fwmessage';
/**
 * @var array Contains string names for the message constants - used for Twig variables
 */
        private static $msgnames  = ['fwerrmessage', 'fwwarnmessage', 'fwmessage'];
/**
 * @var string    The absolute path to the site directory
 */
        private $basepath = '';
/**
 * @var string  The name of the site directory
 */
        private $basedname      = '';
/**
 * @var ?\Framework\Support\ErrorHandler
 */
        private $errorHandler   = NULL;
/**
 * @var ?object    the Twig renderer
 */
        private $renderer       = NULL;
/**
 * @var array    Key/value array of data to pass into template renderer
 */
        private $tvals          = [];
/**
 * @var array<array>    Stash away messages so that messages.twig works
 */
        private $messages       = [[], [], []];
/**
 * @var array               Config values from database
 */
        private $fwconfig       = [];
/**
 * @var bool   Developer mode?
 */
        private $devel          = FALSE;
/**
 * Return state of devel flag
 *
 * @return bool
 */
        public function develMode()
        {
            return $this->devel;
        }
/**
 * Send mail if possible
 *
 * @param string[]       $to       An array of people to send to.
 * @param string     $subject  The subject
 * @param string     $msg      The message - if $alt is not empty then this is assumed to be HTML.
 * @param string     $alt      The alt message - plain text
 * @param string[]   $other    From, cc, bcc etc. etc.
 * @param string[]   $attach   Any Attachments
 *
 * @return string
 */
        public function sendmail(array $to, string $subject, string $msg, string $alt = '', array $other = [], array $attach = []) : string
        {
            /** @psalm-suppress RedundantCondition */
            if (Config::USEPHPM || ini_get('sendmail_path') !== '')
            {
                try
                {
                    $mail = new \Framework\Utility\FMailer();
                    $mail->setFrom($other['from'] ?? Config::SITENOREPLY);
                    if (isset($other['replyto']))
                    {
                        $mail->addReplyTo($other['replyto']);
                    }
                    if (isset($other['cc']))
                    {
                        foreach ($other['cc'] as $cc)
                        {
                            $mail->addCC($cc);
                        }
                    }
                    if (isset($other['bcc']))
                    {
                        foreach ($other['bcc'] as $cc)
                        {
                            $mail->addBCC($cc);
                        }
                    }
                    foreach ($to as $em)
                    {
                        $mail->addAddress($em);
                    }
                    $mail->Subject = $subject;
                    if ($alt !== '')
                    {
                        $mail->AltBody= $alt;
                        $mail->isHTML(TRUE);
                    }
                    else
                    {
                        $mail->isHTML(FALSE);
                    }
                    $mail->msgHTML($msg);
                    foreach ($attach as $fl)
                    {
                        $mail->addAttachment($fl);
                    }
                    return $mail->send() ? '' : $mail->ErrorInfo;
                }
                catch (\Throwable $e)
                {
                    return $mail->ErrorInfo;
                }
            }
            return 'No mailer configured';
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
            return $this->errorHandler->eIgnore($ignore);
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
            return $this->base().'/assets'; // for HTML so the / is OK to use here
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
 * Calls a user defined function with the twig object as a parameter.
 * The user can then add extensions, filters etc.
 *
 * @param callable     $fn      A user defined function
 *
 * @return void
 */
        public function extendRenderer(callable $fn) : void
        {
            $this->renderer->extendEngine($fn);
        }
/**
 * Return TRUE if a renderer is enabled
 *
 * @return bool
 */
        public function hasRenderer()
        {
            return is_object($this->renderer);
        }
/**
 * Render a twig and return the string - will do nothing if the template is the empty string
 *
 * @param string    $tpl    The template
 * @param mixed[]   $vals   Values to set for the template
 *
 * @return string
 */
        public function getrender(string $tpl, array $vals = []) : string
        {
            return $this->renderer->getRender($tpl, $this->tvals);
        }
/**
 * Render a template - will do nothing if the template is the empty string
 *
 * @param string   $tpl       The template
 * @param mixed[]  $vals      Values to set for the template
 * @param string   $mimeType
 * @param int      $status
 *
 * @return void
 */
        public function render(string $tpl, array $vals = [], string $mimeType = Web::HTMLMIME, int $status = \Framework\Web\StatusCodes::HTTP_OK) : void
        {
            $this->renderer->render($tpl, $this->tvals);
        }
/**
 * Add a value into the values stored for rendering the template
 *
 * @param string|array<mixed>   $vname    The name to be used inside the template or an array of key/value pairs
 * @param mixed                 $value    The value to be stored or "" if an array in param 1
 * @param bool                  $tglobal  If TRUE add this as a template global variable
 *
 * @throws \Framework\Exception\InternalError
 *
 * @return void
 */
        public function addval(string|array $vname, mixed $value = '', bool $tglobal = FALSE) : void
        {
            $this->renderer->addval($vname, $value, $tglobal);
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
 * To have your Template engine deal with these you need
 *
 * {% include '@util/message.twig %}
 *
 * somewhere in the relevant template (usually at the top of the main body)
 *
 * @param int                   $kind   The kind of message
 * @param string|array<string>  $value  The value to be stored or an array of values
 *
 * @return void
 */
        public function message(int $kind, string|array $value) : void
        {
            $this->renderer->message($kind, $value);
        }
/**
 * Clear out messages
 *
 * @param ?int    $kind   Either NULL for all messages or a specific kind
 *
 * @return void
 */
        public function clearMessages(?int $kind = NULL) : void
        {
            $this->renderer->clearmessages($kind);
        }
/**
 * Clear out values
 *
 * @return void
 */
        public function clearValues() : void
        {
            $this->renderer->clearValues();
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
            return $this->base() !== '' ? preg_replace('#^'.$this->base().'#', '', $url) : $url;
        }
/**
 * Put the system into debugging mode
 *
 * @return void
 * @psalm-suppress PossiblyNullReference
 */
        public function enabledebug() : void
        {
            $this->errorHandler->enableDebug();
            if ($this->hasRenderer())
            { // now we know we have twig - hence suppress above
                $this->renderer->addExtension(new \Twig\Extension\DebugExtension());
                $this->renderer->enableDebug();
            }
        }
/**
 * Check to see if non-admin users are being excluded
 *
 * @param bool $admin
 *
 * @return void
 */
        public function adminOnly(bool $admin) : void
        {
            $offl = $this->makebasepath('admin', 'adminonly');
            if (file_exists($offl) && !$admin)
            { // go offline before we try to do anything else as we are not an admin
                $this->errorHandler->earlyFail('OFFLINE', file_get_contents($offl), FALSE);
                /* NOT REACHED */
            }
        }
/**
 * Set up local information. Returns self
 *
 * The $loadORM parameter simplifies some of the unit testing for this class
 *
 * @param string    $basedir    The full path to the site directory
 * @param bool      $ajax       If TRUE then this is an AJAX call
 * @param bool      $devel      If TRUE then we are developing the system
 * @param string    $render     The name of the Renderer class or '' if not wanted
 * @param bool      $loadORM    If TRUE then load in RedBean
 *
 * @return \Framework\Local
 */
        public function setup(string $basedir, bool $ajax, bool $devel, string $render, bool $loadORM = TRUE) : \Framework\Local
        {
            $this->basepath = $basedir;
            $this->basedname = Config::BASEDNAME;
            $this->devel = $devel;
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
        //        $pp = \pathinfo($bd);
        //        $bd = $pp['dirname'];
        //        $bdr[] = $pp['basename'];
        //    }
        //    $this->basedname = \implode('/', $bdr);
            $this->errorHandler = new \Framework\Support\ErrorHandler($devel, $ajax, $this);

            if ($render !== '')
            { // we want a renderer - this setups Twig but needs to be generalised
                $this->renderer = new ('\\Framework\\Presentation\\'.$render)($context, []);
            }

            $offl = $this->makebasepath('admin', 'offline');
            if (file_exists($offl))
            { // go offline before we try to do anything else...
                $this->errorHandler->earlyFail('OFFLINE', file_get_contents($offl), FALSE);
                /* NOT REACHED */
            }
/*
 * Initialise database access
 */
            \class_alias('\RedBeanPHP\R', '\R');
            /** @psalm-suppress RedundantCondition - the mock config file has this set to a value so this. Ignore this error */
            if (Config::DBHOST !== '' && $loadORM)
            { // looks like there is a database configured
                R::setup(Config::DBTYPE.':host='.Config::DBHOST.';dbname='.Config::DB, Config::DBUSER, Config::DBPW); // mysql initialiser
                if (!R::testConnection())
                {
                    $this->errorHandler->earlyFail('Database Error', 'Cannot connect to the database. Database may not be running or the site may be overloaded, please try later.', TRUE);
                    /* NOT REACHED */
                }
                R::freeze(!$devel); // freeze DB for production systems
                $this->fwconfig = [];
                foreach (R::findAll(FW::CONFIG) as $cnf)
                {
                    $cnf->value = preg_replace('/%BASE%/', $this->basedname, $cnf->value);
                    $this->fwconfig[$cnf->name] = $cnf;
                }

                if ($this->renderer !== NULL)
                {
                    /** @psalm-suppress PossiblyNullReference */
                    $this->renderer->addGlobal('fwurls', $this->fwconfig); // Package URL values for use in Twigs
                }
            }
            return $this;
        }
    }
?>