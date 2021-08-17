<?php
/**
 * Contains the definition of Formdata GET support class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020-2021 Newcastle University
 * @package Framework
 * @subpackage FormData
 */
    namespace Framework\FormData;

    use \Config\Framework as CFW;
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
 */
        public function recaptcha() : bool
        {
            return CFW::constant('RECAPTCHA', 0) != 0 ? \Framework\Web\Web::getInstance()->recaptcha(CFW::constant('RECAPTCHASECRET', '')) : TRUE;
        }
    }
?>