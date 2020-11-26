<?php
/**
 * Contains the definition of the Formdata class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2015-2020 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Config\Config;
/**
 * A class that provides helpers for accessing form data
 */
    class FormData
    {
        use \Framework\Utility\Singleton;
/**
 * @var string Parameter values that indicate which data we want
 */
        private $map = [
            'cookie',
            'file',
            'get',
            'post',
            'put',
        ];
/**
 * @var array<object> An array of all the getters for forms
 */
        private $getters = [];
/**
 * Return the getter for this particular form type
 *
 * @param string $which  The getter we want.
 *
 * @return object
 */
        public function getter($which)
        {
            if (!isset($this->getters[$which]))
            {
                $class = '\Framework\FormData\\'.ucfirst($which);
                $this->getters[$which] = new $class();
            }
            return $this->getters[$which];
        }
/**
 * Map old formadata calls onto new style calls
 *
 * @deprecated
 *
 * @param string $calling    The function name
 * @param array  $arguments  The parameters
 *
 * @return string|array
 */
        public function __call(string $calling, array $arguments)
        {
            $name = \strtolower($calling);
            $func = '';
            if (($must = (\strpos($name, 'must') === 0)))
            {
                $name = \substr($name, 4);
            }
            if (\strpos($name, 'filter') === 0)
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
            foreach ($this->map as $t)
            {
                if (\strpos($name, $t) === 0)
                {
                    $func = $func !== '' ? $func.\ucfirst($t) : $t;
                    switch (\substr($name, \strlen($t)))
                    {
                    case '':
                        $ix = $must ? 1 : 2;
                        $res = $this->getter($t)->getValue($arguments[0], $must ? NULL : ($arguments[1] ?? NULL), $must, FALSE, $arguments[$ix] ?? NULL, $arguments[$ix+1] ?? []);
                        return $has ? $res[0] : $res[1];
                    case 'a': // get an array iterator
                        return $this->getter($t)->{$must ? 'mustFetchArray' : 'fetchArray'}($arguments[0]);
                    case 'bean':
                        if (!$must)
                        { // bean has to be a must just now
                            break 2;
                        }
                        return $this->getter($t)->mustFetchBean(...$arguments);
                    case 'data': // filedata call
                        return $this->getter($t)->fileData([$arguments[0], $arguments[1]]);
                    default:
                        break 2;
                    }
                    /** NOT REACHED */
                }
            }
            throw new \Framework\Exception\BadValue('Bad FormData call: '.$calling);
        }
/**
 * Deal with a recaptcha
 *
 * For this to work, you need three constants defined in \Config\Config :
 *
 * RECAPTCHA - the kind of RECAPTCHA: 2 or 3 (0 means no RECAPTCHA)
 * RECAPTCHAKEY - the key given by google
 * RECAPTCHASECRET - the secret key given by google
 *
 * @return bool
 * @psalm-suppress UndefinedClass
 * @psalm-suppress UndefinedConstant
 */
        public function recaptcha() : bool
        {
            if (\Config\Framework::constant('RECAPTCHA', 0) != 0)
            { // if this is non-zero we can assume SECRET and KEY are defined also
                if ($this->getter('post')->exists('g-recaptcha-response'))
                {
                    $data = [
                        'secret'    => Config::RECAPTCHASECRET,
                        'response'  => $_POST['g-recaptcha-response'],
                        'remoteip'  => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'],
                    ];

                    $client = new \GuzzleHttp\Client(['base_uri' => 'https://www.google.com']); //@phpstan-ignore-line
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