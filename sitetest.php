<?php
/**
 * Run page tests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
     include_once 'devel/curl.php';

     $options = getopt('h::u::p::v::', ['host::', 'user::', 'password::', 'verbose::']);
     $host = $options['h'] ?? 'localhost';
     $user = $options['u'] ?? '';
     $password = $options['p'] ?? '';
     $verbose = $options['v'] ?? FALSE;
     
     $prefix = 'http://'.$host;
     $prefixssl = 'https://'.$host;
     
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
                ['/admin/', 200, 1,  '/login?page=/admin/'],
                ['/test', 200, 1, '/login?page=/test'],
                ['/test/', 200, 1,  '/login?page=/test/'],
            ]
       ];

    foreach ($nologin['success'] as $url)
    {
        $url = $prefix.$url;
        $data = Curl::fetch($url);
        $info = Curl::code();
        if ($info['http_code'] != 200 && $info['redirect_count'] == 0)
        {
            echo '"'.$url.'" returns '.$info['http_code'].PHP_EOL;
        }
        elseif ($verbose !== FALSE)
        {
            echo '"'.$url.'" '.$info['http_code'].' OK'.PHP_EOL;
        }
    }
    foreach ($nologin['fail'] as $test)
    {
        [$url, $code, $rdcount, $rdurl] = $test;
        $url = $prefix.$url;
        $data = Curl::fetch($url);
        $info = Curl::code();
        if ($info['http_code'] != $code || $info['redirect_count'] != $rdcount || ($rdcount > 0 && $prefix.$rdurl != urldecode($info['redirect_url'])))
        {
            echo '"'.$url.'" ('.urldecode($info['url']).') returns '.$info['http_code'].PHP_EOL;
            var_dump($info);
        }
        elseif ($verbose !== FALSE)
        {
            echo '"'.$url.'" '.$info['http_code'].' ('.urldecode($info['url']).') OK'.PHP_EOL;
        }
    }
?>