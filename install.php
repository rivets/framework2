<?php
/**
 * This contains the code to initialise the framework from the web
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2014-2017 Newcastle University
 */
/**
 * Store a new framework config item
 * @param string    $name
 * @param string    $value
 *
 * @return void
 **/
    function addfwconfig($name, $value)
    {
        $fwc = R::dispense('fwconfig');
        $fwc->name = $name;
        $fwc->value = $value;
        R::store($fwc);
    }

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
 * @todo Genrate a better error message for this!
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
/*
 * URLs for various clientside packages that are used by the installer and by the framework
 */
    $fwurls = [
        'bootcss'       => '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css',
        'editable'      => '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js',
        'editablecss'   => '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css',
        'facss'         => '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',

        'jquery1'       => '//code.jquery.com/jquery-1.12.4.min.js',
        'jquery2'       => '//code.jquery.com/jquery-3.2.1.slim.min.js',
        'bootjs'        => '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js',
        'bootbox'       => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js',
        'parsley'       => '//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.7.2/parsley.min.js',
        'popperjs'      => '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js',
    ];

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
        $name = end($bdr); # don't use $bdr again so no need to reset() it...
    }

    $tpl = 'install.twig';
    $vals = ['name' => $name, 'dir' => __DIR__, 'fwurls' => $fwurls];

    $fail = FALSE;
    if (preg_match('/#/', $name))
    { // names with # in them will break the regexp in Local debase()
        $fail = $vals['hashname'] = TRUE;
    }
    elseif (version_compare(phpversion(), '5.6.0', '<')) {
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
        'private', 'public', 'regexp',
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
//    $hasconfig = file_exists('class/config.php');
//    $hashtaccess  = file_exists('.htaccess');
//    $vals['hasconfig'] = $hasconfig;
//    $vals['hashtaccess'] =  $hashtaccess;
    if (!$fail && filter_has_var(INPUT_POST, 'sitename'))
    { # this is an installation attempt
        $cvars = [
            'dbhost'        => ['DBHOST', FALSE], # name of const, add to DB, DB fieldname
            'dbname'        => ['DB', FALSE],
            'dbuser'        => ['DBUSER', FALSE],
            'dbpass'        => ['DBPW', FALSE],
            'sitename'      => ['SITENAME', TRUE],
            'siteurl'       => ['SITEURL', TRUE],
            'sitenoreply'   => ['SITENOREPLY', TRUE],
            'sysadmin'      => ['SYSADMIN', TRUE],
            'admin'         => ['', FALSE],
            'adminpw'       => ['', FALSE],
            'cadminpw'      => ['', FALSE],
        ];
        $cvalue = [];
        foreach (array_keys($cvars) as $v)
        {
            if (filter_has_var(INPUT_POST, $v))
            {
                $cvalue[$v] = trim($_POST[$v]);
            }
            else
            {
                header('HTTP/1.1 400 Bad Request');
                exit;
            }
        }

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
        foreach ($cvars as $fld => $pars)
        {
            if ($pars[0] !== '')
            { # Only save relevant values - see above
                fputs($fd, "\tconst ".$pars[0]."\t= '".$cvalue[$fld]."';".PHP_EOL);
            }
        }
        fputs($fd, "\tconst DBOP\t= '".($options['regexp'] ? ' regexp ' : '=')."';".PHP_EOL);
        fputs($fd, "\tconst UPUBLIC\t= ".($options['public'] ? 'TRUE' : 'FALSE').';'.PHP_EOL);
        fputs($fd, "\tconst UPRIVATE\t= ".($options['private'] ? 'TRUE' : 'FALSE').';'.PHP_EOL);


	fputs($fd, "
	public static function setup()
	{
	    \\Framework\\Web\\Web::getinstance()->addheader([
		'Date'			=> gmstrftime('%b %d %Y %H:%M:%S', time()),
		'Window-target'		=> '_top',	# deframes things
		'X-Frame-Options'	=> 'DENY',	# deframes things
		'Content-Language'	=> 'en',
		'Vary'			=> 'Accept-Encoding',
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
            @unlink('class/config/config.php');
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

        mkdir('debug'); // make a directory for debugging output
/*
 *  Make directories for uploads if required
 */
        if ($options['public'])
        { # make the directory for public files
            mkdir('assets'.DIRECTORY_SEPARATOR.'public', 0766);
        }

        if ($options['private'])
        { # make the directory for private files
            mkdir('private', 0766);
        }

        mkdir('twigcache'); # in case we turn caching on for twig.
/*
 * Try opening the database and setting up the User table
 */
        require('rb.php');
        try
        {
            $now = \R::isodatetime(time() - date('Z')); # make sure the timestamp is in UTC (this should fix a weird problem with some XAMPP installations)
            $vals['dbhost'] = $cvalue['dbhost'];
            $vals['dbname'] = $cvalue['dbname'];
            $vals['dbuser'] = $cvalue['dbuser'];
            \R::setup('mysql:host='.$cvalue['dbhost'].';dbname='.$cvalue['dbname'], $cvalue['dbuser'], $cvalue['dbpass']); # mysql initialiser
            \R::freeze(FALSE);
            \R::nuke(); # clear everything.....
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
/**
 * Check that timezone setting for PHP has not made the date into the future...
 */
            $dt = R::findOne('user', 'joined > NOW()');
            if (is_object($dt))
            {
                $vals['timezone'] = TRUE;
            }
/**
 * Save some framework configuration information into the database
 * This will make it easier to remote updating of the system once
 * it is up and running
 */
            foreach ($cvars as $fld => $pars)
            {
                if ($pars[1])
                {
                    addfwconfig($fld, $cvalue[$fld]);
                }
            }
            foreach ($fwurls as $k => $v)
            {
                addfwconfig($k, $v);
            }
/**
 * Set up some roles for access control:
 *
 * Admin for the Site
 * Developer for the Site
 *
 * These are both granted to the admin user.
 */
            $cname = R::dispense('rolecontext');
            $cname->name = 'Site';
            $cname->fixed = 1;
            R::store($cname);
// Admin role name
            $arname = R::dispense('rolename');
            $arname->name = 'Admin';
            $arname->fixed = 1;
            R::store($rname);

            $role = R::dispense('role');
            $role->otherinfo = '-';
            $role->start = $now;
            $role->end =   $now; # this makes RedBean make it a datetime field
            R::store($role);
            $role->end = NULL; # clear end date as we don't want to time limit admin
            R::store($role);
            $user->xownRole[] = $role;
            $cname->xownRole[] = $role;
            $arname->xownRole[] = $role;
            R::store($arname);
// Developer Role name
            $drname = R::dispense('rolename');
            $drname->name = 'Developer';
            R::store($srname);

            $role = R::dispense('role');
            $role->otherinfo = '-';
            $role->start = $now;
            $role->end = NULL; # no end date
            R::store($role);
            $user->xownRole[] = $role;
            $cname->xownRole[] = $role;
            $drname->xownRole[] = $role;
            R::store($user);
            R::store($cname);
            R::store($drname);
/**
 * See code below for significance of the entries (kind, source, admin, needlogin, devel, active)
 *
 * the link for install.php is to catch when people try to run install again after a successful install
 */
            $pages = [
                'about'         => [Siteaction::TEMPLATE, 'about.twig', FALSE, 0, FALSE, 1],
                'admin'         => [Siteaction::OBJECT, '\\Framework\\Pages\\Admin', TRUE, 1, FALSE, 1],
                'assets'        => [Siteaction::OBJECT, '\\Framework\\Pages\\Assets', TRUE, 1, FALSE, 0],          # not active - really only needed when total cacheability is needed
                'confirm'       => [Siteaction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                'contact'       => [Siteaction::OBJECT, '\\Framework\\Pages\\Contact', FALSE, 0, FALSE, 1],
                'devel'         => [Siteaction::OBJECT, '\\Framework\\Pages\\Developer', TRUE, 1, TRUE, 1],
                'forgot'        => [Siteaction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                'home'          => [Siteaction::TEMPLATE, 'index.twig', FALSE, 0, FALSE, 1],
                'install.php'   => [Siteaction::TEMPLATE, 'oops.twig', FALSE, 0, FALSE, 1],
                'login'         => [Siteaction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                'logout'        => [Siteaction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 1, FALSE, 1],
                'private'       => [Siteaction::OBJECT, '\\Framework\\Pages\\GetFile', FALSE, 1, FALSE, $options['private'] ? 1 : 0],
                'register'      => [Siteaction::OBJECT, '\\Framework\\Pages\\UserLogin', FALSE, 0, FALSE, 1],
                'upload'        => [Siteaction::OBJECT, '\\Framework\\Pages\\Upload', FALSE, 0, FALSE, $options['public'] || $options['private'] ? 1 : 0],
            ];
            foreach ($pages as $name => $data)
            {
                $page = R::dispense('page');
                $page->name = $options['regexp'] ? '^'.$name.'$' : $name;
                $page->kind = $data[0];
                $page->source = $data[1];
                $page->needlogin = $data[3];
                $page->mobileonly = 0;
                $page->active = $data[5];
                \R::store($page);
                if ($data[2])
                { // must be an admin
                    $pagerole = R::dispense('pagerole');
                    $pagerole->start = $now;
                    $pagerole->end = NULL;
                    $pagerole->otherinfo = '';
                    R::store($pagerole);
                    $page->xownPageRole[] = $pagerole;
                    $cname->xownPageRole[] = $pagerole;
                    $arname->xownPageRole[] = $pagerole;
                    R::store($page);
                    R::store($cname);
                    R::store($arname);
                }
                if ($data[4])
                { // must be a developer
                    $pagerole = R::dispense('pagerole');
                    $pagerole->start = $now;
                    $pagerole->end = NULL;
                    $pagerole->otherinfo = '';
                    R::store($pagerole);
                    $page->xownPageRole[] = $pagerole;
                    $cname->xownPageRole[] = $pagerole;
                    $arname->xownPageRole[] = $pagerole;
                    R::store($page);
                    R::store($cname);
                    R::store($drname);
                }
                    
            }
            $tpl = 'success.twig';
        }
        catch (Exception $e)
        { # something went wrong - so cleanup and try again...
            $vals['dberror'] = $e->getMessage();
            @unlink('.htaccess');
            @unlink('class/config/config.php');
        }
    }
    echo $twig->render($tpl, $vals);
?>
