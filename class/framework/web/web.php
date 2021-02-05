<?php
/**
 * Contains definition of the Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 * @subpackage Web
 */
    namespace Framework\Web;

/**
 * A class that handles various web related things.
 */
    class Web extends WebBase
    {
        use CSP;      // bring in CSP handling code
        use Response; // bring in response generating functions.
/**
 * Check to see if the client accepts gzip encoding
 *
 * @return bool
 */
        public function acceptgzip() : bool
        {
            return $this->accepts('gzip');
        }
/**
 * Check to see if the client accepts gzip encoding
 *
 * @param string  $type  The type you are looking for...
 *
 * @return bool
 */
        public function accepts(string $type) : bool
        {
            return filter_has_var(INPUT_SERVER, 'HTTP_ACCEPT_ENCODING') && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], $type) > 0;
        }
/**
 *
/**
 * What kind of request was this?
 *
 * @return string
 */
        public function method() : string
        {
            return $_SERVER['REQUEST_METHOD'];
        }
/**
 * Is this a POST?
 *
 * @return bool
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function isPost() : bool
        {
            return $this->method() == 'POST';
        }
/**
 * Add an item for use in a Cache-Control header
 *
 * @param string[]  $items  An array of items
 *
 * @return void
 */
        public function addCache(array $items) : void
        {
            $this->cache = array_merge($this->cache, $items);
        }
/**
 * Return header value or NULL if it does not exist
 *
 * @param string $name
 *
 * @return ?string
 */
        public function header(string $name) : ?string
        {
            $xname = strtoupper('HTTP_'.preg_replace('/\s+/', '_', trim($name)));
            return filter_has_var(INPUT_SERVER, $xname) ? $_SERVER[$xname] : NULL;
        }
    }
?>