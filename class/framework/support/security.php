<?php
/**
 * Contains definition of the Security class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 * @subpackage Web
 */
    namespace Framework\Support;

/**
 * A class that handles provides various security related functions.
 */
    class Security
    {
        use \Framework\Utility\Singleton;

        private const ALGORITHM = 'sha256';
/**
 * Make a nonce value for including inline CSS
 *
 * @return string
 */
        public function makeNonce()
        {
            $rand = '';
            for ($i = 0; $i < 32; $i++) {
                $rand .= chr(mt_rand(0, 255));
            }
            return hash('sha512', $rand);
        }
/**
 * Hash a string
 *
 * @param string $value
 *
 * @return string
 */
        public function hash(string $data)
        {
            return self::ALGORITHM.'-'.base64_encode(hash(self::ALGORITHM, $data, TRUE));
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
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if (($mime = finfo_file($finfo, $path)) === FALSE)
            { // there was an error of some kind.
                $mime = '';
            }
            finfo_close($finfo);
            return $mime;
        }
    }
?>