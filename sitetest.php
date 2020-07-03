<?php
/**
 * Run page tests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    include_once 'devel/curl.php';
    
    function runtest(array $test) : void
    {
        global $verbose, $ssl, $https, $http, $prefix;

        foreach ($test['success'] as $url)
        {
            $url = $prefix.$url;
            $data = Curl::fetch($url);
            $info = Curl::code();
            if ($info['http_code'] != 200 && $info['redirect_count'] == 0)
            {
                echo '** "'.$url.'" returns '.$info['http_code'].PHP_EOL;
            }
            elseif ($verbose)
            {
                echo '"'.$url.'" '.$info['http_code'].' OK'.PHP_EOL;
            }
        }
        foreach ($test['fail'] as $test)
        {
            [$turl, $code, $rdcount, $rdurl] = $test;
            $url = $prefix.$turl;
            $data = Curl::fetch($url);
            $info = Curl::code();
            $resurl = urldecode($info['url']);
            if ($info['http_code'] != $code)
            {
                echo '** "'.$url.'" ('.$resurl.') returns '.$info['http_code'].' with '.$info['redirect_count'].' redirect'.($info['redirect_count'] != 1 ? 's' : '').PHP_EOL;
            }
            elseif ($info['redirect_count'] != $rdcount)
            {
                if ($info['redirect_count'] == $rdcount + 1)
                {
                    if(preg_match('/^https/i', $resurl))
                    {
                        if ($resurl == $https.$rdurl || $resurl == $https.$rdurl.'/')
                        {
                            echo '-- "'.$url.'" ('.$resurl.') redirected to https'.PHP_EOL;
                        }
                        else
                        {
                            echo '-- "'.$url.'" ('.$resurl.') redirected more than expected'.PHP_EOL;
                        }
                    }
                    elseif ($resurl == $prefix.$rdurl.'/')
                    {
                        echo '-- "'.$url.'" ('.$resurl.') redirected to add trailing /'.PHP_EOL;
                    }
                    else
                    {
                        echo '!! "'.$url.'" ('.$resurl.') has '.$info['redirect_count'].' redirect'.($info['redirect_count'] != 1 ? 's' : '').', expecting '.$rdcount.' and '.$prefix.$rdurl.PHP_EOL;
                    }
                }
                else
                {
                    echo '** "'.$url.'" ('.$resurl.') has '.$info['redirect_count'].' redirect'.($info['redirect_count'] != 1 ? 's' : '').', expecting '.$rdcount.' and '.$prefix.$rdurl.PHP_EOL;
                }
            }
            elseif ($rdcount > 0 && $prefix.$rdurl != $resurl)
            {
                echo '** "'.$url.'" ('.$resurl.') expecting '.$prefix.$rdurl.PHP_EOL;
            }
            elseif ($verbose)
            {
                echo '"'.$url.'" '.$info['http_code'].' ('.$resurl.') OK'.PHP_EOL;
            }
        }
    }
    
    $options = getopt('h:u::p::vb::s', ['base::host:', 'user::', 'password::', 'verbose', 'ssl']);
    $host = $options['h'] ?? 'localhost';
    $user = $options['u'] ?? '';
    $password = $options['p'] ?? '';
    $verbose = isset($options['v']);
    $base = $options['b'] ?? '';
    $ssl = isset($options['s']);
    
    $http = 'http://'.$host.($base !== '' ? '/'.$base : '');
    $https = 'https://'.$host.($base !== '' ? '/'.$base : '');
    $prefix = $ssl ? $https : $http;
    
    $nologin = [
       'success' => [
               '/',
               '/about',
               '/about/',
               '/contact',
               '/contact/',
           ],
           'fail' => [
               ['/nosuchpage', 404, 0, ''],
               ['/nosuchpage/', 404, 0, ''],
               ['/admin', 200, 1, '/login/?page=/admin'],
               ['/admin/', 200, 1,  '/login/?page=/admin/'],
               ['/devel', 200, 1, '/login/?page=/devel'],
               ['/devel/', 200, 1,  '/login/?page=/devel/'],
               ['/test', 200, 1, '/login/?page=/test'],
               ['/test/', 200, 1,  '/login/?page=/test/'],
           ]
    ];
      
    $login = [
        'success' => [
            '/',
            '/about',
            '/about/',
            '/contact',
            '/contact/',
            '/admin',
            '/admin/',
            '/devel',
            '/devel/',
            '/devel/mail',
            '/devel/mail/',
            '/devel/test',
            '/devel/test/',
            '/devel/upload',
            '/devel/upload/',
            '/devel/ajax',
            '/devel/ajax/',
            '/admin/info',
            '/admin/info/',
            '/admin/pages',
            '/admin/pages/',
            '/admin/users',
            '/admin/users/',
            '/admin/contexts',
            '/admin/contexts/',
            '/admin/roles',
            '/admin/roles/',
            '/admin/forms',
            '/admin/forms/',
            '/admin/beans',
            '/admin/beans/',
            '/admin/config',
            '/admin/config/',
            '/admin/checksum',
            '/admin/checksum/',
            '/admin/update',
            '/admin/update/',
            '/admin/offline',
            '/admin/offline/',
       ],
       'fail' => [
           ['/nosuchpage', 404, 0, ''],
           ['/nosuchpage/', 404, 0, ''],
           ['/test', 403, 0, ''],
           ['/test/', 403, 0,  ''],
           ['/devel/fail/', 500, 0, ''],
           ['/devel/throw/', 500, 0, ''],
       ]
    ];
    
    runtest($nologin);

    if ($user !== '' && $password !== '')
    {
        echo '-------------------------------- Test with Login --------------------------------'.PHP_EOL;
        $data = Curl::post($prefix.'/login/',['login' => $user, 'password' => $password], '', TRUE);
        $info = Curl::code();
        if ($info['http_code'] == 200)
        {
            runtest($login);
        }
        else
        {
            echo '** login failed'.PHP_EOL;
        }           
    }
    Curl::cleanup();
?>