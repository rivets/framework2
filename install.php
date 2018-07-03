<?php
/**
 * This contains the code to initialise the framework from the web
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2018 Newcastle University
 */
    global $cwd, $verbose;
/**
 * Function to cleanup after errors
 *
 * Not everything needs to be cleaned up though, just things that will
 * stop the installer from running again.
 *
 * @return void
 */
    function cleanup()
    {
        global $cwd, $verbose;

        chdir($cwd);
        if ($verbose)
        {
            echo '<p>Cleaning '.$cwd.'</p>';
        }
        foreach (['class/config/config.php', '.htaccess'] as $file)
        {
            if (file_exists($file))
            {
                if ($verbose)
                {
                    echo '<p>Removing '.$file.'</p>';
                }
                @unlink($file);
            }
        }
    }
/**
 * Store a new framework config item
 *
 * @param string    $name
 * @param string    $value
 * @param boolean   $local     If TRUE then this value should not be overwritten by remote updates
 *
 * @return void
 */
    function addfwconfig($name, $value, $local)
    {
        $fwc = \R::dispense('fwconfig');
        $fwc->name = $name;
        $fwc->value = $value;
        $fwc->local = $local ? 1 : 0;
        $fwc->fixed = 1;
        \R::store($fwc);
    }

/**
 * Shutdown function - this is used to catch certain errors that are not otherwise trapped and
 * generate a clean screen as well as an error report to the developers.
 *
 * It also closes the RedBean connection
 *
 * @return void
 */
    function shutdown()
    {
        if ($error = error_get_last())
        { # are we terminating with an error?
            if (isset($error['type']) && ($error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR))
            { # tell the developers about this
                echo '<h2>There has been an installer system error &ndash; '.$error['type'].'</h2>';
            }
            else
            {
                echo '<h2>There has been an installer system error</h2>';
            }
            echo '<pre>';
            var_dump($error);
            echo '</pre>';
            cleanup();
        }
        \R::close(); # close RedBean connection
    }
/**
 * Deal with untrapped exceptions - see PHP documentation
 *
 * @param Exception	$e
 */
    function exception_handler($e)
    {
        echo '<h2>There has been an installer system exception</h2>';
        echo '<pre>';
        var_dump($e);
        echo '</pre>';
        cleanup();
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
 *
 * @return boolean
 */
    function error_handler($errno, $errstr, $errfile, $errline)
    {
        echo '<h2>There has been an installer system error : '.$errno.'</h2>';
        echo '<pre>';
        var_dump($errno, $errstr, $errfile, $errline);
        echo '</pre>';

        if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]))
        { # this is an internal error so we need to stop
            cleanup();
            exit;
        }
/*
 * If we get here it's a warning or a notice, so we aren't stopping
 *
 * Change this to an exit if you don't want to continue on any errors
 */
        return TRUE;
    }
    $verbose = isset($_GET['verbose']);
/*
 * Remember where we are in the file system
 */
    $cwd = __DIR__;
 /*
  * Set up all the system error handlers
  */
    error_reporting(E_ALL|E_STRICT);

    set_exception_handler('exception_handler');
    set_error_handler('error_handler');
    register_shutdown_function('shutdown');

    set_time_limit(120); # some people have very slow laptops and they run out of time on the installer.

    include 'class/config/framework.php';
    \Config\Framework::initialise();
/*
 * Initialise template engine - check to see if it is installed!!
 *
 */
    if (!file_exists('vendor'))
    {
/**
 * @todo Generate a better error message for this!
 */
        include 'install/errors/notwig.php';
        exit;
    }
    include 'vendor/autoload.php';
/**
 *  RedBean needs an alias to use namespaces
 */
    if (!class_alias('\RedBeanPHP\R','\R'))
    {
        include 'install/errors/notwig.php';
        exit;
    }

    try
    {
        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem('./install/twigs'),
            ['cache' => FALSE, 'debug' => TRUE]
        );
    }
    catch (Exception $e)
    {
        include 'install/errors/notwig.php';
        exit;
    }
