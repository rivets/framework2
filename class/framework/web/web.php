<?php
/**
 * Contains definition of the Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2022 Newcastle University
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
 */
        public function acceptgzip() : bool
        {
            return $this->accepts('gzip');
        }
/**
 * Check to see if the client accepts gzip encoding
 *
 * @param string  $type  The type you are looking for...
 */
        public function accepts(string $type) : bool
        {
            return \filter_has_var(\INPUT_SERVER, 'HTTP_ACCEPT_ENCODING') && \substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], $type) > 0;
        }
/**
 * What kind of request was this?
 */
        public function method() : string
        {
            return \strtoupper($_SERVER['REQUEST_METHOD']);
        }
/**
 * Is this a POST?
 *
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
 */
        public function addCache(array $items) : void
        {
            $this->cache = \array_merge($this->cache, $items);
        }
/**
 * Return the URI without any Query String
 */
        public function request()
        {
            if (isset($_SERVER['REDIRECT_URL']) && !\preg_match('/index.php/', $_SERVER['REDIRECT_URL']))
            {
/*
 *  Apache v 2.4.17 changed the the REDIRECT_URL value to be a full URL, so we need to strip this.
 *  Older versions will not have this so the code will do nothing.
 */
                $uri = \preg_replace('#^https?://[^/]+#', '', $_SERVER['REDIRECT_URL']);
            }
            else
            {
                $uri = $_SERVER['REQUEST_URI'];
            }
            if ($_SERVER['QUERY_STRING'] !== '')
            { // there is a query string so get rid it of it from the URI
                [$uri] = \explode('?', $uri);
            }
            return $uri;
        }
/**
 * Return header value or NULL if it does not exist
 */
        public function header(string $name) : ?string
        {
            $xname = \strtoupper('HTTP_'.\preg_replace('/\s+/', '_', \trim($name)));
            return \filter_has_var(\INPUT_SERVER, $xname) ? $_SERVER[$xname] : NULL;
        }
    }
?>