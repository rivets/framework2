<?php
/**
 * Run page tests
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    include_once 'devel/curl.php';

    class Tester
    {
        private $verbose;
        private $https;
        private $prefix;

        public function __construct(string $verbose, string $https, string $prefix)
        {
            $this->verbose = $verbose;
            $this->https = $https;
            $this->prefix = $prefix;
        }

        private function success(string $url) : void
        {

            $url = $this->prefix.$url;
            $data = Curl::fetch($url);
            $info = Curl::code();
            if ($info['http_code'] != 200 && $info['redirect_count'] == 0)
            {
                echo '** "'.$url.'" returns '.$info['http_code'].PHP_EOL;
            }
            elseif ($this->verbose)
            {
                echo '"'.$url.'" '.$info['http_code'].' OK'.PHP_EOL;
            }
        }

        private function fail(array $test) : void
        {
            [$turl, $code, $rdcount, $rdurl] = $test;
            $url = $this->prefix.$turl;
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
                        if ($resurl == $this->prefix.$rdurl.'/')
                        {
                            echo '-- "'.$url.'" ('.$resurl.') redirected to add trailing /'.PHP_EOL;
                        }
                        elseif ($resurl == $this->https.$rdurl || $resurl == $this->https.$rdurl.'/')
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
                        echo '!! "'.$url.'" ('.$resurl.') has '.$info['redirect_count'].' redirect'.($info['redirect_count'] != 1 ? 's' : '').', expecting '.$rdcount.' and '.$this->prefix.$rdurl.PHP_EOL;
                    }
                }
                else
                {
                    echo '** "'.$url.'" ('.$resurl.') has '.$info['redirect_count'].' redirect'.($info['redirect_count'] != 1 ? 's' : '').', expecting '.$rdcount.' and '.$this->prefix.$rdurl.PHP_EOL;
                }
            }
            elseif ($rdcount > 0 && $this->prefix.$rdurl != $resurl)
            {
                echo '** "'.$url.'" ('.$resurl.') expecting '.$this->prefix.$rdurl.PHP_EOL;
            }
            elseif ($this->verbose)
            {
                echo '"'.$url.'" '.$info['http_code'].' ('.$resurl.') OK'.PHP_EOL;
            }
        }

        public function runtest(array $test) : void
        {
            foreach ($test['success'] as $url)
            {
                $this->success($url);
                $this->success($url.'/');
            }
            foreach ($test['fail'] as $test)
            {
                $this->fail($test);
                $test[0] .= '/';
                $test[3] .= '/';
                $this->fail($test);
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

    $tester = new Tester($verbose, $https, $prefix);
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
           ['/devel/test/assert', 500, 0, ''],
           ['/devel/test/fail', 500, 0, ''],
           ['/devel/test/toss', 500, 0, ''],
           ['/nosuchpage', 404, 0, ''],
           ['/test', 403, 0, ''],
       ]
    ];

    $trester->runtest($nologin);

    if ($user !== '' && $password !== '')
    {
        echo '-------------------------------- Test with Login --------------------------------'.PHP_EOL;
        $data = Curl::post($prefix.'/login/',['login' => $user, 'password' => $password], '', TRUE);
        $info = Curl::code();
        if ($info['http_code'] == 200)
        {
            $tester->runtest($login);
        }
        else
        {
            echo '** login failed'.PHP_EOL;
        }
    }
    Curl::cleanup();
?>