/**
 * Test some PHP installation features...
 */
    $hasmb = function_exists('mb_strlen');
    $haspdo = in_array('mysql', \PDO::getAvailableDrivers());

    if (!$hasmb || !$haspdo)
    {
        include 'install/errors/phpbuild.php';
        exit;
    }
/**
 * Find out where we are
 *
 * Note that there issues with symbolic linking and __DIR__ being on a different path from the DOCUMENT_ROOT
 * DOCUMENT_ROOT seems to be unresolved
 *
 * DOCUMENT_ROOT should be a substring of __DIR__ in a non-linked situation.
 */
    $dn = preg_replace('#\\\\#', '/', __DIR__); # windows installers have \ in the name
    $sdir = preg_replace('#/+$#', '', $_SERVER['DOCUMENT_ROOT']); # remove any trailing / characters
    while (strpos($dn, $sdir) === FALSE)
    { # ugh - not on the same path
        $sdn = $sdir;
        $sdr = [];
        while (!is_link($sdn) && $sdn != '/')
        {
            $pp = pathinfo($sdn);
            array_unshift($sdr, $pp['basename']);
            $sdn = $pp['dirname'];
        }
        if (is_link($sdn))
        { # not a symbolic link clearly.
            $sdir = preg_replace('#/+$#', '', readlink($sdn).'/'.implode('/', $sdr));
        }
        else
        {
            include 'install/errors/symlink.php';
            exit;
        }
    }
    $bdr = [];
    while ($dn != $sdir)
    {
        $pp = pathinfo($dn);
        $dn = $pp['dirname'];
        array_unshift($bdr, $pp['basename']);
    }
    if (empty($bdr))
    {
        $dir = '';
        $name = 'newproject';
    }
    else
    {
        $dir = '/'.implode('/', $bdr);
        $name = array_pop($bdr);
    }

    $tpl = 'install.twig';
    $host = $_SERVER['HTTP_HOST'];
    switch ($host)
    {
    case 'localhost':
    case '127.0.0.1':
        $host = 'localhost.org';
        break;
    }
/*
 * URLs for various clientside packages that are used by the installer and by the framework
 *
 * N.B. WHEN UPDATING THESE DON'T FORGET TO UPDATE THE CSP LOCATIONS IF NECESSARY!!!!!!!!!
 */
    $fwurls = [
// CSS
        'bootcss'       => '//stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css',
//        'editablecss'   => '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css',
        'editablecss'   => '/'.$dir.'/assets/css/bs4-editable.css',
        'facss'         => '//use.fontawesome.com/releases/v5.1.0/css/all.css',
// JS
        'jquery'       => 'https://code.jquery.com/jquery-3.3.1.min.js',
        'jqueryslim'     => 'https://code.jquery.com/jquery-3.3.1.slim.min.js',
        'bootjs'        => '//stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js',
        'bootbox'       => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js',
//        'editable'      => '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js',
        'editable'      => ($dir != '/' ? ('/'.$dir) : '' ).'/assets/js/bs4-editable-min.js',
        'parsley'       => '//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.8.1/parsley.min.js',
        'popperjs'      => '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js',
    ];
    
    $fwcsp = [
        'default-src'   => '\'self\'',
        'css-src'       => '\'self\' use.fontawesome.com stackpath.bootstrapcdn.com',
        'script-src'    => '\'self\' stackpath.bootstrapcdn.com cdnjs.cloudflare.com',
    ];
/*
 * See if we have a sendmail setting in the php.ini file
 */
    $sendmail = ini_get('sendmail_path');

