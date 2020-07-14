<?php
/**
 * Contains the definition of the Formdata class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2020 Newcastle University
 */
    namespace Framework\Support;

    use \Config\Config;
    use \Framework\Exception\BadValue;
    use \Support\Context;
/**
 * A class that provides helpers for accessing form data
 */
    class FormData
    {
        use \Framework\Utility\Singleton;
        
        private $map = [
            'get'       => INPUT_GET,
            'post'      => INPUT_POST,
            'put'       => NULL,
            'cookie'    => INPUT_COOKIE,
            'file'      => NULL,
        ];
        
        private $getters = [];

        public function __call(string $calling, array $arguments)
        {
            $name = \strtolower($calling);
            $func = '';
            if (($must = (\strpos($name, 'must') === 0)))
            {
                $name = \substr($name, 4);
            }
            if (($filter = (\strpos($name, 'filter') === 0)))
            {
                $name = \substr($name, 6);
            }
            if (($has = (\strpos($name, 'has') === 0)))
            {
                $name = \substr($name, 3);
            }
            elseif (($has = (\strpos($name, 'have') === 0)))
            {
                $name = \substr($name, 4);
            }
            foreach (\array_keys($map) as $t)
            {
                if (\strpos($name, $t) === 0)
                {
                    if (!isset($this->getters[$t]))
                    {
                        $class = '\Framework\FormData\\'.$t;
                        $this->getters[$t] = new $class();
                    }
                    $func = $func !== '' ? $func.\ucfirst($t) : $t;
                    switch (\substr($name, \strlen($t)))
                    {
                    case '':
                        break;
                    case 'a':
                        return $this->getters[$t]->{$must ? 'mustGetArray' : 'getArray'}($arguments[0]);
                    case 'bean':
                        if (!$must)
                        {
                            break 2;
                        }
                        return $this->getters[$t]->mustGetBean(...$arguments);
                    case 'data':
                        return $this->getters[$t]->get([$arguments[0], $arguments[1]]);
                    default:
                        break 2;
                    }
                    $res = $this->getters[$t]->fetch($arguments[0], $arguments[1] ?? NULL, $must, FALSE, $arguments[2] ?? NULL, $arguments[3] ?? '');
                    return $has ? $res[0] : $res[1];  
                }
            }
            throw new \Framework\Exception\InternalError('Bad FormData call: '.$calling);
        }

        public function recaptcha() : bool
        {
            if (Context::getinstance()->constant('RECAPTCHA', 0) != 0)
            { # if this is non-zero we can assume SECRET and KEY are defined also
                if ($this->haspost('g-recaptcha-response'))
                {
                    $data = [
                        'secret'    => Config::RECAPTCHASECRET,
                        'response'  => $_POST['g-recaptcha-response'],
                        'remoteip'  => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'],
                    ];

                    $client = new \GuzzleHttp\Client(['base_uri' => 'https://www.google.com']);
                    $response = $client->request('POST', '/recaptcha/api/siteverify', $data);
                    if ($response->getStatusCode() == 200)
                    {
                        return \json_decode($response->getBody())->success;
                    }
                }
                return FALSE;
            }
            return TRUE; // no captcha so it always works.
        }
    }
?>