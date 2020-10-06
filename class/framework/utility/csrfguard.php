<?php
/**
 * A class to generate and handling CSRF prevention tokens.
 *
 * This is derived directly from the code to be found at
 *
 * @link    https://www.owasp.org/index.php/PHP_CSRF_Guard
 *
 * I have rewritten it a bit to make it a little more "wieldy" and conformant
 * to the style of this framework
 *
 * ****************** NB This is just me playing around at the moment!!!! Not tested at all *******************
 */
    namespace Framework\Utility;

/**
 * Contains definition of CRSFGuard class
 */
    class CSRFGuard
    {
        use \Framework\Utility\Singleton;

        private const STRENGTH  = 64;
        private const NAME      = 'CSRFName';
        private const TOKEN     = 'CSRFToken';
/**
 * Generate unique token
 *
 * @param string    $uname  The name to be used for storing the token into the Session data
 *
 * @return string   The token
 */
        private function maketoken(string $uname) : string
        {
            $token = bin2hex(random_bytes(self::STRENGTH));
            /** @psalm-suppress RedundantCondition - not sure why psalm complains about this */
            if (isset($_SESSION))
            {
                $_SESSION[$uname] = $token;
            }
            return $token;
        }
/**
 * Validate token
 *
 * @param string    $uname      The name to be used for storing the token into the Session data
 * @param string    $tocheck    The token to be compared with what is stored
 *
 * @return bool
 */
        private function validate($uname, $tocheck) : bool
        {
            if (!isset($_SESSION[$uname]))
            { // no token in there so we are not checking so it's valid
                return TRUE;
            }
            $token = $_SESSION[$uname];
            unset($_SESSION[$uname]);
            return hash_equals($token, $tocheck); // constant time string comparison
        }
/**
 * Generate a name and a token
 *
 * @return array
 */
        public function generate() : array
        {
            $name ='CSRFGuard_'.mt_rand(0, mt_getrandmax());
            return [$name,  $this->maketoken($name)];
        }
/**
 * Return HTML inputs for CSRF
 *
 * @return string
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function inputs() : string
        {
            $grd = $this->generate();
            return '<input type="hidden" name="'.self::NAME.'" value="'.$grd[0].'"/><input type="hidden" name="'.self::TOKEN.'" value="'.$grd[1].'"/>';
        }
/**
 * Check a form
 *
 * @param int    $type  Defaults to INPUT_POST, but could be INPUT_GET
 *
 * @throws \Framework\Exception\InternalError when CSRFName is expected and not found
 * @throws \Framework\Exception\InternalError when token or name is not as stored in session
 *
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function check(int $type = INPUT_POST) : void
        {
            switch ($type)
            {
            case INPUT_POST:
                if (!$_SERVER['REQUEST_METHOD'] == 'POST')
                {
                    return;
                }
                break;
            case INPUT_GET:
                if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET))
                {
                    break;
                }
                /* FALLTHROUGH */
            default:
                return;
            }
            if (!filter_has_var($type, self::NAME) || !filter_has_var($type, self::TOKEN))
            {
                throw new \Framework\Exception\InternalError('No CSRF Name found, probable invalid request.');
            }
            if (!$this->validate(filter_input($type, self::NAME), filter_input($type, self::TOKEN)))
            {
                throw new \Framework\Exception\InternalError('Invalid CSRF token');
            }
        }
    }
?>