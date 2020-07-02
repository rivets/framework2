<?php
/**
 * Run page tests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
     include_once 'devel/curl.php';

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
                ['/admin', 200, 1, '/login?page=/admin'],
                ['/admin/', 200, 1,  '/login/?page=/admin/'],
                ['/test', 200, 1, '/login/?page=/test'],
                ['/test/', 200, 1,  '/login/?page=/test/'],
            ]
       ];

    foreach ($nologin['success'] as $url)
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
    foreach ($nologin['fail'] as $test)
    {
        [$turl, $code, $rdcount, $rdurl] = $test;
        $url = $prefix.$turl;
        $data = Curl::fetch($url);
        $info = Curl::code();
        $resurl = urldecode($info['redirect_url'] !== '' ? $info['redirect_url'] : $info['url']);
        if ($info['http_code'] != $code)
        {
            echo '** "'.$url.'" ('.$resurl.') returns '.$info['http_code'].' with '.$info['redirect_count'].' redirect'.($info['redirect_count'] != 1 ? 's' : '').PHP_EOL;
        }
        elseif ($info['redirect_count'] != $rdcount)
        {
            if ($info['redirect_count'] == $rdcount + 1 && !$ssl && preg_match('/^https/i', $resurl) && $resurl == $https.$turl)
            {
                echo '-- "'.$url.'" ('.$resurl.') redirected to https'.PHP_EOL;
            }
            else
            {
                echo '** "'.$url.'" ('.$resurl.') has '.$info['redirect_count'].' redirect'.($info['redirect_count'] != 1 ? 's' : '').', expecting '.$rdcount.PHP_EOL;
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
?>