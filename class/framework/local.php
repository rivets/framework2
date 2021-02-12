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
    use \R;
/**
 * This is a class that maintains values about the local environment and does error handling
 *
 * Template rendering is done in here also so TWIG is initialised in this class. This allows TWIG
 * to be used for things like generating nice offline pages.
 */
    class Local extends \Framework\Support\LocalBase
    {
        use \Framework\Utility\Singleton;
        use \Framework\Presentation\RenderFuncs;

        public const ERROR     = 0;        // 'fwerrmessage';
        public const WARNING   = 1;        // 'fwwarnmessage';
        public const MESSAGE   = 2;        // 'fwmessage';
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
 * Set up local information. Returns self
 *
 * The $loadORM parameter simplifies some of the unit testing for this class
 *
 * @param string    $basedir    The full path to the site directory
 * @param bool      $ajax       If TRUE then this is an AJAX call
 * @param bool      $devel      If TRUE then we are developing the system
 * @param array     $render     The name of the Renderer class and any options
 * @param bool      $loadORM    If TRUE then load in RedBean
 *
 * @return \Framework\Local
 */
        public function setup(string $basedir, bool $ajax, bool $devel, array $render, bool $loadORM = TRUE) : \Framework\Local
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

            $this->initRender($render);

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