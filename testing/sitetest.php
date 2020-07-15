<?php
/**
 * Run page tests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    include_once 'devel/curl.php';
    
    function success(string $url) : void
    {
        global $verbose, $ssl, $https, $http, $prefix;

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
    
    function fail(array $test) : void
    {
        global $verbose, $ssl, $https, $http, $prefix;

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
                    if ($resurl == $prefix.$rdurl.'/')
                    {
                        echo '-- "'.$url.'" ('.$resurl.') redirected to add trailing /'.PHP_EOL;
                    }
                    elseif ($resurl == $https.$rdurl || $resurl == $https.$rdurl.'/')
                    {
                        echo '-- "'.$url.'" ('.$resurl.') redirected to https'.PHP_EOL;
                    }
                    else
                    {
                        echo '-- "'.$url.'" ('.$resurl.') redirected more than expected'.PHP_EOL;
                    }
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
    
    function runtest(array $test) : void
    {
        global $verbose, $ssl, $https, $http, $prefix;

        foreach ($test['success'] as $url)
        {
            success($url);
            success($url.'/');
        }
        foreach ($test['fail'] as $test)
        {
            fail($test);
            $test[0] .= '/';
            $test[3] .= '/';
            fail($test);
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
               '/contact',
           ],
           'fail' => [
               ['/nosuchpage', 404, 0, ''],
               ['/admin', 200, 1, '/login/?goto=/admin'],
               ['/devel', 200, 1, '/login/?goto=/devel'],
               ['/test', 200, 1, '/login/?goto=/test'],
           ]
    ];
      
    $login = [
        'success' => [
            '/',
            '/about',
            '/contact',
            '/admin',
            '/devel',
            '/devel/test/mail',
            '/devel/test',
            '/devel/test/upload',
            '/devel/test/ajax',
            '/devel/test/get?remote=1',
            '/devel/test/post?remote=1',
            '/devel/test/cookie?remote=1',
            '/admin/info',
            '/admin/pages',
            '/admin/users',
            '/admin/contexts',
            '/admin/roles',
            '/admin/forms',
            '/admin/beans',
            '/admin/config',
            '/admin/checksum',
            '/admin/update',
            '/admin/offline',
       ],
       'fail' => [
           ['/devel/test/assert', 500, 1, ''],
           ['/devel/test/fail', 500, 1, ''],
           ['/devel/test/toss', 500, 1, ''],
           ['/nosuchpage', 404, 0, ''],
           ['/test', 403, 0, ''],
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