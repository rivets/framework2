<?php
/**
 * Contains the definition of Formdata GET support class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\FormData;

/**
 * A class that provides helpers for accessing GET form data
 */
    class Post extends AccessBase
    {
        public function __construct()
        {
            parent::__construct(INPUT_POST);
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
            $context = \Support\Context::getinstance();
            if ($context->constant('RECAPTCHA', 0) != 0)
            { # if this is non-zero we can assume SECRET and KEY are defined also
                if ($this->exists('g-recaptcha-response', FALSE))
                {
                    $porg = $this->getSuper(INPUT_POST);
                    $srv = $this->getSuper(INPUT_SERVER);
                    $data = [
                        'secret'    => $context->constant('RECAPTCHASECRET', ''),
                        'response'  => $porg['g-recaptcha-response'],
                        'remoteip'  => $srv['HTTP_X_FORWARDED_FOR'] ?? $srv['REMOTE_ADDR'],
                    ];

                    $client = new \GuzzleHttp\Client(['base_uri' => 'https://www.google.com']);
                    $response = $client->request('POST', '/recaptcha/api/siteverify', $data);
                    if ($response->getStatusCode() == 200)
                    {
                        return json_decode($response->getBody())->success;
                    }
                }
                return FALSE;
            }
            return TRUE; // no captcha so it always works.
        }
    }
?>