<?php
/**
 * Contains definition of the Security class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
 * @package Framework
 * @subpackage Web
 */
    namespace Framework\Support;

    use \RobThree\Auth\TwoFactorAuth;
    use \Support\Context;
/**
 * A class that handles provides various security related functions.
 */
    class Security
    {
        use \Framework\Utility\Singleton;

        private const ALGORITHM = 'sha256';

        private const SALT = '7HHX7089ZDCITYNJYAC1LWKD63H0X55NDV6VNCP4';

        private $twoFA = NULL;
/**
 * Make a nonce value for including inline CSS
 *
 * @return string
 */
        public function makeNonce()
        {
            $rand = '';
            for ($i = 0; $i < 32; $i++)
            {
                $rand .= \chr(mt_rand(0, 255));
            }
            return \hash('sha512', $rand);
        }
/**
 * Hash a string
 *
 * @param string $data
 *
 * @return string
 */
        public function hash(string $data)
        {
            return self::ALGORITHM.'-'.\base64_encode(\hash(self::ALGORITHM, $data, TRUE));
        }
/**
 * Get mimetype for a file
 *
 * @param string $path  The path to the file
 *
 * @return string
 */
        public function mimetype(string $path) : string
        {
            $finfo = \finfo_open(FILEINFO_MIME_TYPE);
            if (($mime = \finfo_file($finfo, $path)) === FALSE)
            { // there was an error of some kind.
                $mime = '';
            }
            \finfo_close($finfo);
            return $mime;
        }
/**
 * Return TRUE of there is a valid GPC Sec-GPC header
 *
 * @return bool
 */
        public function hasSecGPC()
        {
            return \Framework\Web\Web::getInstance()->header('Sec-GPC') == '1';
        }
/**
 * Check for HSTS wanted
 *
 * @return void
 */
        public function sslCheck(Context $context)
        {
            if ($context->local()->configVal('forcessl', '0') == '1')
            {
                $context->web()->addHeader([
                    'Strict-Transport-Security' => $context->local()->configVal('ssltime', '31536000'),
                ]);
            }
        }
/**
 * get the 2FA object
 *
 * @return TwoFactorAuth
 */
        private function get2fa()
        {
            if ($this->twoFA === NULL)
            {
                $this->twoFA = new TwoFactorAuth;
            }
            return $this->twoFA;
        }
/**
 * Generate 2FA Secret
 *
 * @return string
 */
        public function make2FASecret()
        {
            return $this->get2FA()->createSecret();
        }
/**
 * Check 2FA
 *
 * @param \RedBeanPHP\OODBBean  $user
 * @param string                $value
 *
 * @return bool
 */
        public function check2FA(string $secret, string $value)
        {
            return $this->get2FA()->verifyCode($secret, $value);
        }
/**
 * Make user code - used fror identifying a user for a 2FA check
 *
 * @param \RedBeanPHP\OODBBean  $user
 *
 * @return string
 */
        public function makeUCode(\RedBeanPHP\OODBBean $user)
        {
            $str = \hash('sha256', time().(new \Framework\Utility\RandomStringGenerator('BCDFGHJKLMNPQRSTVWXYZ0123456789'))->generate(32));
            $user->code2fa = $str;
            \R::store($user);
            return $str;
        }
    }
?>