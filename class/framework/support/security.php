<?php
/**
 * Contains definition of the Security class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2024 Newcastle University
 * @package Framework
 * @subpackage Web
 */
    namespace Framework\Support;

    use \RobThree\Auth\TwoFactorAuth;
    use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
    use \Support\Context;
/**
 * A class that handles provides various security related functions.
 */
    class Security
    {
        use \Framework\Utility\Singleton;

        private const string ALGORITHM = 'sha256';

        private ?TwoFactorAuth $twoFA = NULL;
/**
 * Make a nonce value for including inline CSS
 */
        public function makeNonce() : string
        {
            $rand = '';
            for ($i = 0; $i < 32; $i++)
            {
                $rand .= \chr(\random_int(0, 255));
            }
            return \hash('sha512', $rand);
        }
/**
 * Hash a string
 */
        public function hash(string $data) : string
        {
            return self::ALGORITHM.'-'.\base64_encode(\hash(self::ALGORITHM, $data, TRUE));
        }
/**
 * Get mimetype for a file
 *
 * @param string $path  The path to the file
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
 */
        public function hasSecGPC() : bool
        {
            return \Framework\Web\Web::getInstance()->header('Sec-GPC') == '1';
        }
/**
 * Check for HSTS wanted
 */
        public function sslCheck(Context $context) : void
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
 */
        private function get2fa() : TwoFactorAuth
        {
            if ($this->twoFA === NULL)
            {
                $this->twoFA = new TwoFactorAuth(new EndroidQrCodeProvider());
            }
            return $this->twoFA;
        }
/**
 * Generate 2FA Secret
 */
        public function make2FASecret() : string
        {
            return $this->get2FA()->createSecret();
        }
/**
 * Check 2FA
 */
        public function check2FA(string $secret, string $value) : bool
        {
            return $this->get2FA()->verifyCode($secret, $value);
        }
/**
 * Make user code - used fror identifying a user for a 2FA check
 */
        public function makeUCode(\RedBeanPHP\OODBBean $user) : string
        {
            $str = \hash('sha256', \time().(new \Framework\Utility\RandomStringGenerator('BCDFGHJKLMNPQRSTVWXYZ0123456789'))->generate(32));
            $user->code2fa = $str;
            \R::store($user);
            return $str;
        }
    }
?>