/*
 * Set up important values
 */
    $vals = [
             'name'         => $name,
             'dir'          => __DIR__,
             'base'         => $dir,
             'fwurls'       => $fwurls,
             'siteurl'      => 'http://'.$host.'/'.$dir.'/',
             'noreply'      => 'noreply@'.$host,
             'adminemail'   => $_SERVER['SERVER_ADMIN'],
             'sendmail'     => $sendmail !== '',
        ];

    $fail = FALSE;
    if (preg_match('/#/', $name))
    { // names with # in them will break the regexp in Local debase()
        $fail = $vals['hashname'] = TRUE;
    }
    elseif (version_compare(phpversion(), '7.0.0', '<')) {
        $fail = $vals['phpversion'] = TRUE;
    }
    elseif (!function_exists('password_hash'))
    {
        $fail = $vals['phpversion'] = TRUE;
    }

    if (!is_writable('.'))
    {
        $fail = $vals['nodotgw'] = TRUE;
    }

    if (!is_writable('class/config'))
    {
        $fail = $vals['noclassgw'] = TRUE;
    }

    if (file_exists('.htaccess') && !is_writable('.htaccess'))
    {
        $fail = $vals['nowhtaccess'] = TRUE;
    }

/*
 * We need to know some option selections to do some requirements checking
 */
    $flags = [
        'private', 'public', 'regexp', 'usephpm',
    ];
    $options = [];
    foreach ($flags as $fn)
    {
        $options[$fn] = filter_has_var(INPUT_POST, $fn);
    }

    if ($options['public'])
    {
        if (!is_writable('assets'))
        {
            $fail = $vals['noassets'] = TRUE;
        }
    }

    $vals['fail'] = $fail;
    $hasconfig = file_exists('class/config.php');
    $hashtaccess  = file_exists('.htaccess');
