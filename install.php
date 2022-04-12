<?php
/**
 * This contains the code to initialise the framework from the web
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2022 Newcastle University
 */
    use Config\Framework as FW;
    use \Framework\Dispatch;

    $dir = \dirname(__DIR__, 2);
    /** @psalm-suppress UnusedFunctionCall */
    \set_include_path(
        \implode(PATH_SEPARATOR, [
            \implode(DIRECTORY_SEPARATOR, [$dir, 'class']),
            \get_include_path(),
        ])
    );
    /** @psalm-suppress UnusedFunctionCall */
    \spl_autoload_extensions('.php');
    \spl_autoload_register();

    global $verbose;
/**
 * Function to cleanup after errors
 *
 * Not everything needs to be cleaned up though, just things that will
 * stop the installer from running again.
 */
    function cleanup() : void
    {
        global $verbose;

        \chdir(__DIR__);
        if ($verbose)
        {
            echo '<p>Cleaning '.__DIR__.'</p>';
        }
        foreach (['class/config/config.php', '.htaccess'] as $file)
        {
            if (\file_exists($file))
            {
                if ($verbose)
                {
                    echo '<p>Removing '.$file.'</p>';
                }
                \unlink($file);
            }
        }
    }
/**
 * Store a new framework config item
 *
 * @param $local If TRUE then this value should not be overwritten by remote updates
 */
    function addfwconfig(string $name, array|string $value, bool $local) : void
    {
        $fwc = \R::dispense(FW::CONFIG);
        $fwc->name = $name;
        $fwc->local = $local ? 1 : 0;
        if (\is_array($value))
        {
            $fwc->value = $value[0];
            $fwc->fixed = $value[1];
            $fwc->integrity = $value[2];
            $fwc->crossorigin = $value[3];
            $fwc->defer = $value[4];
            $fwc->async = $value[5];
            $fwc->type = $value[6];
        }
        else
        {
            $fwc->value = $value;
            $fwc->fixed = 1;
            $fwc->integrity = '';
            $fwc->crossorigin = '';
            $fwc->defer = 0;
            $fwc->async = 0;
            $fwc->type = 'string';
        }
        \R::store($fwc);
    }

/**
 * Shutdown function - this is used to catch certain errors that are not otherwise trapped and
 * generate a clean screen as well as an error report to the developers.
 *
 * It also closes the RedBean connection
 */
    function shutdown() : void
    {
        if ($error = \error_get_last())
        { // are we terminating with an error?
            if (isset($error['type']) && ($error['type'] === \E_ERROR || $error['type'] === \E_PARSE || $error['type'] === \E_COMPILE_ERROR))
            { // tell the developers about this
                echo '<h2>There has been an installer system error &ndash; '.$error['type'].'</h2>';
            }
            else
            {
                echo '<h2>There has been an installer system error</h2>';
            }
            cleanup();
        }
        if (\class_exists('R'))
        {
            \R::close(); // close RedBean connection
        }
    }
/**
 * Deal with untrapped exceptions - see PHP documentation
 *
 * @return never
 */
    function exception_handler(Throwable $e) // : never
    {
        echo '<h2>There has been an installer system exception</h2>';
        echo '<p>'.$e->getMessage().'</p>';
        \ob_start();
        \debug_print_backtrace(1, 2);
        $back = \ob_get_clean(); // will get used later in make500
        echo \str_replace(',[', ',<br/>&nbsp;&nbsp;&nbsp;&nbsp;[', \str_replace(PHP_EOL, '<br/>'.PHP_EOL, \htmlentities($back))).'</pre>';
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
 */
    function error_handler(int $errno, string $errstr, string $errfile, int $errline) : bool
    {
        echo '<h2>There has been an installer system error : '.$errno.'</h2>';
        echo '<pre>';
        echo 'Errno: '.$errno.' Error: '.$errstr.PHP_EOL;
        echo 'File: '.$errfile.' Line: '.$errline.PHP_EOL;
        echo '</pre>';

        if (\in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]))
        { // this is an internal error so we need to stop
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
/**
 * Make a new role or context name
 */
    function makerc(string $type, string $name) : \RedBeanPHP\OODBBean
    {
        $drname = \R::dispense($type);
        $drname->name = $name;
        $drname->fixed = 1;
        \R::store($drname);
        return $drname;
    }
/**
 * Make a role
 */
    function makerole(string $type, string $now, \RedBeanPHP\OODBBean $owner, \RedBeanPHP\OODBBean $cname, \RedBeanPHP\OODBBean $rname) : \RedBeanPHP\OODBBean
    {
        $role = \R::dispense($type);
        $role->otherinfo = '-';
        $role->start = $now;
        $role->end = $now; // this makes RedBean make it a datetime field
        \R::store($role);
        $role->end = NULL; // clear end date as we don't want to time limit admin
        \R::store($role);
        $xown = 'xown'.ucfirst($type).'List';
        $owner->{$xown}[] = $role;
        $cname->{$xown}[] = $role;
        $rname->{$xown}[] = $role;
        \R::store($owner);
        \R::store($cname);
        \R::store($rname);
        return $role;
    }
/**
 * Make a string constant
 *
 * @param bool|int|string $str
 */
    function mkstring(bool|int|string $str): string
    {
        return "'".\preg_replace("/'/", "\\'", $str)."'";
    }
/**
 * Make a bool constant
 */
    function mkbool(bool|int|string $bl) : string
    {
        return $bl ? 'TRUE' : 'FALSE';
    }

    $verbose = isset($_GET['verbose']);
 /*
  * Set up all the system error handlers
  */
    \error_reporting(E_ALL|E_STRICT);

    \set_exception_handler('exception_handler');
    \set_error_handler('error_handler');
    \register_shutdown_function('shutdown');

    \set_time_limit(120); // some people have very slow laptops and they run out of time on the installer.
/*
 * Initialise template engine - check to see if it is installed!!
 *
 */
    if (!\file_exists('vendor'))
    {
        include 'install/errors/composer.php';
        exit;
    }
    include 'class/config/framework.php';
    \Config\Framework::initialise();
/**
 * Find out where we are
 *
 * Note that there issues with symbolic linking and __DIR__ being on a different path from the DOCUMENT_ROOT
 * DOCUMENT_ROOT seems to be unresolved
 *
 * DOCUMENT_ROOT should be a substring of __DIR__ in a non-linked situation.
 */
    $dn = \preg_replace('#\\\\#', '/', __DIR__); // windows installers have \ in the name
    $sdir = \preg_replace('#/+$#', '', $_SERVER['DOCUMENT_ROOT']); // remove any trailing / characters
    while (\strpos($dn, $sdir) === FALSE)
    { // ugh - not on the same path
        $sdn = $sdir;
        $sdr = [];
        while (!\is_link($sdn) && $sdn != '/')
        {
            $pp = \pathinfo($sdn);
            $sdn = $pp['dirname'];
            $sdr[] = $pp['basename'];
        }
        if (\is_link($sdn))
        { // not a symbolic link clearly.
            $sdir = \preg_replace('#/+$#', '', \readlink($sdn).'/'.\implode('/', $sdr));
        }
        else
        {
            include 'install/errors/symlink.php';
            exit;
        }
    }
    $bdr = [];
    while ($dn != $sdir)
    { // go backwards till we get to document root
        $pp = \pathinfo($dn);
        $dn = $pp['dirname'];
        \array_unshift($bdr, $pp['basename']);
    }
    if (empty($bdr))
    {
        $dir = '';
        $name = 'framework';
    }
    else
    {
        $dir = '/'.\implode('/', $bdr);
        $name = \array_pop($bdr);
    }
/*
 * URLs for various client side packages that are used by the installer and by the framework
 *
 * N.B. WHEN UPDATING THESE DON'T FORGET TO UPDATE THE CSP LOCATIONS IF NECESSARY!!!!!!!!!
 *
 * fwurls is used in some of the error gwnerating files os it ne3eds to set up here.
 */
    $fwurls = [ // url, fixed, integrity, crossorigin, defer, async, type
// CSS
        'bootcss'       => ['https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', 1, '', '', 0, 0, 'css'],
//        'editablecss'   => ['//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css', 1, '', '', 0, 0, 'css'],
        'editablecss'   => [$dir.'/assets/css/fw-editable.min.css', 1, '', '', 0, 0, 'css'],
        'facss'         => ['https://use.fontawesome.com/releases/v6.0.0/css/all.css', 1, '', '', 0, 0, 'css'],
// JS
        'jquery'        => ['https://code.jquery.com/jquery-3.5.1.min.js', 1, '', '', 0, 0, 'js'],
        'jqueryslim'    => ['https://code.jquery.com/jquery-3.5.1.slim.min.js', 1, '', '', 0, 0, 'js'],
        'bootjs'        => ['https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', 1, '', '', 0, 0, 'js'],
        'bootbox'       => ['https://cdn.jsdelivr.net/npm/bootbox@5.5.2/bootbox.js', 1, '', '', 0, 0, 'js'],
//        'editable'      => ['//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js', 1, '', '', 0, 0, 'js'],
        'editable'      => [$dir.'/assets/js/fw-editable-min.js', 1, '', '', 0, 0, 'js'],
        'parsley'       => ['https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/dist/parsley.min.js', 1, '', '', 0, 0, 'js'],
        // 'popperjs'      => ['https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js', 1, '', '', 0, 0, 'js'],
        'utiljs'        => [$dir.'/assets/js/util-min.js', 1, '', '', 0, 0, 'js'],
    ];

    try
    {
        $twig = new \Twig\Environment(
            new \Twig\Loader\FilesystemLoader('./install/twigs'),
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
    $hasmb = \function_exists('mb_strlen');
    $haspdo = \in_array('mysql', \PDO::getAvailableDrivers());
    $hasgah = \function_exists('getallheaders'); // this is an Apache only function called in the setup of the system

    if (!$hasmb || !$haspdo)
    {
        include 'install/errors/phpbuild.php';
        exit;
    }

    $tpl = 'install.twig';
    $host = $_SERVER['HTTP_HOST'];
    switch ($host)
    { // makes for a proper looking fake email address....
    case 'localhost':
    case '127.0.0.1':
        $host = 'localhost.org';
        break;
    }

    $fwcsp = [
        'connect-src'   => ["'self'"],
        'default-src'   => ["'self'"],
        'font-src'      => ["'self'", 'data:', '*.fontawesome.com'], // fontawesome uses data: internally
        'img-src'       => ["'self'", 'data:'],
        'script-src'    => ["'self'", 'cdn.jsdelivr.net', 'code.jquery.com', '*.fontawesome.com'], // fontawesome in case a kit is used later
        'style-src'     => ["'self'", 'cdn.jsdelivr.net', '*.fontawesome.com'],
    ];
/*
 * See if we have a sendmail setting in the php.ini file
 */
    $sendmail = \ini_get('sendmail_path');

/*
 * Set up important values
 */
    $vals = [
        'name'         => $name,
        'dir'          => __DIR__,
        'base'         => $dir,
        'fwurls'       => $fwurls,
        'siteurl'      => 'http://'.$host.$dir.'/',
        'noreply'      => 'noreply@'.$host,
        'adminemail'   => $_SERVER['SERVER_ADMIN'],
        'sendmail'     => $sendmail !== '',
    ];

    $fail = FALSE;
    if (\preg_match('/#/', $name))
    { // names with # in them will break the regexp in Local debase()
        $fail = $vals['hashname'] = TRUE;
    }
    elseif (\version_compare(phpversion(), '8.0', '<')) {
        $fail = $vals['phpversion'] = TRUE;
    }
    elseif (!\function_exists('password_hash'))
    {
        $fail = $vals['phpversion'] = TRUE;
    }

    if (!\is_writable('.'))
    {
        $fail = $vals['nodotgw'] = TRUE;
    }

    if (!\is_writable('class/config'))
    {
        $fail = $vals['noclassgw'] = TRUE;
    }

    if (\file_exists('.htaccess') && !\is_writable('.htaccess'))
    {
        $fail = $vals['nowhtaccess'] = TRUE;
    }

/*
 * We need to know some option selections to do some requirements checking
 */
    $flags = [
        'forcessl', 'confemail', 'private', 'public', 'regexp', 'register', 'reportcsp', 'usecsp', 'usephpm',
    ];
    $cvalue = [];
    $options = [];
    foreach ($flags as $fn)
    {
        $options[$fn] = \filter_has_var(\INPUT_POST, $fn);
        $cvalue[$fn] = $options[$fn] ? 1 : 0;
    }

    if ($options['public'])
    {
        if (!\is_writable('assets'))
        {
            $fail = $vals['noassets'] = TRUE;
        }
    }

    $vals['fail'] = $fail;
    $hasconfig = \file_exists('class/config.php');
    $hashtaccess  = \file_exists('.htaccess');
//    $vals['hasconfig'] = $hasconfig;
//    $vals['hashtaccess'] =  $hashtaccess;
    if (!$fail && \filter_has_var(INPUT_POST, 'sitename'))
    { // this is an installation attempt
        $cvars = [
            'dbtype'        => ['DBTYPE', FALSE, TRUE, 'string', 'mysql'],  // name of const, add to DB?, non-optional?, type, default
            'dbhost'        => ['DBHOST', FALSE, TRUE, 'string', 'localhost'],
            'dbname'        => ['DB', FALSE, TRUE, 'string', ''],
            'dbuser'        => ['DBUSER', FALSE, TRUE, 'string', ''],
            'dbpass'        => ['DBPW', FALSE, TRUE, 'string', ''],

            'sitename'      => ['SITENAME', TRUE, TRUE, 'string', ''],
            'sitenoreply'   => ['SITENOREPLY', TRUE, TRUE, 'string', ''],
            'siteurl'       => ['', TRUE, TRUE, 'string', ''],
            'sysadmin'      => ['SYSADMIN', TRUE, TRUE, 'string', ''],

            'admin'         => ['', FALSE, TRUE, '', ''],
            'adminpw'       => ['', FALSE, TRUE, '', ''],
            'cadminpw'      => ['', FALSE, TRUE, '', ''],

            'regexp'        => ['DBRX', FALSE, FALSE, 'bool', FALSE],
            'register'      => ['REGISTER', FALSE, FALSE, 'bool', FALSE],
            'confemail'     => ['CONFEMAIL', FALSE, FALSE, 'bool', FALSE],

            'public'        => ['UPUBLIC', FALSE, FALSE, 'bool', FALSE],
            'private'       => ['UPRIVATE', FALSE, FALSE, 'bool', FALSE],

            'minpwlen'      => ['MINPWLENGTH', FALSE, FALSE, 'int', 8],
            'forcessl'      => ['', TRUE, FALSE, 'bool', FALSE],
            'reportcsp'     => ['', TRUE, FALSE, 'bool', FALSE],
            'ssltime'       => ['', TRUE, FALSE, 'string', '31536000'], // one year
            'usecsp'        => ['', TRUE, FALSE, 'bool', TRUE],

            'recaptcha'     => ['RECAPTCHA', FALSE, TRUE, 'int', 0],
            'recaptchakey'  => ['RECAPTCHAKEY', FALSE, FALSE, 'string', ''],
            'recaptchasecret'  => ['RECAPTCHASECRET', FALSE, FALSE, 'string', ''],

            'usephpm'       => ['USEPHPM', FALSE, FALSE, 'bool', FALSE],
            'smtphost'      => ['SMTPHOST', FALSE, FALSE, 'string', ''],
            'smtpport'      => ['SMTPPORT', FALSE, FALSE, 'string', ''],
            'protocol'      => ['PROTOCOL', FALSE, FALSE, 'string', ''],
            'smtpuser'      => ['SMTPUSER', FALSE, FALSE, 'string', ''],
            'smtppass'      => ['SMTPPW', FALSE, FALSE, 'string', ''],
            'csmtppass'     => ['', FALSE, FALSE, 'string', ''],
        ];

        foreach (\array_keys($cvars) as $v)
        {
            if (\filter_has_var(INPUT_POST, $v))
            {
                $cvalue[$v] = trim($_POST[$v]);
            }
            elseif ($cvars[$v][2])
            { // that variable must be present
                \header('HTTP/1.1 400 Bad Request');
                exit;
            }
        }
/*
 * Make some directories that might be useful now or in the future if options change
 */
        $direrr = [];
        foreach (['debug', 'assets'.\DIRECTORY_SEPARATOR.'public', 'private', 'twigcache'] as $mdir)
        {
            if (!\file_exists($mdir))
            {
                if (!@\mkdir($mdir, 0770)) // make a directory for debugging output
                {
                    $direrr[] = 'Cannot create directory "'.$mdir.'"';
                }
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
            $fd = \fopen('class/config/config.php', 'w');
            if ($fd === FALSE)
            {
                \header('HTTP/1.1 500 Internal Error');
                echo '<h2>Cannot open "class/config/config.php" for writing/h2>';
                exit;
            }
            \fputs($fd, '<?php'.\PHP_EOL.'    namespace Config;'.\PHP_EOL);
            \fputs($fd, '/**'.\PHP_EOL.' * Generated by framework installer - '.\date('r').\PHP_EOL.'*/'.\PHP_EOL.'    class Config'.\PHP_EOL.'    {'.\PHP_EOL);
            \fputs($fd, "        public const BASEDNAME\t= ".mkstring($dir).';'.\PHP_EOL);
            \fputs($fd, "        public const PUTORPATCH\t= 'PATCH';".\PHP_EOL);
            \fputs($fd, "        public const SESSIONNAME\t= '".'PSI'.\preg_replace('/[^a-z0-9]/i', '', (string) $cvalue['sitename'])."';".\PHP_EOL);

            foreach ($cvars as $fld => $pars)
            {
                if ($pars[0] !== '')
                { // Only save relevant values - see above
                    \fputs($fd, "        public const ".$pars[0]."\t= ");
                    switch($pars[3])
                    {
                    case 'string':
                        if (isset($cvalue[$fld]))
                        {
                            \fputs($fd, \mkstring($cvalue[$fld]).';'.\PHP_EOL);
                        }
                        elseif ($pars[2])
                        { // this is required and should exist
                        }
                        else
                        {
                            \fputs($fd, \mkstring($pars[4]).';'.\PHP_EOL);
                        }
                        break;
                    case 'bool':
                        if (isset($options[$fld]))
                        {
                            \fputs($fd, \mkbool($options[$fld]).';'.\PHP_EOL);
                        }
                        elseif ($pars[2])
                        { // this is required
                        }
                        else
                        {
                            \fputs($fd, \mkbool($pars[4]).';'.\PHP_EOL);
                        }
                        break;
                    case 'int':
                        if (isset($cvalue[$fld]))
                        {
                            \fputs($fd, $cvalue[$fld].';'.\PHP_EOL);
                        }
                        elseif ($pars[2])
                        { // this is required
                        }
                        else
                        {
                            \fputs($fd, $pars[4].';'.\PHP_EOL);
                        }
                        break;
                    }
                }
            }
            //fputs($fd, "\tpublic const DBOP\t= '".($options['regexp'] ? ' regexp ' : '=')."';".PHP_EOL);
            \fputs($fd, "
        public static function setup()
        {
            \\Framework\\Web\\Web::getinstance()->addheader([
                'Date'                   => \gmdate('%b %d %Y %H:%M:%S', time()),
                'Window-Target'          => '_top',      // deframes things
                'X-Frame-Options'        => 'DENY',      // deframes things: SAMEORIGIN would allow this site to use frames
                'Content-Language'       => 'en',
                'Vary'                   => 'Accept-Encoding',
                'X-Content-Type-Options' => 'nosniff',
                'X-XSS-Protection'       => '1; mode=block',
            ]);
        }".\PHP_EOL.\PHP_EOL);
            \fputs($fd, '    }'.\PHP_EOL.\PHP_EOL);
            if (!$hasgah)
            {
                \fputs($fd, '
        function getallheaders() // code taken from PHP getallheaders manual page
        { // Apache only function so provide a definition of it. Used in \\Framework\\Context
            $headers = [];
            foreach ($_SERVER as $name => $value)
            {
                if (substr($name, 0, 5) == \'HTTP_\')
                {
                    $headers[str_replace(\' \', \'-\', ucwords(strtolower(str_replace(\'_\', \' \', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
         }'.\PHP_EOL.\PHP_EOL);
            }
            \fputs($fd, '?>');
            \fclose($fd);
    /*
     * Setup the .htaccess file
     */
            $fd = \fopen('.htaccess', 'w');
            if ($fd === FALSE)
            {
                cleanup();
                \header('HTTP/1.1 500 Internal Error');
                exit;
            }
            \fputs($fd, 'RewriteEngine on'.PHP_EOL.'Options -Indexes +FollowSymlinks'.\PHP_EOL);
            \fputs($fd, 'RewriteBase '.($dir === '' ? '/' : $dir).\PHP_EOL);
            \fputs($fd,
                'RewriteRule ^ajax.* ajax.php [L,NC,QSA]'.\PHP_EOL.
                'RewriteRule ^(assets'.($options['public'] ? '|public' : '').')/(.*) $1/$2 [L,NC]'.\PHP_EOL.
    //            'RewriteRule ^(themes/[^/]*/assets/(css|js)/[^/]*) $1 [L,NC]'.PHP_EOL.
                'RewriteRule ^.*$ index.php [L,QSA]'.\PHP_EOL.\PHP_EOL.
                '# uncomment these to turn on compression of responses'.\PHP_EOL.
                '# Apache needs the deflate module and PHP needs the zlib module for these to work'.\PHP_EOL.
                '# AddOutputFilterByType DEFLATE text/css'.\PHP_EOL.
                '# AddOutputFilterByType DEFLATE text/javascript'.\PHP_EOL.
                '# php_flag zlib.output_compression  On'.\PHP_EOL.
                '# php_value zlib.output_compression_level 5'.\PHP_EOL

            );
            \fclose($fd);
    /*
     * Try opening the database and setting up the User table
     */
            try
            {
                $now = \R::isodatetime(time() - (int) \date('Z')); // make sure the timestamp is in UTC (this should fix a problem with some XAMPP installations where the timezone is not local)
                $vals['dbtype'] = $cvalue['dbtype'];
                $vals['dbhost'] = $cvalue['dbhost'];
                $vals['dbname'] = $cvalue['dbname'];
                $vals['dbuser'] = $cvalue['dbuser'];
                \R::setup($cvalue['dbtype'].':host='.$cvalue['dbhost'].';dbname='.$cvalue['dbname'], (string) $cvalue['dbuser'], (string) $cvalue['dbpass']); // mysql initialiser
                \R::freeze(FALSE); // we need to be able to update things on the fly!
                \R::nuke(); // clear everything.....
                foreach ($fwcsp as $key => $val)
                {
                    foreach ($val as $host)
                    { //make the CSP database table
                        $bn = \R::dispense(FW::CSP);
                        $bn->type = $key;
                        $bn->host = $host;
                        $bn->essential = 1;
                        \R::store($bn);
                    }
                }
                $user = R::dispense(FW::USER);
                $user->email = $cvalue['sysadmin'];
                $user->login = $cvalue['admin'];
                $user->password = \password_hash((string) $cvalue['adminpw'], \PASSWORD_DEFAULT);
                $user->active = 1;
                $user->confirm = 1;
                $user->joined = $now;
                $user->secret = '';
                \R::store($user);
    /**
     * Now initialise the confirmation code table
     */
                $conf = R::dispense(FW::CONFIRM);
                $conf->code = 'this is a rubbish code';
                $conf->issued = $now;
                $conf->kind = 'C';
                \R::store($conf);
                $user->{'xown'.ucfirst(FW::CONFIRM).'List'}[] = $conf;
                \R::store($user);
                \R::trash($conf);
    /**
     * Save some framework configuration information into the database
     * This will make it easier to remote updating of the system once
     * it is up and running
     */
                foreach ($cvars as $fld => $pars)
                {
                    if ($pars[1])
                    {
                        addfwconfig($fld, (string) $cvalue[$fld], TRUE);
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
                $cname = makerc(FW::ROLECONTEXT, FW::FWCONTEXT);
    // Admin role name
                $arname = makerc(FW::ROLENAME, FW::ADMINROLE);
                makerole(FW::ROLE, $now, $user, $cname, $arname);
    // Developer Role name
                $drname = makerc(FW::ROLENAME, FW::DEVELROLE);
                makerole(FW::ROLE, $now, $user, $cname, $drname);
    // Testing role and context
                $tname = makerc(FW::ROLECONTEXT, FW::TESTCONTEXT);
                $trname = makerc(FW::ROLENAME, FW::TESTROLE);
    /**
     * See code below for significance of the entries (kind, source, admin, needlogin, devel, active)
     *
     * the link for install.php is to catch when people try to run install again after a successful install
     */
                /**
                 * @psalm-suppress PossiblyUndefinedArrayOffset
                 * @psalm-suppress TypeDoesNotContainType
                 * @psalm-suppress RedundantCondition
                 */
                $private = $options['private'] ? 1 : 0;
                $register = $options['register'] ? 1 : 0;
                /* Type, location, Admin?, Must Login?, Developer?, Active?, Tester?  - Must Login and Active are 1 or 0 as they go into the database */
                $pages = [
                    'about'         => [Dispatch::TEMPLATE, '@content/about.twig', FALSE, 0, FALSE, 1, FALSE],
                    'add2fa'        => [Dispatch::OBJECT, '\\Framework\\Pages\\Add2FA', FALSE, 1, FALSE, 1, FALSE],
                    'admin'         => [Dispatch::OBJECT, '\\Framework\\Pages\\Admin', TRUE, 1, FALSE, 1, FALSE],
                    'assets'        => [Dispatch::OBJECT, '\\Framework\\Pages\\Assets', FALSE, 1, FALSE, 0, FALSE], // not active - really only needed when total cacheability is needed
                    'confirm'       => [Dispatch::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, $register, FALSE],
                    'contact'       => [Dispatch::OBJECT, '\\Pages\\Contact', FALSE, 0, FALSE, 1, FALSE],
                    'cspreport'     => [Dispatch::OBJECT, '\\Framework\\Pages\\CSPReport', FALSE, 0, FALSE, $options['reportcsp'] ? 1 : 0, FALSE],
                    'devel'         => [Dispatch::OBJECT, '\\Framework\\Pages\\Developer', TRUE, 1, TRUE, 1, FALSE],
                    'forgot'        => [Dispatch::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1, FALSE],
                    'home'          => [Dispatch::OBJECT, '\\Pages\\Home', FALSE, 0, FALSE, 1, FALSE],
                    'install.php'   => [Dispatch::TEMPLATE, '@util/oops.twig', FALSE, 0, FALSE, 1, FALSE],
                    'login'         => [Dispatch::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1, FALSE],
                    'logout'        => [Dispatch::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 1, FALSE, 1, FALSE],
                    'private'       => [Dispatch::OBJECT, '\\Framework\\Pages\\GetFile', FALSE, 1, FALSE, $private, FALSE],
                    'register'      => [Dispatch::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, $register, FALSE],
                    'test'          => [Dispatch::TEMPLATE, '@util/test.twig', FALSE, 1, FALSE, 1, TRUE],
                    'twofa'         => [Dispatch::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1, FALSE],
                    'upload'        => [Dispatch::OBJECT, '\\Framework\\Pages\\Upload', FALSE, 1, FALSE, $options['public'] || $private, FALSE],
                ];
                foreach ($pages as $pname => $data)
                {
                    $page = \R::dispense(FW::PAGE);
                    /** @psalm-suppress PossiblyUndefinedArrayOffset **/
                    $page->name = $options['regexp'] ? '^'.$pname.'$' : $pname;
                    $page->kind = $data[0];
                    $page->source = $data[1];
                    $page->needlogin = $data[3];
                    $page->mobileonly = 0;
                    $page->active = $data[5];
                    \R::store($page);
                    if ($data[2])
                    { // must be an admin
                        makerole(FW::PAGEROLE, $now, $page, $cname, $arname);
                    }
                    if ($data[4])
                    { // must be a developer
                        makerole(FW::PAGEROLE, $now, $page, $cname, $drname);
                    }
                    if ($data[6])
                    { // must be a tester
                        makerole(FW::PAGEROLE, $now, $page, $tname, $trname);
                    }
                }
                $tpl = 'success.twig';
            }
            catch (Throwable $e)
            { // something went wrong - so cleanup and try again...
                $vals['dberror'] = $e->getMessage();
                $vals['fail'] = TRUE;
                cleanup();
            }
        }
    }
    echo $twig->render($tpl, $vals);
?>