//    $vals['hasconfig'] = $hasconfig;
//    $vals['hashtaccess'] =  $hashtaccess;
    if (!$fail && filter_has_var(INPUT_POST, 'sitename'))
    { # this is an installation attempt
        $cvars = [
            'dbhost'        => ['DBHOST', FALSE, TRUE, 'string'], # name of const, add to DB, non-optional, type
            'dbname'        => ['DB', FALSE, TRUE, 'string'],
            'dbuser'        => ['DBUSER', FALSE, TRUE, 'string'],
            'dbpass'        => ['DBPW', FALSE, TRUE, 'string'],
            'sitename'      => ['SITENAME', TRUE, TRUE, 'string'],
            'siteurl'       => ['SITEURL', TRUE, TRUE, 'string'],
            'sitenoreply'   => ['SITENOREPLY', TRUE, TRUE, 'string'],
            'sysadmin'      => ['SYSADMIN', TRUE, TRUE, 'string'],
            'admin'         => ['', FALSE, TRUE],
            'adminpw'       => ['', FALSE, TRUE],
            'cadminpw'      => ['', FALSE, TRUE],
            'regexp'        => ['DBRX', FALSE, FALSE, 'bool'],
            'public'        => ['UPUBLIC', FALSE, FALSE, 'bool'],
            'private'       => ['UPRIVATE', FALSE, FALSE, 'bool'],
            'usephpm'       => ['USEPHPM', FALSE, FALSE, 'bool'],
            'smtphost'      => ['SMTPHOST', FALSE, FALSE, 'string'],
            'smtpport'      => ['SMTPPORT', FALSE, FALSE, 'string'],
            'protocol'      => ['PROTOCOL', FALSE, FALSE, 'string'],
            'smtpuser'      => ['SMTPUSER', FALSE, FALSE, 'string'],
            'smtppass'      => ['SMTPPW', FALSE, FALSE, 'string'],
            'csmtppass'     => ['', FALSE, FALSE, 'string'],
        ];
        $cvalue = [];
        foreach (array_keys($cvars) as $v)
        {
            if (filter_has_var(INPUT_POST, $v))
            {
                $cvalue[$v] = trim($_POST[$v]);
            }
            elseif ($cvars[$v][2])
            { // that variable must be present
                header('HTTP/1.1 400 Bad Request');
                exit;
            }
        }
        $direrr = [];
        if (!file_exists('debug'))
        {
            if (!@mkdir('debug', 0770)) // make a directory for debugging output
            {
                $direrr[] = 'Cannot create directory "debug"';
            }
        }
/*
 *  Make directories for uploads if required
 */
        if ($options['public'] && !file_exists('assets'.DIRECTORY_SEPARATOR.'public'))
        { # make the directory for public files
            if (!@mkdir('assets'.DIRECTORY_SEPARATOR.'public', 0766))
            {
                $direrr[] = 'Cannot create directory "assets'.DIRECTORY_SEPARATOR.'public"';
            }
        }

        if ($options['private'] && !file_exists('private'))
        { # make the directory for private files
            if (!@mkdir('private', 0766))
            {
                $direrr[] = 'Cannot create directory "private"';
            }
        }

        if (!file_exists('twigcache'))
        {
            if (!@mkdir('twigcache')) # in case we turn caching on for twig.
            {
                $direrr[] = 'Cannot create directory "twigcache"';
            }
        }

        if (!empty($direrr))
        {
            $vals['direrr'] = TRUE;
            $vals['dirmsg'] = $direrr;
            $vals['fail'] = TRUE;
        }
        else
        {
/*
 * Setup the config.php file in the lib directory
 */
            $fd = fopen('class/config/config.php', 'w');
            if ($fd === FALSE)
            {
                header('HTTP/1.1 500 Internal Error');
                exit;
            }
            fputs($fd, '<?php'.PHP_EOL.'    namespace Config;'.PHP_EOL);
            fputs($fd, '/**'.PHP_EOL.' * Generated by framework installer - '.date('r').PHP_EOL.'*/'.PHP_EOL.'    class Config'.PHP_EOL.'    {'.PHP_EOL);
            fputs($fd, "\tconst BASEDNAME\t= '".$dir."';".PHP_EOL);
            fputs($fd, "\tconst SESSIONNAME\t= '".'PSI'.preg_replace('/[^a-z0-9]/i', '', $cvalue['sitename'])."';".PHP_EOL);
            foreach ($cvars as $fld => $pars)
            {
                if ($pars[0] !== '')
                { # Only save relevant values - see above
                    switch($pars[3])
                    {
                    case 'string':
                        if (isset($cvalue[$fld]))
                        {
                            fputs($fd, "\tconst ".$pars[0]."\t= ");
                            fputs($fd, "'".$cvalue[$fld]."';".PHP_EOL);
                        }
                        elseif ($pars[2])
                        { // this is required
                        }
                        break;
                    case 'bool':
                        if (isset($options[$fld]))
                        {
                            fputs($fd, "\tconst ".$pars[0]."\t= ");
                            fputs($fd, ($options[$fld] ? 'TRUE' : 'FALSE').';'.PHP_EOL);
                        }
                        elseif ($pars[2])
                        { // this is required
                        }
                        break;
                    }
                }
            }
            //fputs($fd, "\tconst DBOP\t= '".($options['regexp'] ? ' regexp ' : '=')."';".PHP_EOL);
            //fputs($fd, "\tconst UPUBLIC\t= ".($options['public'] ? 'TRUE' : 'FALSE').';'.PHP_EOL);
            //fputs($fd, "\tconst UPRIVATE\t= ".($options['private'] ? 'TRUE' : 'FALSE').';'.PHP_EOL);


            fputs($fd, "
        public static function setup()
        {
            \\Framework\\Web\\Web::getinstance()->addheader([
            'Date'              => gmstrftime('%b %d %Y %H:%M:%S', time()),
            'Window-target'     => '_top',      # deframes things
            'X-Frame-Options'	=> 'DENY',      # deframes things
            'Content-Language'	=> 'en',
            'Vary'              => 'Accept-Encoding',
            ]);
        }".PHP_EOL.PHP_EOL);
            fputs($fd, '
        public static $defaultCSP = ['.PHP_EOL);
            foreach ($fwcsp as $key => $val)
            {
                fputs($fd, "                '".$key."' => \"".$val.'",'.PHP_EOL);
            }
            fputs($fd, '        ];'.PHP_EOL);
            fputs ($fd,"
/**
 * Set up default CSP headers for a page
 *
 * There will be a basic set of default CSP permissions for the site to function,
 * but individual pages may wish to extend or restrict these.
 *
 * @return void
 */
        public function setCSP()
        {
            \$csp = '';
            foreach (Config::\$defaultCSP as \$key => \$val)
            {
                \$csp .= ' '.\$key.' '.\$val.';';
            }
            \\Framework\\Web\\Web::getinstance()->addheader([
                'Content-Security-Policy'   => \$csp
            ]);
        }".PHP_EOL);
            fputs($fd, '    }'.PHP_EOL.'?>');
            fclose($fd);
    /*
     * Setup the .htaccess file
     */
            $fd = fopen('.htaccess', 'w');
            if ($fd === FALSE)
            {
                cleanup();
                header('HTTP/1.1 500 Internal Error');
                exit;
            }
            fputs($fd, 'RewriteEngine on'.PHP_EOL.'Options -Indexes +FollowSymlinks'.PHP_EOL);
            fputs($fd, 'RewriteBase '.($dir === '' ? '/' : $dir).PHP_EOL);
            fputs($fd,
                'RewriteRule ^ajax.* ajax.php [L,NC,QSA]'.PHP_EOL.
                'RewriteRule ^(assets'.($options['public'] ? '|public' : '').')/(.*) $1/$2 [L,NC]'.PHP_EOL.
    //            'RewriteRule ^(themes/[^/]*/assets/(css|js)/[^/]*) $1 [L,NC]'.PHP_EOL.
                'RewriteRule ^.*$ index.php [L,QSA]'.PHP_EOL.PHP_EOL.
                '# uncomment these to turn on compression of responses'.PHP_EOL.
                '# Apache needs the deflate module and PHP needs the zlib module for these to work'.PHP_EOL.
                '# AddOutputFilterByType DEFLATE text/css'.PHP_EOL.
                '# AddOutputFilterByType DEFLATE text/javascript'.PHP_EOL.
                '# php_flag zlib.output_compression  On'.PHP_EOL.
                '# php_value zlib.output_compression_level 5'.PHP_EOL

            );
            fclose($fd);
    /*
     * Try opening the database and setting up the User table
     */
            try
            {
                $now = \R::isodatetime(time() - date('Z')); # make sure the timestamp is in UTC (this should fix a weird problem with some XAMPP installations)
                $vals['dbhost'] = $cvalue['dbhost'];
                $vals['dbname'] = $cvalue['dbname'];
                $vals['dbuser'] = $cvalue['dbuser'];
                \R::setup('mysql:host='.$cvalue['dbhost'].';dbname='.$cvalue['dbname'], $cvalue['dbuser'], $cvalue['dbpass']); # mysql initialiser
                \R::freeze(FALSE); // we need to be able to update things on the fly!
                \R::nuke(); // clear everything.....
                $user = R::dispense('user');
                $user->email = $cvalue['sysadmin'];
                $user->login = $cvalue['admin'];
                $user->password = password_hash($cvalue['adminpw'], PASSWORD_DEFAULT);
                $user->active = 1;
                $user->confirm = 1;
                $user->joined = $now;
                \R::store($user);
    /**
     * Now initialise the confirmation code table
     */
                $conf = R::dispense('confirm');
                $conf->code = 'this is a rubbish code';
                $conf->issued = $now;
                $conf->kind = 'C';
                \R::store($conf);
                $user->xownConfirm[] = $conf;
                \R::store($user);
                \R::trash($conf);
    ///**
    // * Check that timezone setting for PHP has not made the date into the future...
    // */
    //            $dt = \R::findOne('user', 'joined > NOW()');
    //            if (is_object($dt))
    //            {
    //                $vals['timezone'] = TRUE;
    //            }
    /**
     * Save some framework configuration information into the database
     * This will make it easier to remote updating of the system once
     * it is up and running
     */
                foreach ($cvars as $fld => $pars)
                {
                    if ($pars[1])
                    {
                        addfwconfig($fld, $cvalue[$fld], TRUE);
                    }
                }
                foreach ($fwurls as $k => $v)
                {
                    addfwconfig($k, $v, FALSE);
                }
    /**
     * Set up some roles for access control:
     *
     * Admin for the Site
     * Developer for the Site
     *
     * These are both granted to the admin user.
     */
                $cname = \R::dispense('rolecontext');
                $cname->name = 'Site';
                $cname->fixed = 1;
                \R::store($cname);
    // Admin role name
                $arname = \R::dispense('rolename');
                $arname->name = 'Admin';
                $arname->fixed = 1;
                \R::store($arname);

                $role = \R::dispense('role');
                $role->otherinfo = '-';
                $role->start = $now;
                $role->end =   $now; # this makes RedBean make it a datetime field
                \R::store($role);
                $role->end = NULL; # clear end date as we don't want to time limit admin
                \R::store($role);
                $user->xownRole[] = $role;
                $cname->xownRole[] = $role;
                $arname->xownRole[] = $role;
                \R::store($arname);
    // Developer Role name
                $drname = \R::dispense('rolename');
                $drname->name = 'Developer';
                \R::store($drname);

                $role = \R::dispense('role');
                $role->otherinfo = '-';
                $role->start = $now;
                $role->end = NULL; # no end date
                \R::store($role);
                $user->xownRole[] = $role;
                $cname->xownRole[] = $role;
                $drname->xownRole[] = $role;
                \R::store($user);
                \R::store($cname);
                \R::store($drname);
    /**
     * See code below for significance of the entries (kind, source, admin, needlogin, devel, active)
     *
     * the link for install.php is to catch when people try to run install again after a successful install
     */
                $pages = [
                    'about'         => [\Framework\SiteAction::TEMPLATE, '@pages/about.twig', FALSE, 0, FALSE, 1],
                    'admin'         => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\Admin', TRUE, 1, FALSE, 1],
                    'assets'        => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\Assets', TRUE, 1, FALSE, 0],          # not active - really only needed when total cacheability is needed
                    'confirm'       => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                    'contact'       => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\Contact', FALSE, 0, FALSE, 1],
                    'devel'         => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\Developer', TRUE, 1, TRUE, 1],
                    'forgot'        => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                    'home'          => [\Framework\SiteAction::TEMPLATE, 'index.twig', FALSE, 0, FALSE, 1],
                    'install.php'   => [\Framework\SiteAction::TEMPLATE, '@pages/oops.twig', FALSE, 0, FALSE, 1],
                    'login'         => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                    'logout'        => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 1, FALSE, 1],
                    'private'       => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\GetFile', FALSE, 1, FALSE, $options['private'] ? 1 : 0],
                    'register'      => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                    'upload'        => [\Framework\SiteAction::OBJECT, '\\Framework\\Pages\\Upload', FALSE, 0, FALSE, $options['public'] || $options['private'] ? 1 : 0],
                ];
                foreach ($pages as $name => $data)
                {
                    $page = \R::dispense('page');
                    $page->name = $options['regexp'] ? '^'.$name.'$' : $name;
                    $page->kind = $data[0];
                    $page->source = $data[1];
                    $page->needlogin = $data[3];
                    $page->mobileonly = 0;
                    $page->active = $data[5];
                    \R::store($page);
                    if ($data[2])
                    { // must be an admin
                        $pagerole = \R::dispense('pagerole');
                        $pagerole->start = $now;
                        $pagerole->end = NULL;
                        $pagerole->otherinfo = '';
                        \R::store($pagerole);
                        $page->xownPageRole[] = $pagerole;
                        $cname->xownPageRole[] = $pagerole;
                        $arname->xownPageRole[] = $pagerole;
                        \R::store($page);
                        \R::store($cname);
                        \R::store($arname);
                    }
                    if ($data[4])
                    { // must be a developer
                        $pagerole = \R::dispense('pagerole');
                        $pagerole->start = $now;
                        $pagerole->end = NULL;
                        $pagerole->otherinfo = '';
                        \R::store($pagerole);
                        $page->xownPageRole[] = $pagerole;
                        $cname->xownPageRole[] = $pagerole;
                        $drname->xownPageRole[] = $pagerole;
                        \R::store($page);
                        \R::store($cname);
                        \R::store($drname);
                    }

                }
                $tpl = 'success.twig';
            }
            catch (Exception $e)
            { # something went wrong - so cleanup and try again...
                $vals['dberror'] = $e->getMessage();
                $vals['fail'] = TRUE;
                cleanup();
            }
        }
    }
    echo $twig->render($tpl, $vals);
?>
